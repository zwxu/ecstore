<?php

class groupbuy_ctl_admin_group extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }

    /*
     *Index
     */
    public function index(){
        $this->finder('groupbuy_mdl_activity',array(
                'title'=>app::get('groupbuy')->_('团购活动'),
                'actions'=>array(
                        array('label'=>app::get('groupbuy')->_('发起团购活动'),'icon'=>'add.gif','href'=>'index.php?app=groupbuy&ctl=admin_group&act=add',
                        ),
                        array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=groupbuy&ctl=admin_group&act=delete'),
                    ),
                'use_buildin_recycle'=>false,
                )
            );
    }

    public function add(){
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_group','act'=>'toAdd'));

        $storeregion =array();
        $m =  app::get('b2c')->model('goods_cat');
        foreach($m->getList('*',array('parent_id'=>'0')) as $item){
           $storeregion[$item['cat_id']] = $item['cat_name'];
        }
        $this->pagedata['business_type_filter'] = array('parent_id'=>0);
        $this->pagedata['storeregion'] = $storeregion;
        $this->page('admin/add.html');
    }

    public function toAdd(){
        $this->begin($this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_group','act'=>'index'))); 
        $data = $this->get_data();
        $oActivity = $this->app->model('activity');
        if($data['act_id']){
            $data['last_modified'] = time();
            $re = $oActivity->update($data,array('act_id'=>$data['act_id']));
        }else{
            $re = $oActivity->save($data);
        }
        if($re){
            $this->end(true,'保存成功');
        }else{
            $this->end(false,'保存失败');
        }
       
    } 

    public function edit(){
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'groupbuy','ctl'=>'admin_group','act'=>'toAdd'));
        $act_id = $this->_request->get_get('act_id');
        $oActivity = $this->app->model('activity');
        $activity = $oActivity->dump(array('act_id'=>$act_id),'*');
        $this->pagedata['activity'] = $activity;
        $store_region = $activity['business_type'];
        $store_region = explode(',',$store_region);
        $store_lv = $activity['store_lv'];
        $store_lv = explode(',',$store_lv);
        $store_type = $activity['store_type'];
        $store_type = explode(',',$store_type);
        $filter = array('issue_type'=>$store_type,'disabled'=>'false');
        $this->pagedata['business_type_filter'] = array('parent_id'=>0);
        $this->pagedata['filter'] = $filter;
        $this->pagedata['store_region'] = $store_region;
        $this->pagedata['store_lv'] = $store_lv;
        $this->pagedata['store_type'] = $store_type;
        
        $this->page('admin/add.html');
        
    } 

    public function get_data(){
       
       $data = $this->_request->get_post();
       if(!$data['name']){
           $this->end(false,'请填写活动名称');
       }

       if(!$data['start_time']||!$data['end_time']){
           $this->end(false,'请填写活动的起始时间');
       }

       $data['start_time'] = strtotime($data['start_time'].' '.$data['_DTIME_']['H']['start_time'].':'.$data['_DTIME_']['M']['start_time']);
       $data['end_time'] = strtotime($data['end_time'].' '.$data['_DTIME_']['H']['end_time'].':'.$data['_DTIME_']['M']['end_time']);
       if($data['end_time']<=$data['start_time']){
           $this->end(false,'开始时间要小于结束时间');
       }

       $data['apply_start_time'] = strtotime($data['apply_start_time'].' '.$data['_DTIME_']['H']['apply_start_time'].':'.$data['_DTIME_']['M']['apply_start_time']);
       $data['apply_end_time'] = strtotime($data['apply_end_time'].' '.$data['_DTIME_']['H']['apply_end_time'].':'.$data['_DTIME_']['M']['apply_end_time']);
       if($data['apply_end_time']<=$data['apply_start_time']){
           $this->end(false,'申请开始时间要小于申请结束时间');
       }

       $item['act_id'] = $data['act_id'];
       $item['activity_tag'] = $data['activity_tag'];
       $item['price_tag'] = $data['price_tag'];
       $item['name'] = $data['name'];
       $item['nums'] = $data['nums'];
       $item['description'] = $data['description'];
       $item['start_time'] = $data['start_time'];
       $item['end_time'] = $data['end_time'];
       $item['apply_start_time'] = $data['apply_start_time'];
       $item['apply_end_time'] = $data['apply_end_time'];
       $item['act_open'] = $data['act_open'];
       $item['business_type'] = $data['business_type'] ? ','.implode(',',$data['business_type']).',' : '';
       $item['store_type'] = isset($data['store_type']) ? $data['store_type'] : '';
       $item['store_lv'] = $data['store_lv'] ? implode(',',$data['store_lv']) : '';

       return $item; 
    }

    function getBusinessActivity(){
        $act_id = isset($_POST['act_id'])?$_POST['act_id']:-1;
        $act_obj = $this->app->model('groupapply');
        $goods_obj = app::get('b2c')->model('goods');
        $res = $act_obj->getList('id,gid,aid',array('aid'=>$act_id));
        if($res){
            foreach($res as $k=>$v){
                $goods = $goods_obj->dump(array('goods_id'=>$v['gid']),'name');
                $res[$k]['name'] = $goods['name'];
            }
            echo json_encode($res);
            exit();
        }

        echo 'null';
    }

    function changeStoreLv(){
        $render = $this -> app -> render();
        $store_type = $_POST['store_type'];
        $store_type = explode(',',$store_type);
        $filter = array('issue_type'=>$store_type,'disabled'=>'false');
        $this->pagedata['filter'] = $filter;
        echo $render -> fetch('admin/storeLv.html');
    }


    function delete(){
        $this->begin('index.php?app=groupbuy&ctl=admin_group&act=index');
        $ids = $this->_request->get_post('act_id');
        $applyObj = $this->app->model('groupapply');
        $objAct = $this->app->model('activity');
        $business = $applyObj->getList('id',array('aid'=>$ids));
        if($business){
            $this->end(false,'存在活动商品，不能删除');
        }else{
            $re = $objAct->delete(array('act_id'=>$ids));
            if($re){
                $this->end(true,'删除成功');
            }else{
                $this->end(false,'删除失败');
            }
        }
    }

}