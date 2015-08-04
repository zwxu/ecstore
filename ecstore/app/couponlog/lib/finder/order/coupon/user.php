<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class couponlog_finder_order_coupon_user
{
    public function __construct( &$app ) {
        $this->app = $app;
    }
    
    
    
    var $detail_basic = '查看';
    function detail_basic($id=null){
        $render = $this->app->render();
        $arr_detail = $this->app->model('order_coupon_user')->dump($id);
        $order_id = $arr_detail['order_id'];
        $arr_order_coupon = $this->app->model('order_coupon_ref')->getList( 'memc_code',array('order_id'=>$order_id) );
        foreach( $arr_order_coupon as $row ) {
            if( $arr_detail['memc_code']==$row['memc_code'] ) continue;
            $coupon[] = $row['memc_code'];
        }
        
        //会员关联数组 此处应该用会员统一接口取会员名称
        $arr_member = app::get('pam')->model('account')->getList( 'login_name,account_id',array('account_id'=>$arr_detail['member_id']) );
        foreach( $arr_member as $val ) {
            $arr_detail['member_name'] = $val['login_name'];
        }
        
        $arr_detail['other_coupon'] = implode('、',(array)$coupon);
        $render->pagedata['info'] = $arr_detail;
        return $render->fetch('admin/finder/log_detail.html');
    }
}