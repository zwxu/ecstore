<?php

class timedbuy_ctl_admin_businessactivity extends desktop_controller{
    
    function __construct($app){
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }//End

    function index(){
        $this->finder('timedbuy_mdl_businessactivity',array(
                'title'=>app::get('timedbuy')->_('限时抢购'),
				'actions'=>array(
					array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=timedbuy&ctl=admin_businessactivity&act=delete'),
				),
                'use_view_tab'=>true,
				'use_buildin_recycle'=>false,
                )
            );
    }

	function delete(){
		$this->begin('index.php?app=timedbuy&ctl=admin_businessactivity&act=index');
		$ids = $this->_request->get_post('id');
		$object = $this->app->model('businessactivity');
		$oGoods = app::get('b2c')->model('goods');
		foreach($ids as $k=>$v){
			$goods_id = $object->getList('gid,disabled',array('id'=>$v));
			$re = $object->delete(array('id'=>$v));
			if($re){
				if($goods_id[0]['gid']&&$goods_id[0]['disabled']=='false'){
					$sql1 = 'select act_type from sdb_b2c_goods where goods_id = "'.$goods_id[0]['gid'].'"';
                    $act_type = $oGoods->db->selectrow($sql1);
                    if($act_type['act_type'] == 'timedbuy'){
						$sql = 'update sdb_b2c_goods set act_type="normal" where goods_id="'.$goods_id[0]['gid'].'"';
                        $rs = $oGoods->db->exec($sql);
						if(!$rs){
							$this->end(false,'删除失败');
						}
                    }
				}
			}
		}
		$this->end(true,'删除成功');
	}

     public function _views(){

		$count_all = $this->app->model('businessactivity')->count();
		$count_dai = $this->app->model('businessactivity')->count(array('status'=>1));
		$count_pass = $this->app->model('businessactivity')->count(array('status'=>2));
		$count_no = $this->app->model('businessactivity')->count(array('status'=>3));
        return array(
                0=>array('label'=>app::get('timedbuy')->_('全部'),'optional'=>false,'filter'=>'','addon'=>$count_all,'href'=>$this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_businessactivity','act'=>'index','view'=>0))),
                1=>array('label'=>app::get('timedbuy')->_('待审核'),'optional'=>false,'filter'=>array('status'=>1),'addon'=>$count_dai,'href'=>$this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_businessactivity','act'=>'index','view'=>1))),
                2=>array('label'=>app::get('timedbuy')->_('审核通过'),'optional'=>false,'filter'=>array('status'=>2),'addon'=>$count_pass,'href'=>$this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_businessactivity','act'=>'index','view'=>2))),
                3=>array('label'=>app::get('timedbuy')->_('审核不通过'),'optional'=>false,'filter'=>array('status'=>3),'addon'=>$count_no,'href'=>$this->router->gen_url(array('app'=>'timedbuy','ctl'=>'admin_businessactivity','act'=>'index','view'=>3))),

				
            );
    }

    public function audit(){
        $sign = $this->_request->get_get('sign');
        $remark = $this->_request->get_post('remark');
        $id = $this->_request->get_post('id');
        $this->begin('index.php?app=timedbuy&ctl=admin_businessactivity&act=index');
        if($sign){
            $item['status'] = 3;
            $item['remark'] = $remark;
        }else{
            $item['status'] = 2;
            $item['remark'] = $remark;
        }
        $business = app::get('timedbuy')->model('businessactivity');
        $rs = $business->update($item,array('id'=>$id));
        if($rs){
            $this->end(true,'操作成功');
        }else{
            $this->end(false,'操作失败');
        }
    }
}