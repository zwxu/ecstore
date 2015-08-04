<?php
/**
 * 立即购买按钮
 * @package default
 * @author ql
 */
class groupbuy_site_product_btn_fastbuy{

    private $file = 'site/product/btn/fastbuy.html';
    private $order = 100;
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
        return $this->order;
    }
    
}