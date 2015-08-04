<?php

/**
 * 获取购物车信息 处理活动商品
 * $ 2010-04-28 20:29 $
 */
class timedbuy_cart_process_goods implements b2c_interface_cart_process {

     private $app;

    public function __construct(&$app){
        $this->app = $app;
    }

    
    public function get_order() {
        return 62;
    }

    public function process($aData,&$aResult=array(),$aConfig=array(),$sign=null){
        $this->filter($aData,$aResult,$aConfig,$sign);
        app::get('b2c')->model('cart')->count_objects($aResult);

    }

    public function filter($aData,&$aResult=array(),$aConfig=array(),$sign=null){
        $oGoods = app::get('b2c')->model('goods');
		if($sign){
			foreach($aResult['object']['goods'] as $k=>$v){
				$gid = $v['params']['goods_id'];
				$act_type = $oGoods->getList('act_type',array('goods_id'=>$gid));
				if($act_type[0]['act_type']=='timedbuy'){
					$re = $this->checkgoods($gid);
					if($re){
						if(!$re['nums']||$re['remainnums']>0){
							foreach($v['obj_items']['products'] as $key=>$value){
								$aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['price']=$re['price'];
								$aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['buy_price']=$re['price'];
								$aResult['object']['goods'][$k]['obj_items']['products'][$key]['price']['member_lv_price']=$re['price'];
							}
							$aResult['inAct'] = 'true';
						}
					}
				}
			}
		}
    }

    public function checkgoods($gid){
        $businessactivity = app::get('timedbuy')->model('businessactivity');
        $activity  = app::get('timedbuy')->model('activity');
        $businessInfo = $businessactivity->getList('*',array('gid'=>$gid,'disabled'=>'false'));
        $businessInfo = $businessInfo[0];
        if($businessInfo&&$businessInfo['status']==2){
            $activityInfo = $activity->getList('*',array('act_id'=>$businessInfo['aid']));
            $activityInfo = $activityInfo[0];
            if($activityInfo['act_open']=='true'&&$activityInfo['active_status']=='active'){
                $now = time();
                if($activityInfo['start_time']<$now&&$now<$activityInfo['end_time']){
                    return $businessInfo;
                }else{
                    return false;
                }
            }else{
                return  false;
            }
        }else{
            return false;
        }

    }
}