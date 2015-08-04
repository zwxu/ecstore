<?php

class timedbuy_activity_update
{
        
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//En

    public function updateActivity(){
		if(app::get('timedbuy')->is_installed()){
			$list = $this->app->model('activity')->getList('*',array('disabled'=>'false','active_status'=>'end'));
			if($list){
				foreach($list as $key=>$value){
					$this->updateGoods($value['act_id']);
					$this->updateBusiness($value['act_id']);
				}
			}
		}
    }

	public function updateGoods($act_id){
		if($act_id){
			$oBusiness = $this->app->model('businessactivity');
			$businesslist = $oBusiness->getList('*',array('aid'=>$act_id));
			$goods_id = array();
			if($businesslist){
				foreach($businesslist as $k=>$v){
					$goods_id[] = $v['gid'];
				}
				if($goods_id){
					app::get('b2c')->model('goods')->update(array('act_type'=>'normal'),array('goods_id|in'=>$goods_id));
				}
			}
		}
	}
	public function updateBusiness($act_id){
		if($act_id){
			$oBusiness = $this->app->model('businessactivity');
			$oBusiness->update(array('disabled'=>'true'),array('aid'=>$act_id));
			$this->app->model('activity')->update(array('disabled'=>'true','act_open'=>'false'),array('act_id'=>$act_id));
		}
	}

	public function updateActiveStatus(){
		if(app::get('timedbuy')->is_installed()){
			$oActivity = $this->app->model('activity');
			$list = $oActivity->getList('*',array('disabled'=>'false'));
			$now = time();
			
			foreach($list as $key=>$value){
				if($value['start_time']<$now&&$now<$value['end_time']){
					$oActivity->update(array('active_status'=>'active'),array('act_id'=>$value['act_id']));
				}elseif($value['end_time']<=$now){
					$oActivity->update(array('active_status'=>'end'),array('act_id'=>$value['act_id']));
				}
			}
		}
	}

    public function close_order(){
        //付款超时，自动关闭交易
        $mdl_order   = kernel::single('b2c_mdl_orders');
        $close_time = time()-(app::get('b2c')->getConf('member.timedbuy_payed_time'))*60;
        $n_close = $mdl_order->getList('order_id',array('createtime|lthan'=>$close_time,'status'=>'active','pay_status'=>0,'order_type'=>'timedbuy'));
        if($n_close){
            $this->do_close($n_close);
        }
    }

    function do_close($n_close){
        $controller   = kernel::single('b2c_ctl_site_order');
        foreach($n_close as $k=>$order_id){
            $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
            if (!$obj_checkorder->check_order_cancel($order_id['order_id'],'',$message))
            {
               //echo json_encode($message);
            }
            
            $sdf['order_id'] = $order_id['order_id'];
            $sdf['op_id'] = '0';
            $sdf['opname'] = 'auto';
            
            $b2c_order_cancel = kernel::single("b2c_order_cancel");
            if ($b2c_order_cancel->generate($sdf, $controller, $message))
            {
                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$order_id['order_id'];
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
               
                $order_id = $order_id['order_id'];
                $buyMod = app::get('timedbuy')->model('memberbuy');
                $businessMod = app::get('timedbuy')->model('businessactivity');
                $buys = $buyMod->getList('*',array('order_id'=>$order_id));
                if($buys){
                  $business = $businessMod->getList('*',array('gid'=>$buys[0]['gid'],'aid'=>$buys[0]['aid']));
                  $buyMod->update(array('disable'=>'true'),array('order_id'=>$order_id));
                  if($business[0]['nums']){
                      $arr['remainnums'] = intval($business[0]['remainnums'])+intval($buys[0]['nums']);
                      $businessMod->update($arr,array('id'=>$business[0]['id']));
                  }
                }
                //end
                //echo json_encode('订单取消成功！');
            }
            else
            {
                //echo json_encode('订单取消失败！');
            }
        }
    }
}