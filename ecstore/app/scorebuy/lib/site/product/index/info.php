<?php
/**
 * @package default
 * @author kxgsy163@163.com
 */
class scorebuy_site_product_index_info
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