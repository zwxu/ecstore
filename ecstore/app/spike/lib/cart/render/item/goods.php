<?php

 
/**
 * cart render item goods
 * $ 2010-05-06 11:23 $
 */
 
class spike_cart_render_item_goods
{
    public $app = 'spike';
    public $file = 'site/cart/item/goods.html';
    public $index = 99; // 所处位置
    
    
    public function _get_minicart_view() {
        $arr = array(
            'file'=>'site/cart/mini/item/goods.html',
            'index'=>99,
        );
        return $arr;
    }
}

