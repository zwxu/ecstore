<?php
/**
 * @package default
 * @author kxgsy163@163.com
 */
class b2c_site_product_info_basic
{
	
	private $file = 'site/product/info/basic.html';
    private $order = 90;
    
    
    
    
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
    	return $this->order;
    }
}