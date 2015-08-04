<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_promotion_getcoupon
{
    
    function __construct( &$app )
    {
        $this->app = $app;
    }
    
    
    public function promotion( $member_id,$cpns_id ) {
        $o = app::get('b2c')->model('coupons');
        $arr = $o->getList( '*',array('cpns_id'=>$cpns_id) );
        if( $arr ) {
            if( !$member_id ) return false; //没有会员id时不赠送
            
            $o_mem_coupon = app::get('b2c')->model('member_coupon');
            foreach( $arr as $row ) {
                if( $row['cpns_type']=='1' ) {  //b类
                    $coupons = $o->downloadCoupon( $row['cpns_id'],1 ,array('0','1'));
                    foreach( $coupons as $code ) {
                        $aSave = array(
                                    'memc_code'=>$code,
                                    'cpns_id'=>$row['cpns_id'],
                                    'member_id'=>$member_id,
                                    'memc_gen_orderid'=>$order_id,
                                    'memc_gen_time'=>time(),
                                );
                        $o_mem_coupon->save($aSave);
                    }
                } else {    //a类
                    $aSave = array(
                                'memc_code'=>$row['cpns_prefix'],
                                'cpns_id'=>$row['cpns_id'],
                                'member_id'=>$member_id,
                                'memc_gen_orderid'=>$order_id,
                                'memc_gen_time'=>time(),
                            );
                    $o_mem_coupon->save($aSave);
                }
            }
        }
    }
}