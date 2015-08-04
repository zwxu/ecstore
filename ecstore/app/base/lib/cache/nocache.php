<?php


/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_cache_nocache extends base_cache_abstract implements base_interface_cache
{
    public $name = '不使用cache';

    function __construct() 
    {

    }//End Function

    public function get_modified($type, $key) 
    {
        return false;
    }//End Function

    public function set_modified($type, $key, $time=null) 
    {
        return false;
    }//End Function

    public function fetch($key, &$result) 
    {
        return false;
    }//End Function

    public function store($key, $value) 
    {
        return false;
    }//End Function
    
}//End Class
