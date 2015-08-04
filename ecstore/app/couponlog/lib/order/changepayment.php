<?php 
/**
 * 修改优惠券记录
 * @package default
 * @author afei
 */
class couponlog_order_changepayment
{
    public function __construct( &$app ) {
        $this->app = $app;
    }
    
    /*
     * 修改订单信息
     */
    public function generate($data)
    {
    	if(empty($data['order_id'])||empty($data['total_amount'])) return;
    	$coupon = $this->app->model('order_coupon_user');
    	$filter = array('order_id'=>$data['order_id']);
    	if($coupon->count($filter)=== 1){
    		$return = $coupon->update(array('total_amount'=>$data['total_amount']) , $filter);
    	}
    }
}
