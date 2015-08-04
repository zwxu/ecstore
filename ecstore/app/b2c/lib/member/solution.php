<?php

 
class b2c_member_solution{
    private $filter;
    
    
    public function __construct( $app ) {
        $this->app = $app;
        $this->db = kernel::database();
    }
    
    function get_all( $mid ){
        $return['goods'] = $this->get_prefilter( $mid );
        $return['order'] = $this->get_postfilter( $mid );
        return $return;
    }
    
    
    function get_all_to_array( $mid ) {
       $goods = $this->get_prefilter( $mid );
       $order = $this->get_postfilter( $mid );
       is_array($goods) or $goods=array();
       is_array($order) or $order=array();
       return array_merge( $goods, $order);
       
    }
    
    
    function get_prefilter( $mid ){
       $this->destory();
       $this->filter['time'] = time();
        $this->filter['member_lv_id'] = (int)$mid;
        if( empty($this->filter['member_lv_id']) ) return false;
        $table = $this->db->prefix . 'b2c_sales_rule_goods';
        $this->filter['status'] = 'true';
        $sql = sprintf( 'SELECT `rule_id`,`name`,`description`,`from_time`,`to_time` FROM %s WHERE 1 AND %s', $table, $this->_filter() );
        return $this->db->select($sql);
    }
    
    function get_postfilter( $mid ){
       $this->destory();
       $this->filter['time'] = time();
        $this->filter['member_lv_id'] = (int)$mid;
        if( empty($this->filter['member_lv_id']) ) return false;
        $table = $this->db->prefix . 'b2c_sales_rule_order';
        $this->filter['rule_type'] = 'N';
        $this->filter['status'] = 'true';
        $sql = sprintf( 'SELECT `rule_id`,`name`,`description`,`from_time`,`to_time`,`rule_type` FROM %s WHERE 1 AND %s', $table, $this->_filter() );
        return $this->db->select($sql);
    }
    
    private function _filter(){
        $where = array(1);
        extract($this->filter);
        if( $time ) 
            $where[] = sprintf( 'from_time<=%s AND to_time>=%s', $time, $time );
        
        if( $member_lv_id ) 
            $where[] = sprintf( 'find_in_set(\'%s\',member_lv_ids)', $member_lv_id );
        
        if( $rule_type )
           $where[] = sprintf( 'rule_type=\'%s\'', $rule_type );
        if( $status )
            $where[] = sprintf('status=\'%s\'',$status);
        return implode( ' AND ', $where );
    }
    
    private function destory() {
       $this->filter = null;
    }
    
}
