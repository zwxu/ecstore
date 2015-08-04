<?php
/**
 * 立即购买按钮
 * @package default
 * @author kxgsy163@163.com
 */
class fastbuy_site_product_btn_fastbuy
{
    
    private $file = 'site/product/btn/fast.html';
    private $order = 99;
    
    
    
    
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
    	return $this->order;
    }
    
}