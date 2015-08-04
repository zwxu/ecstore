<?php 
/**
 * @package default
 * @author kxgsy163@163.com
 */
class package_cart_render_item_package
{
    
    public $app = 'package';
    public $file = 'site/cart/item/giftpackage.html';
    public $index = 81; // 所处位置
    
    public function _get_minicart_view() {
        $arr = array(
            'file'=>'site/cart/mini/item/giftpackage.html',
            'index'=>81,
        );
        return $arr;
    }
    
}