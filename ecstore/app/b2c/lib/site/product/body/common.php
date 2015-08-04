<?php 

class b2c_site_product_body_common
{
    
    private $file = 'site/product/body/common.html';
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