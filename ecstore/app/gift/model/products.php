<?php

 
    
    
    
class gift_mdl_products extends b2c_mdl_products {
    
    
    public function get_schema(){
        $this->app = app::get('b2c');
        $columns = parent::get_schema();
        return $columns;
    }

    
    public function table_name($real=false){
        $app_id = $this->app->app_id;
        $table_name = substr(get_parent_class($this),strlen($app_id)+5);
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_'.$table_name;
        }else{
            return $table_name;
        }
    }
    
    
    function dump_1($filter,$field = '*',$subSdf = null){
        $arr = $this->getList( $field,$filter );
        $arr = $arr[0];
        if( $arr['product_id'] ) {
            $filter = array();
            $filter['product_id'] = $arr['product_id'];
            $tmp = app::get('gift')->model('ref')->getList( '*',$filter);
            if( empty( $tmp[0] ) ) return false;
            $arr['gift'] = $tmp[0];
        }
        return $arr;
    }
    
    function getList($cols='*',$filter=array(),$start=0,$limit=-1,$orderType=null){
        $filter = $this->_filter_product( $filter );
        return parent::getList( $cols, $filter, $start, $limit, $orderType );
    }
    
    public function count ( $filter=null ) {
        return parent::count( $this->_filter_product( $filter ) );
    }
    
    private function _filter_product( $filter ) {
        //选择赠品时 选择全部 会显示所有赠品 处理 time：2010-11-25 11:51
        if( (!$filter['product_id']&&!$filter['goods_id']) || $filter['product_id'][0]=='_ALL_' ) {
            $arr = app::get('gift')->model('ref')->getList('product_id',array());
            $filter['product_id'] = array_map( 'current',(array)$arr );
        } else {
            #$arr = app::get('gift')->model('ref')->getList('product_id',array());
            #$filter['product_id'] = array_map( 'current',(array)$arr );
            #if( !app::get('gift')->model('ref')->getList('product_id',$filter) ) {
            #    return array('product_id'=>'false');
            #}
        }
        return $filter;
    }
    
    function getList_1 ( $field='*', $filter=array() ) {
        $arr = $this->getList( $field,$filter );
        foreach( $arr as &$row ) {
            if( $row['product_id'] ) {
                $filter = array();
                $filter['product_id'] = $row['product_id'];
                $tmp = app::get('gift')->model('ref')->getList( '*',$filter);
                if( empty( $tmp[0] ) ) continue;
                $row['gift'] = $tmp[0];
            }
        }
        return $arr;
    }
    
    
}