<?php

 
/**
 * cart render item goods
 * $ 2010-05-06 11:23 $
 */
class gift_cart_render_item_gift
{
    public $app = 'gift';
    public $file = 'site/cart/item/gift.html';
    public $index = 80; // 所处位置
    
    /**
     * 迷你购物车模板配置
     *
     * @return array
     */
    public function _get_minicart_view() {
        $arr = array(
            'file'=>'site/cart/mini/item/gift.html',
            'index'=>80,
        );
        return $arr;
    }
}
