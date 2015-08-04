<?php
/**
 * @package default
 * @author kxgsy163@163.com
 */
class couponlog_ctl_admin_log extends desktop_controller
{
    /*
    function __construct( &$app )
    {
        $this->app = $app;
        parent
    }
    */
    
    /*
     * 使用记录列表
     */
    public function index()
    {
        $this->finder('couponlog_mdl_order_coupon_user',array(
            'title'=>app::get('b2c')->_('优惠券使用记录'),
            'use_buildin_recycle'=>false,
            ));
    }
    #End Func
}

