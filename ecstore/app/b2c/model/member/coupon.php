<?php

 

class b2c_mdl_member_coupon extends dbeav_model{    
    
    
    
    public function _get_list( $cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null ){ 
        $arr = parent::getList( $cols, $filter,$offset,$limit,$orderby );
        $o_coupon = $this->app->model('coupons');
        $o_rule_order = $this->app->model('sales_rule_order');
        foreach( $arr as &$row ) {
            $arr_coupons_info = $o_coupon->dump($row['cpns_id']);
            $row['coupons_info'] = $arr_coupons_info;
            if(empty($arr_coupons_info['rule']['rule_id'])) continue;
            $arr_rule_info = $o_rule_order->dump($arr_coupons_info['rule']['rule_id'], 'from_time,to_time,member_lv_ids');
            $row['time'] = $arr_rule_info;
        }
        return $arr;
    }
    
}
