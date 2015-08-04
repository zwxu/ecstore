<?php
/**

 * 加入购物车按钮
 * @package default
 * @author kxgsy163@163.com
 */
class b2c_site_product_btn_buy
{
    
    private $file = 'site/product/btn/buy.html';
    private $order = 80;
    
    
    
    
    
    public function __get($var)
    {
        return $this->$var;
    }

	public function get_type(){
		return 'buy';
	}
    #End Func
    
    public function get_order() {
    	return $this->order;
    }
    
}