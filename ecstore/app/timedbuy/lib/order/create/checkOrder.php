<?php
class timedbuy_order_create_checkOrder{
    
    function __construct($app)
    {
        $this->app = $app;
		$this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
		$this->member_ident = kernel::single("base_session")->sess_id();
    }

    function check_products($order_data, &$messages){
		   $arr_goods = $order_data['order_objects'];
		   $goods_num_arr = array();
		   foreach($arr_goods as $k=>$v){
			   $goods_num_arr[$v['goods_id']]['quantity'] += $v['quantity'];
			   $goods_num_arr[$v['goods_id']]['name'] = $v['name'];
		   }
		  foreach($goods_num_arr as $k=>$v){
				$flag = $this->check($k,$v['quantity'],$v['name'],$messages);
				if(!$flag){
					return false;
				}

		  }
		  return true;

    }

	function check($gid,$quantity,$name,&$msg){
		$arr = kernel::single('timedbuy_cart_process_goods')->checkgoods($gid);
		if(!$arr){
            return true;
        }
		$memberbuy = $this->app->model('memberbuy');
        $buys = $memberbuy->getList('*',array('member_id'=>$this->arr_member_info['member_id'],'gid'=>$gid,'aid'=>$arr['aid'],'disable'=>'false'));
		$num=0;
        foreach($buys as $key=>$value){
            $num = $num + $value['nums'];
        }
		if( $arr['presonlimit'] && $arr['presonlimit']<$num+$quantity ) {
            $msg = '商品'.$name.'的累计购买数量超出每人限购数量！' ;
            return false;
        }
        if( $arr['nums'] && $arr['remainnums']<$quantity ) {
            $msg = '商品'.$name.'的购买数量大于活动剩余库存！';
            return false;
        }
		return true;
	}
}