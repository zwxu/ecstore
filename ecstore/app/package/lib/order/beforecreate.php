<?php 
/**
 * 修改订单信息
 * @package default
 * @author kxgsy163@163.com
 */
class package_order_beforecreate
{
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /*
     * 修改订单信息
     */
    public function generate( &$sdf )
    {
        $o = $this->app->model('sell_log');
        $member_id = $sdf['member_id'];
        $order_id = $sdf['order_id'];
        foreach( (array)$sdf['order_objects'] as $row ) {
            if( $row['obj_type']!=kernel::single('package_cart_object_package')->get_type() ) continue;
            $aSave = array(
                'member_id'=>$member_id,
                'order_id'=>$order_id,
                'giftpackage_id'=>$row['goods_id'],
                'quantity'=>$row['quantity'],
            );
            $o->insert( $aSave );
        }
    }
    #End Func
}