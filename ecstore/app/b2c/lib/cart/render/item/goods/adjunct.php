<?php

/**
 * cart render item goods
 * $ 2010-05-06 11:23 $
 */
 
class b2c_cart_render_item_goods_adjunct
{
    public $app = 'b2c';
    public $file = 'site/cart/item/goods/adjunct.html';
    public $index = 99; // λ
    
    
    
    public function _get_minicart_view() {
        $arr = array(
            'file'=>'site/cart/mini/item/goods/adjunct.html',
            'index'=>99,
        );
        return $arr;
    }
}

