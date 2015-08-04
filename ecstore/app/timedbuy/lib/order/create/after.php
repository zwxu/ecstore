<?php
class timedbuy_order_create_after{
    
    function __construct($app)
    {
        $this->app = $app;
    }

    public function get_order(){
        return 95;
    }

    public function generate(&$item){
        $object = kernel::single('timedbuy_cart_process_goods');
        $memberbuy = app::get('timedbuy')->model('memberbuy');
        $business = app::get('timedbuy')->model('businessactivity');
		$oGoods = app::get('b2c')->model('goods');
        foreach($item['order_objects'] as $key=>$value){
			$act_type = $oGoods->getList('act_type',array('goods_id'=>$value['goods_id']));
			$inAct = false;
            $inAct = $object->checkgoods($value['goods_id']);
			$data = array();
            if($inAct&&$item['order_type']=='timedbuy'){
				if(!$re['nums']||$re['remainnums']>0){
					$data['gid'] = $value['goods_id'];
					$data['aid'] = $inAct['aid'];
					$data['member_id'] = $item['member_id'];
					$data['order_id'] = $item['order_id'];
					$data['nums'] = $value['quantity'];
					$data['bid'] = $inAct['id'];
					$rs = $memberbuy->insert($data);
					if($inAct['nums']){
						$arr['remainnums'] = intval($inAct['remainnums'])-intval($value['quantity']);
						$re = $business->update($arr,array('id'=>$inAct['id']));
					}
				}
            }
        }
        return true;
    }
}