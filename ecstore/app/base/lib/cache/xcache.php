<?php


/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_cache_xcache extends base_cache_abstract implements base_interface_cache 
{

    function __construct() 
    {
        $this->check_vary_list();
    }//End Function

    public function fetch($key, &$result) 
    {
        if(xcache_isset($key)){
            $result = xcache_get($key);
            return true;
        }else{
            return false;
        }
    }//End Function

    public function store($key, $value) 
    {
        return xcache_set($key, $value, 0);
    }//End Function

}//End Class