<?php

  
class gift_cart_object_goods extends b2c_cart_object_goods {
    
    function __construct($app) {
        $this->app = $app;
        $this->o_product = $this->app->model('products');
        $this->o_goods = $this->app->model('goods');
    }
    /**
     * 获取指定id商品相关信息
     *
     * @param int|array $id 商品id 可以传整数或整数组成的数组
     */ 
    public function _get_products($id) {
        if(!$id) return false;
		$router = app::get('site')->router();
		$json = kernel::single('b2c_cart_json');
        if( !is_array($id) ) $id = array($id);
        
        foreach( $id as $_key => $_id ) {
            #if( isset($this->arr_gift[$_id]) ) unset($id[$_key]);
        }
        if( !$id ) return $this->arr_gift;
        
        $arr_gift = $this->o_product->getList_1( '*',array('product_id'=>$id) );
        foreach( (array)$arr_gift as $row ) {
           $gift = $row['gift'];
           if( !$gift ) continue;
           if( !isset($gift['max_limit']) ) $gift['max_limit'] = 9999999;
           if($gift['marketable']=='false') {  //品录芄锍凳ы！
               unset($row);continue;
           }
           
           if( !$this->arr_all_goods_info[$row['goods_id']] ) {
               $tmp = $this->o_goods->getList( 'image_default_id',array('goods_id'=>$row['goods_id']) );
               $this->arr_all_goods_info[$row['goods_id']] = $tmp[0];
           } 
           $arr_goods_info = $this->arr_all_goods_info[$row['goods_id']];

           $aResult[$row['product_id']] = array(
                    'bn' => $row['bn'],
                    'price' => array(
                                'price' => $row['price'],
                                'cost' => $row['cost'],
                                'member_lv_price' => $row['price'],
                                'buy_price' => 0,
                              ),
					'json_price' => array(
                                'price' => $row['price'],
                                'cost' => $row['cost'],
                                'member_lv_price' => $row['price'],
                                'buy_price' => 0,
                              ),
                    'product_id' => $row['product_id'],
                    'goods_id' => $row['goods_id'],
                    'goods_type' => $row['goods_type'],
                    'name'=> $row['name'],
                    'consume_score' => $gift['consume_score'],
                    'max_buy_store' => $gift['max_buy_store'],
                    'gain_score' => intval($row['gain_score']),
                    'type_id' => $row['type_id'],
                    'min_buy' => $row['min_buy'],
                    'spec_info' => $row['spec_info'],
                    'spec_desc' => is_array($row['spec_desc']) ? $row['spec_desc'] : @unserialize($row['spec_desc']),
                    'weight' => $row['weight'],
                    'quantity' => 1,
                    'params' => $row['params'],
                    'floatstore' => $row['floatstore'],
                    'store' => (empty($row['store']) ? ($row['store']===0 ? 0 : 999999) : $row['store']),
                    'freez' => $row['freez'],
                    'default_image' => array(
                                        'thumbnail' => $arr_goods_info['image_default_id'],
                                      ),
                    '_limit' => ($gift['max_limit'] - $gift['real_limit']),
           );
		   //组合JSON格式让JS显示
		   $aResult[$row['product_id']]['json_price']['price'] = $json->get_cur_order($aResult[$row['product_id']]['json_price']['price']);
		   $aResult[$row['product_id']]['json_price']['cost'] = $json->get_cur_order($aResult[$row['product_id']]['json_price']['cost']);
		   $aResult[$row['product_id']]['json_price']['member_lv_price'] = $json->get_cur_order($aResult[$row['product_id']]['json_price']['member_lv_price']);
		   $aResult[$row['product_id']]['json_price']['buy_price'] = $json->get_cur_order($aResult[$row['product_id']]['json_price']['buy_price']);
		   $aResult[$row['product_id']]['url'] = $router->gen_url(array('app'=>'gift','ctl'=>'site_gift','full'=>1,'act'=>'index','arg'=>$aResult[$row['product_id']]['goods_id']));
		   $aResult[$row['product_id']]['thumbnail'] = base_storager::image_path( $aResult[$row['product_id']]['default_image']['thumbnail'],'s');
           $buy_limit = $gift['max_limit'] - $gift['real_limit'];
           $aResult[$row['product_id']]['max_buy_store'] = ($gift['max_buy_store']>$buy_limit ? $buy_limit : $gift['max_buy_store']);
           $this->arr_gift[$row['product_id']] = $aResult[$row['product_id']];
       }
       
       return $this->arr_gift;
    }
    
}
