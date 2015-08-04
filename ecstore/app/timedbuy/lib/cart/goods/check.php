<?php
	
class timedbuy_cart_goods_check{

	public function __construct(&$app){
        $this->app = $app;
    }

	function check_isTimedbuy($aData,&$msg){

		$goods_id=$aData['goods']['goods_id'];
		$oGoods = app::get('b2c')->model('goods');
		$businessAct = app::get('timedbuy')->model('businessactivity');
		if($goods_id){
			$act_type = $oGoods->getList('act_type',array('goods_id'=>$goods_id));
			$business = $businessAct->getList('gid,id',array('gid'=>$goods_id,'disabled'=>'false'));
			if($act_type[0]['act_type']=='timedbuy'&&$business){
				$msg = '限时抢购的商品不能加入购物车';
				return false;
			}
		}
		return true;
	}

	function checkTimedBuyTime($aData,&$msg){
		$goods_id=$aData['goods']['goods_id'];
		$oGoods = app::get('b2c')->model('goods');
		$activity  = app::get('timedbuy')->model('activity');
		$businessAct = app::get('timedbuy')->model('businessactivity');
		if($goods_id){
			$act_type = $oGoods->getList('act_type',array('goods_id'=>$goods_id));
			$business = $businessAct->getList('*',array('gid'=>$goods_id,'disabled'=>'false'));
			if($act_type[0]['act_type']=='timedbuy'&&$business[0]['status']==2){
				$activityInfo = $activity->getList('*',array('act_id'=>$business[0]['aid']));
				$activityInfo = $activityInfo[0];
				if($activityInfo['act_open']=='true'&&$activityInfo['active_status']=='active'){
					$now = time();
					if($activityInfo['start_time']<$now&&$now<$activityInfo['end_time']){
						return true;
					}else{
						$msg = '活动尚未开始';
						return false;
					}
				}
			}
		}
		return true;
	}
}