<?php 
class package_site_goods{
    private $goods_info;
    
    function __construct(&$app){
        $this->o_goods = app::get('b2c')->model('goods');
        $this->o_products = app::get('b2c')->model('products');
    }
    
    public function get_goods_info($arr_goods_id){
        if( !$arr_goods_id || !is_array($arr_goods_id) ) return false;
        $arr = array();
        foreach ($arr_goods_id as $key => $id) {
            if( !$this->goods_info[$id] )
                $arr[$key] = $this->o_goods->dump( $id );
            else 
                $arr[$key] = $this->goods_info[$id];
            $arr[$key]['products'] = $this->get_products_info($id);
        }
        return $arr;
    }
    
    public function get_products_info($goods_id){
        return $this->o_products->getList('product_id,spec_desc', array('goods_id'=>$goods_id) );
    }
}