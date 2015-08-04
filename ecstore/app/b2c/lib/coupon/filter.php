<?php 

class b2c_coupon_filter
{
    
    function __construct( &$app )
    {
        $this->app = $app;
    }
    
    /*
     * 返回未过期的优惠券id数组
     */
    public function get_coupon($store_id=null)
    {
         //'cpns_type'=>'1'  所有优惠券不仅限于b类  产品述
        if($store_id){
            //  增加店铺id过滤
            $filter = array(
                'filter_sql'=>' locate(\''.$store_id.'\',sdb_b2c_coupons.store_id) > 0  And cpns_status=\'1\'' ,
            );
          
            $arr = $this->app->model('coupons')->getList('cpns_id,rule_id',$filter); 

        }else  {
            $arr = $this->app->model('coupons')->getList( 'cpns_id,rule_id',array('cpns_status'=>'1') );
        }


        foreach( (array)$arr as $row ) {
            $cpns_id[$row['rule_id']] = $row['cpns_id'];
        }
        if( !$cpns_id ) return array('-1');
        $rule_id = array_keys($cpns_id); 
        
        //获取可以应用的优惠券
        $arr = $this->app->model('sales_rule_order')->getList( 'rule_id',array('rule_id'=>$rule_id,'to_time|than'=>time()) );
        $tmp = $cpns_id;
        $cpns_id = array();
        foreach( (array)$arr as $row ) {
            $cpns_id[] = $tmp[$row['rule_id']];
        }
        if( empty($cpns_id) ) $cpns_id = array('-1'); 
        
        return $cpns_id;
    }
    #End Func
    
}