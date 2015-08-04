<?php
class package_ctl_admin_activity extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
        $this->pagedata['return_url'] = $this->router->gen_url(array('app'=>'package','ctl'=>'admin_activity','act'=>'index'));
    }

    /*
     *Index
     */
    public function index(){
        $this->finder('package_mdl_activity',array(
            'title'=>app::get('package')->_('捆绑'),
            'actions'=>array(
                    array('label'=>app::get('package')->_('发起活动'),'icon'=>'add.gif','href'=>'index.php?app=package&ctl=admin_activity&act=add'),
                    array('label'=>app::get('package')->_('删除'),'submit'=>'index.php?app=package&ctl=admin_activity&act=delete'),
                ),
              'use_buildin_recycle'=>false,
            )
        );
    }
    
    function delete(){
        $this->begin('index.php?app=package&ctl=admin_activity&act=index');
        $ids = $this->_request->get_post('act_id');
        $object = $this->app->model('attendactivity');
        $iCount = $object->count(array('aid'=>$ids));
        if($iCount>0){
            $this->end(false,'所选活动中有商品信息，请先删除活动商品！');
        }
        $object = $this->app->model('activity');
        foreach($ids as $k=>$v){
            $re = $object->delete(array('act_id'=>$v));
        }
        $this->end(true,'删除成功');
    }

    public function add(){
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'package','ctl'=>'admin_activity','act'=>'toAdd'));

        $storeregion =array();
        $m =  app::get('b2c')->model('goods_cat');
        foreach($m->getList('*',array('parent_id'=>'0')) as $item){
           $storeregion[$item['cat_id']] = $item['cat_name'];
        }
        $this->pagedata['filter'] = array('parent_id'=>0);
        $this->pagedata['storeregion'] = $storeregion;
        $this->page('admin/add.html');
    }

    public function toAdd(){
        $this->begin($this->router->gen_url(array('app'=>'package','ctl'=>'admin_activity','act'=>'index'))); 
        $data = $this->get_data();
        $data['last_modified'] = time();
        $oActivity = $this->app->model('activity');
        if($data['act_id']){
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
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'package','ctl'=>'admin_activity','act'=>'toAdd'));
        $act_id = $this->_request->get_get('act_id');
        $oActivity = $this->app->model('activity');
        $activity = $oActivity->getList('*',array('act_id'=>$act_id));
        $this->pagedata['activity'] = $activity[0];
        $store_region = $activity[0]['business_type'];
        $store_region = explode(',',$store_region);
        $this->pagedata['store_region'] = $store_region;
        $this->pagedata['filter'] = array('parent_id'=>0);
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
       $item['act_id'] = $data['act_id'];
       $item['name'] = $data['name'];
       $item['description'] = $data['description'];
       $item['start_time'] = $data['start_time'];
       $item['end_time'] = $data['end_time'];
       $item['act_open'] = $data['act_open'];
       $item['business_type'] = $data['business_type'] ? ','.implode(',',$data['business_type']).',' : '';
       return $item; 
    }

    function getBusinessActivity(){
        $act_id = isset($_POST['act_id'])?$_POST['act_id']:-1;
        $act_obj = $this->app->model('businessactivity');
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

}