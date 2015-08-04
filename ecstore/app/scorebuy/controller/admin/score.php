<?php

class scorebuy_ctl_admin_score extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }

    /*
     *Index
     */
    public function index(){
        $this->finder('scorebuy_mdl_activity',array(
                'title'=>app::get('scorebuy')->_('积分换购活动'),
                'actions'=>array(
                        array('label'=>app::get('scorebuy')->_('发起积分换购活动'),'icon'=>'add.gif','href'=>'index.php?app=scorebuy&ctl=admin_score&act=add',
                        ),
                        array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=scorebuy&ctl=admin_score&act=delete'),
                    ),
                'use_buildin_recycle'=>false,
                )
            );
    }

    public function add(){
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_score','act'=>'toAdd'));

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
        $this->begin($this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_score','act'=>'index'))); 
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
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'scorebuy','ctl'=>'admin_score','act'=>'toAdd'));
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

       $data['apply_start_time'] = strtotime($data['apply_start_time'].' '.$data['_DTIME_']['H']['apply_start_time'].':'.$data['_DTIME_']['M']['apply_start_time']);
       $data['apply_end_time'] = strtotime($data['apply_end_time'].' '.$data['_DTIME_']['H']['apply_end_time'].':'.$data['_DTIME_']['M']['apply_end_time']);
       if($data['apply_end_time']<=$data['apply_start_time']){
           $this->end(false,'申请开始时间要小于申请结束时间');
       }

       $item['act_id'] = $data['act_id'];
       $item['name'] = $data['name'];
       $item['description'] = $data['description'];
       $item['start_time'] = $data['start_time'];
       $item['end_time'] = $data['end_time'];
       $item['apply_start_time'] = $data['apply_start_time'];
       $item['apply_end_time'] = $data['apply_end_time'];
       $item['act_open'] = $data['act_open'];
       $item['business_type'] = $data['business_type'] ? ','.implode(',',$data['business_type']).',' : '';
       return $item; 
    }

    function getBusinessActivity(){
        $act_id = isset($_POST['act_id'])?$_POST['act_id']:-1;
        $act_obj = $this->app->model('scoreapply');
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

    function delete(){
        $this->begin('index.php?app=scorebuy&ctl=admin_score&act=index');
        $ids = $this->_request->get_post('act_id');
        $applyObj = $this->app->model('scoreapply');
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