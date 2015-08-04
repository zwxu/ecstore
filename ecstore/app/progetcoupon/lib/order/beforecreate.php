<?php 
/**
 * 送优惠券
 * @package default
 * @author kxgsy163@163.com
 */
class progetcoupon_order_beforecreate
{
    /**
     *  根据订单数据，执行有关送优惠券的方案
     *
     *  @param array &$sdf 订单数据
     *  @return void
     */
    public function generate( &$sdf,$order_sdf,$cart_objects)
    {
        $class = 'progetcoupon_promotion_solutions_getcoupon';
        $o = kernel::single($class);
        
        if( isset($cart_objects['promotion_order_create']) && is_array($cart_objects['promotion_order_create']) && $cart_objects['promotion_order_create'] ) {
            if( isset( $cart_objects['promotion_order_create'][$class] ) && is_array($cart_objects['promotion_order_create'][$class]) ) {
                foreach( $cart_objects['promotion_order_create'][$class] as $row  ) {
                    $o->exec( $row,$sdf['order_id'] );
                }
            }
        }
        
        if( $cart_objects['object']['goods'] && is_array($cart_objects['object']['goods']) ) {
            foreach( $cart_objects['object']['goods'] as $arr_goods  ) {
                if( !$arr_goods['promotion_order_create'][$class] || !is_array($arr_goods['promotion_order_create'][$class]) ) continue;
                foreach( $arr_goods['promotion_order_create'][$class] as $row ) {
                    $o->exec( $row,$sdf['order_id'] );
                }
            }
        }
    }
     /**
      * 订单付款后把优惠券设为可用
      *
      * @see promotion_solutions_getcoupon::exec
      * @param array $sdf 订单数据
      * @param array &$sdf_order 暂未使用
      * @return boolean 更新成功返回true,更新失败返回false
      */ 
     public function order_pay_extends($sdf,&$sdf_order=array()) {
        if($sdf['status'] != 'succ' && $sdf['status'] != "progress"){
            return true;
        }
        $o_mem_coupon = app::get('b2c')->model('member_coupon');
        return $o_mem_coupon->update(array('memc_isvalid'=>'true'),array('memc_gen_orderid'=>$sdf['order_id']));
    }
}
