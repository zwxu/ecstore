<?php

 
class b2c_coupon_mem {
    
    
    public function __construct() {
        $this->app = app::get('b2c');
    }
    
    
    public function exchange($id=0, $m_id=0, $point=0,&$params) {
        
        $id = (int)$id;
        if( empty($id) || empty($m_id) ) return false;
        
        $o = $this->app->model('coupons');
        $arr = $o->dump($id);
        if( !$arr['rule']['rule_id'] ) return false;
        if($arr['cpns_status'] === '0') return false;
        $arr_rule_order = $this->app->model('sales_rule_order')->dump( $arr['rule']['rule_id'] );

        if( !$arr_rule_order || $arr_rule_order['to_time']<=time() ) return false;
        if( empty($arr) ) return false;
        if( $point<$arr['cpns_point'] ) return false;
        $arr_m_c['memc_code'] = $o->_makeCouponCode($arr['cpns_gen_quantity']+1, $arr['cpns_prefix'], $arr['cpns_key']);
        if(!$o->isDownloadAble($arr_m_c['memc_code'])) return false; //是否可下载 B类
        $arr['cpns_gen_quantity'] += 1;
        $o->save($arr);
        $arr_m_c['cpns_id'] = $arr['cpns_id'];
        $arr_m_c['member_id'] = $m_id;
        $arr_m_c['memc_used_times'] = 0;
        $arr_m_c['memc_gen_time'] = time();
        $params['cpns_point'] = $arr['cpns_point'];
        $params['memc_code'] = $arr_m_c['memc_code'];
        return $this->app->model('member_coupon')->save($arr_m_c);
    }
    
    public function exchange_delete( $params ) {
        if( $params['memc_code'] ) 
            $this->app->model('member_coupon')->delete($params);
    }
    
    
    
    public function get_list_m($m_id=0) {
        if( empty($m_id) ) return false;
        $filter = array('member_id'=>$m_id);
        $filter['disabled'] = 'false';
        $filter['memc_isvalid'] = 'true';
        $arr = $this->app->model('member_coupon')->_get_list('*', $filter);
        return $arr;
    }
    
    
    
    public function get_list() {
        $sql = "SELECT * FROM `sdb_b2c_coupons` WHERE cpns_point is not null AND cpns_status='1'";
        $arr = $this->app->model('coupons')->db->select($sql);
        foreach( $arr as $key => &$row ) {
            if(empty($row['rule_id'])) continue;
            $arr_rule_info = $this->app->model('sales_rule_order')->dump($row['rule_id'], 'from_time,to_time,member_lv_ids');
            if( $arr_rule_info['to_time']<=time() ) unset($arr[$key] );
            $row['time'] = $arr_rule_info;
        }
        return $arr;
    }
    
    
    /**
     * member_id 与 memc_code 组成唯一标识 
     * 其他会员适用该优惠号码时修改表数据处理（根据号码查询信息、修改)
     **/
    public function use_c($memc_code ='', $uid=null ) {
        if( empty($uid) ) return false;
        if( empty($memc_code) ) return false;
        $o = $this->app->model('member_coupon');
        $couponFlag = $this->app->model('coupons')->getFlagFromCouponCode($memc_code);
        
        if( strtolower($couponFlag)!='b' ) return false;
        $coupons = $this->app->model('coupons')->getCouponByCouponCode($memc_code);
        $coupons = $coupons[0];
        $arr['memc_code'] = $memc_code;
        $arr['memc_used_times'] = 1;
        $arr['cpns_id'] = $coupons['cpns_id'];
        $m_coupon = $o->getList( '*',array('memc_code'=>$memc_code) );
        if( !$m_coupon ) return false;
        $arr['member_id'] = $m_coupon[0]['member_id'];
        return $o->save($arr);
    }
    
    
    
    
    
    
}

