<?php

class scorebuy_ctl_admin_scoreapply extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }//End

    function index(){
        $this->finder('scorebuy_mdl_scoreapply',array(
                'title'=>app::get('scorebuy')->_('积分换购活动'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=scorebuy&ctl=admin_scoreapply&act=delete'),
                ),
                'use_view_tab'=>true,
                'use_buildin_recycle'=>false,
                )
            );
    }

    function delete(){
        $this->begin('index.php?app=scorebuy&ctl=admin_scoreapply&act=index');
        $ids = $this->_request->get_post('id');
        $object = $this->app->model('scoreapply');
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
                    if($garr['act_type'] == 'score'){
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

        $count_all = $this->app->model('scoreapply')->count();
        $count_dai = $this->app->model('scoreapply')->count(array('status'=>1));
        $count_pass = $this->app->model('scoreapply')->count(array('status'=>2));
        $count_no = $this->app->model('scoreapply')->count(array('status'=>3));
        return array(
                0=>array('label'=>app::get('scorebuy')->_('全部'),'optional'=>false,'filter'=>'','addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('scorebuy')->_('待审核'),'optional'=>false,'filter'=>array('status'=>1),'addon'=>$count_dai,'href'=>$this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('scorebuy')->_('审核通过'),'optional'=>false,'filter'=>array('status'=>2),'addon'=>$count_pass,'href'=>$this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'index','view'=>2))),
                3=>array('label'=>app::get('scorebuy')->_('审核不通过'),'optional'=>false,'filter'=>array('status'=>3),'addon'=>$count_no,'href'=>$this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'index','view'=>3))),

                4=>array('label'=>app::get('scorebuy')->_('审核不通过'),'optional'=>false,'filter'=>array('status'=>3),'addon'=>$count_no,'href'=>$this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_scoreapply','act'=>'index','view'=>3))),
            );
    }

    public function audit(){
        $sign = $this->_request->get_get('sign');
        $remark = $this->_request->get_post('remark');
        $last_price = $this->_request->get_post('last_price');
        $id = $this->_request->get_post('id');
        $isMemLv = $this->_request->get_post('isMemLv');
        $this->begin('index.php?app=scorebuy&ctl=admin_scoreapply&act=index');
        if($sign){
            $item['status'] = 3;
            $item['remark'] = $remark;
            $item['last_price'] = $last_price;
        }else{
            $item['status'] = 2;
            $item['remark'] = $remark;
            $item['last_price'] = $last_price;
        }
        $business = app::get('scorebuy')->model('scoreapply');
        $apply = $business->dump(array('id'=>$id),'*');
        if($apply){
            $rs = $business->update($item,array('id'=>$id));
            if($rs){
                //修改会员价格
                if($isMemLv){
                    $object = kernel::single('scorebuy_business_activity');
                    $memLvScore = $this->_request->get_post('memLvScore');
                    $memLvPrice = $this->_request->get_post('memLvPrice');
                    $memLvLastPrice = $this->_request->get_post('memLvLastPrice');
                    foreach($memLvScore as $levelId=>$score){
                        $item = array();
                        $item['aid'] = $id;
                        $item['gid'] = $apply['gid'];
                        $item['level_id'] = $levelId;
                        $item['score'] = $score;
                        $item['price'] = $memLvPrice[$levelId];
                        $item['last_price'] = $memLvLastPrice[$levelId];

                        $res = $object->addMemLvScore($item);
                        
                        if(!$res){
                            $this->end(false,'操作失败');
                        }
                    }
                }

                //冻结商品库存
                $goodsObj = app::get('b2c')->model('goods');
                if($apply['nums'] != ''){
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