<?php

 
/**
 * cart render item goods
 * $ 2010-05-06 11:23 $
 */
 
class business_cart_render_item_goods_business
{
    public $app = 'business';
    public $file = 'site/cart/item/goods/business.html';
    public $index = 90; // λ
    
    /**
     * 迷你购物车模板配置
     *
     * @return array
     */ 
    public function _get_minicart_view() {
        $arr = array(
            'file'=>'site/cart/mini/item/goods/business.html',
            'index'=>90,
        );
        return $arr;
    }
    
}
