<?php 
/**
 * 修改订单信息
 * @package default
 * @author kxgsy163@163.com
 */
class couponlog_order_beforecreate
{
    public function __construct( &$app ) {
        $this->app = $app;
    }
    
    /*
     * 修改订单信息
     */
    public function generate( &$sdf,$order_sdf,$cart_objects )
    {
        $order_id = $sdf['order_id'];
        $total_amount = $sdf['total_amount'];
        $member_id = $sdf['member_id'];
        $usetime = time();
        if( isset($cart_objects['object']['coupon']) && $cart_objects['object']['coupon'] && is_array($cart_objects['object']['coupon']) ) {
            $o = $this->app->model('order_coupon_user');
            $o_ref = $this->app->model('order_coupon_ref');
            foreach( $cart_objects['object']['coupon'] as $row ) {
                if( !$row['used'] ) continue;
                
                $aSave = array(
                            'order_id' => $order_id,
                            'cpns_id'  => $row['cpns_id'],
                            'cpns_name' => $row['name'],
                            'usetime'  => $usetime,
                            'memc_code' => $row['coupon'],
                            'cpns_type' => $row['cpns_type'],
                            'member_id' => $member_id,
                            'total_amount' => $total_amount,
                        );
                $o->save( $aSave );
                $arr_order_ref = array(
                    'order_id'  => $order_id,
                    'memc_code' => $row['coupon'],
                );
                $o_ref->save($arr_order_ref);
            }
        }
    }
}
