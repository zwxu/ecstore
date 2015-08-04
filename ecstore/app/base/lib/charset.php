<?php

 
/*
 * @package base
 * @license 
 */
 
class base_charset{
    
    private $_instance = null;

    function __construct() 
    {
        $obj = kernel::service('base_charset');
        if($obj instanceof base_charset_interface){ 
            $this->set_instance($obj);
        }
    }//End Function

    public function set_instance(&$obj) 
    {
        $this->_instance = $obj;
    }//End Function
    
    public function get_instance() 
    {
        return $this->_instance;
    }//End Function

    public function local2utf($strFrom,$charset='zh') 
    {
        return $this->_instance->local2utf($strFrom, $charset);
    }//End Function

    public function utf2local($strFrom,$charset='zh') 
    {
        return $this->_instance->utf2local($strFrom, $charset);
    }//End Function

    public function u2utf8($str) 
    {
        return $this->_instance->u2utf8($str);
    }//End Function

    public function utf82u($str) 
    {
        return $this->_instance->utf82u($str);
    }//End Function
	
	public function replace_utf8bom( $str )  
	{
		return $this->_instance->replace_utf8bom($str);
	}
	
	public function is_utf8( $str )
	{
		return $this->_instance->is_utf8($str);
	}
}
