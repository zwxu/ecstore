<?php

class groupbuy_ctl_admin_groupapply extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }//End

    function index(){
        $this->finder('groupbuy_mdl_groupapply',array(
                'title'=>app::get('groupbuy')->_('团购活动'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=groupbuy&ctl=admin_groupapply&act=delete'),
                ),
                'use_view_tab'=>true,
                'use_buildin_recycle'=>false,
                )
            );
    }

    function delete(){
        $this->begin('index.php?app=groupbuy&ctl=admin_groupapply&act=index');
        $ids = $this->_request->get_post('id');
        $object = $this->app->model('groupapply');
        $actObj = $this->app->model('activity');
        $oGoods = app::get('b2c')->model('goods');
        foreach($ids as $k=>$v){
            $goods_id = $object->dump(array('id'=>$v),'gid,aid');
            $act_status = $actObj->dump(array('act_id'=>$goods_id['aid']),'act_status');
            $re = $object->delete(array('id'=>$v));
            if($re){
                if($goods_id['gid']){
                    $sql1 = 'select act_type,goods_id from sdb_b2c_goods where goods_id = "'.$goods_id['gid'].'"';
                    $garr = $oGoods->db->selectrow($sql1);
                    if($garr['act_type'] == 'group'){
                        if($act_status && $act_status['act_status'] != '2'){
                            $sql = 'update sdb_b2c_goods set act_type="normal",store_freeze=0 where goods_id="'.$goods_id['gid'].'"';
                            $rs = $oGoods->db->exec($sql);
                            if(!$rs){
                                $this->end(false,'删除失败');
                            }
                        }
                    }
                }
            }
        }
        $this->end(true,'删除成功');
    }

     public function _views(){

        $count_all = $this->app->model('groupapply')->count();
        $count_dai = $this->app->model('groupapply')->count(array('status'=>1));
        $count_pass = $this->app->model('groupapply')->count(array('status'=>2));
        $count_no = $this->app->model('groupapply')->count(array('status'=>3));
        return array(
                0=>array('label'=>app::get('groupbuy')->_('全部'),'optional'=>false,'filter'=>'','addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_groupapply','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('groupbuy')->_('待审核'),'optional'=>false,'filter'=>array('status'=>1),'addon'=>$count_dai,'href'=>$this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_groupapply','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('groupbuy')->_('审核通过'),'optional'=>false,'filter'=>array('status'=>2),'addon'=>$count_pass,'href'=>$this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_groupapply','act'=>'index','view'=>2))),
                3=>array('label'=>app::get('groupbuy')->_('审核不通过'),'optional'=>false,'filter'=>array('status'=>3),'addon'=>$count_no,'href'=>$this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_groupapply','act'=>'index','view'=>3))),

                4=>array('label'=>app::get('groupbuy')->_('审核不通过'),'optional'=>false,'filter'=>array('status'=>3),'addon'=>$count_no,'href'=>$this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_groupapply','act'=>'index','view'=>3))),
            );
    }

    public function audit(){
        $sign = $this->_request->get_get('sign');
        $remark = $this->_request->get_post('remark');
        $last_price = $this->_request->get_post('last_price');
        $id = $this->_request->get_post('id');
        $this->begin('index.php?app=groupbuy&ctl=admin_groupapply&act=index');
        if($sign){
            $item['status'] = 3;
            $item['remark'] = $remark;
            $item['last_price'] = $last_price;
        }else{
            $item['status'] = 2;
            $item['remark'] = $remark;
            $item['last_price'] = $last_price;
        }
        $business = app::get('groupbuy')->model('groupapply');
        $apply = $business->dump(array('id'=>$id),'*');
        if($apply){
            $rs = $business->update($item,array('id'=>$id));
            if($rs){
               if($sign){
               }else{
                    $mdl_activity=app::get('groupbuy')->model('activity');
                    $activity=$mdl_activity->dump($apply['aid'],'*');
                    $promotion=kernel::single('business_goods_promotion');
                    $data['goods_id']=$apply['gid'];
                    $data['ref_id']=$apply['aid'];
                    $data['p_price']=$item['last_price'];
                    $data['p_name']=$activity['name'];
                    $data['p_type']='group';
                    $data['from_time']=$activity['start_time'];
                    $data['to_time']=$activity['end_time'];
                    $promotion->addPrice($data);
                }
                //冻结商品库存
                if($apply['nums'] != ''){
                    $goodsObj = app::get('b2c')->model('goods');
                    $sql = 'update sdb_b2c_goods set store_freeze='.$apply['nums'].' where goods_id="'.$apply['gid'].'"';
                    $result = $goodsObj->db->exec($sql);
                    if($result){
                        $this->end(true,'操作成功');
                        exit;
                    }
                }else{
                    $this->end(true,'操作成功');
                    exit;
                }
            }
        }
        $this->end(false,'操作失败');
    }
}