<?php
/**
 * 立即购买按钮
 * @package default
 * @author ql
 */
class spike_site_product_btn_fastbuy{

    private $file = 'site/product/btn/fastbuy.html';
    private $order = 101;
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
        return $this->order;
    }
    
}