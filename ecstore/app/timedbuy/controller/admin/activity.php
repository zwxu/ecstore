<?php

class timedbuy_ctl_admin_activity extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }

    /*
     *Index
     */
    public function index(){
        $this->finder('timedbuy_mdl_activity',array(
                'title'=>app::get('timedbuy')->_('限时抢购'),
                'actions'=>array(
                        array('label'=>app::get('timedbuy')->_('发起活动'),'icon'=>'add.gif','href'=>'index.php?app=timedbuy&ctl=admin_activity&act=add',
                        ),
						array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=timedbuy&ctl=admin_activity&act=delete'),
                    ),
				'use_buildin_recycle'=>false,
                )
            );
    }

    public function add(){
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'toAdd'));

        $storeregion =array();
        $m =  app::get('b2c')->model('goods_cat');
        foreach($m->getList('*',array('parent_id'=>'0')) as $item){
           $storeregion[$item['cat_id']] = $item['cat_name'];
        }
		$this->pagedata['filter'] = array('parent_id'=>0);
        $this->pagedata['storeregion'] = $storeregion;
        $this->pagedata['storegrade'] = array (array('id'=>'0','name' => '卖场型旗舰店'),array('id'=>'1','name' => '专卖店'),array('id'=>'2','name' => '专营店'),array('id'=>'3','name' => '品牌旗舰店'));
        //echo "<pre>";print_r($this->pagedata['storegrade']);exit;
		$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'index'));
        $this->page('admin/add.html');
    }

    public function toAdd(){
        $this->begin($this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'index'))); 
        $data = $this->get_data();
		//echo '<pre>';print_r($data);exit;
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
		$this->begin($this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'index')));
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'toAdd'));
        $act_id = $this->_request->get_get('act_id');
        $oActivity = $this->app->model('activity');
        $activity = $oActivity->getList('*',array('act_id'=>$act_id));
		if($activity['act_open']=='true'){	
			$this->end(false,'不能编辑已开启的活动');
		}
        $this->pagedata['activity'] = $activity[0];
		$store_region = $activity[0]['business_type'];
		$store_region = explode(',',$store_region);
		$this->pagedata['store_region'] = $store_region;
        $store_lv = $activity[0]['store_lv'];
        $store_lv = explode(',',$store_lv);
        $store_type = $activity[0]['store_type'];
        $store_type = explode(',',$store_type);
        $this->pagedata['store_lv'] = $store_lv;
        $this->pagedata['store_type'] = $store_type;

		$this->pagedata['filter'] = array('parent_id'=>0);
		$this->pagedata['reUrl'] = $this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'index'));
        $this->page('admin/add.html');
        
    } 

	public function openActivity(){
		$this->begin($this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'index')));
		$act_id = $this->_request->get_get('act_id');
		$oActivity = $this->app->model('activity');
		$re = $oActivity->update(array('act_open'=>'true'),array('act_id'=>$act_id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
	}
	public function closeActivity(){
		$this->begin($this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_activity','act'=>'index')));
		$act_id = $this->_request->get_get('act_id');
		$oActivity = $this->app->model('activity');
		$re = $oActivity->update(array('act_open'=>'false'),array('act_id'=>$act_id));
		if($re){
			$this->end(true,'保存成功');
		}else{
			$this->end(false,'保存失败');
		}
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
	   $now = time();
	   if($data['end_time']<=$now){
		   $this->end(false,'结束时间要大于当前时间');
	   }
	  if($data['start_time']<$now){
		  $item['active_status'] = 'active';
	  }else{
		  $item['active_status'] = 'start';
	  }

       $item['act_id'] = $data['act_id'];
       $item['name'] = $data['name'];
       $item['description'] = $data['description'];
       $item['start_time'] = $data['start_time'];
       $item['end_time'] = $data['end_time'];
       $item['act_open'] = $data['act_open'];
       $item['business_type'] = $data['business_type'] ? ','.implode(',',$data['business_type']).',' : '';
       $item['store_type'] = isset($data['store_type']) ? $data['store_type'] : '';
       $item['store_lv'] = $data['store_lv'] ? implode(',',$data['store_lv']) : '';
       $item['act_tag'] = $data['act_tag'];
       $item['price_tag'] = $data['price_tag'];
       return $item; 
    }

    function getBusinessActivity(){
        $act_id = isset($_POST['act_id'])?$_POST['act_id']:-1;
        $cat_id = isset($_POST['cat_id'])?$_POST['cat_id']:-1;
        $act_obj = $this->app->model('businessactivity');
        $goods_obj = app::get('b2c')->model('goods');
        $godos_cat_obj = app::get('b2c')->model('goods_cat');
        $res = $act_obj->getList('id,gid,aid',array('aid'=>$act_id));
        $goodsCatRes = $godos_cat_obj->getList('cat_id',array('cat_path|head'=>','.$cat_id.','));
        foreach ($goodsCatRes as $gkey => $gval) {
            $aGoodsCat[] = $gval['cat_id'];
        }

        if($res){
            foreach($res as $k=>$v){
                $filter = array('goods_id'=>$v['gid']);
                $goods = $goods_obj->dump($filter,'name,cat_id');
                if($cat_id != -1){
                    if(!in_array($goods['category']['cat_id'], $aGoodsCat)){
                        unset($res[$k]);
                    }else{

                        $res[$k]['name'] = $goods['name'];
                    }
                }else{
                    $res[$k]['name'] = $goods['name'];
                }
            }
            if(empty($res)){
                echo 'null';
                exit();
            }else{
                echo json_encode(array_values($res));
                exit();
            }
        }

        echo 'null';exit();
    }

	 function delete(){
		$this->begin('index.php?app=timedbuy&ctl=admin_activity&act=index');
		$ids = $this->_request->get_post('act_id');
		$object = $this->app->model('businessactivity');
		$objAct = $this->app->model('activity');
		$business = $object->getList('*',array('aid'=>$ids));
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