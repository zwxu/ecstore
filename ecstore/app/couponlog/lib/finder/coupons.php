<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class couponlog_finder_coupons 
{
    public function __construct( &$app ) {
        $this->app = $app;
    }
    
    
    
    var $detail_log = '使用历史';
    function detail_log($cpns_id=null){
        $len = 30;
        $arr = $this->app->model('order_coupon_user')->getList( '*',array('cpns_id'=>$cpns_id),0,$len,'id desc' );
        foreach( (array)$arr as $key => $value ) {
            if( $value['member_id'] )
                $arr[$key]['member_name'] = &$member[$value['member_id']];
            $arr[$key]['other_coupon'] = &$coupon[$value['order_id']];
        }
        if( $member ) { //会员关联数组 此处应该用会员统一接口取会员名称
            $arr_member = app::get('pam')->model('account')->getList( 'login_name,account_id',array('account_id'=>array_keys($member)) );
            foreach( $arr_member as $val ) {
                $member[$val['account_id']] = $val['login_name'];
            }
        }
        $render = $this->app->render();
        
        if( count($arr)==$len ) {//filter[cpns_id]=1  //更多历史链接 
            $render->pagedata['detail_url'] = app::get('desktop')->router()->gen_url( array('app'=>'couponlog','ctl'=>'admin_log','act'=>'index','filter[cpns_id]'=>$cpns_id) );
        }
        
        if( $coupon ) { //同时使用的优惠券
            $arr_order_coupon = $this->app->model('order_coupon_ref')->getList( '*',array('order_id'=>array_keys($coupon)) );
            foreach( $arr_order_coupon as $val ) {
                $coupon[$val['order_id']][] = $val['memc_code'];
            }
            foreach( $coupon as $key => $val ) {
                if( $val )  $coupon[$key] = implode('、',$val);
            }
        }
        
        $render->pagedata['couponloglist'] = $arr;
        return $render->fetch('admin/finder/log.html');
    }
}