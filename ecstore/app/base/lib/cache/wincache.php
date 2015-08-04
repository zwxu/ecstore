<?php


/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_cache_wincache extends base_cache_abstract implements base_interface_cache 
{

    function __construct() 
    {
        $this->check_vary_list();
    }//End Function

    public function fetch($key, &$result) 
    {
        $result = wincache_ucache_get($key, $return);
        return $return;
    }//End Function

    public function store($key, $value) 
    {
        return wincache_ucache_set($key, $value, 0);
    }//End Function

    public function status() 
    {
        $status = wincache_ucache_info(true);
        $return['缓存命中'] = $status['total_hit_count'];
        $return['缓存未命中'] = $status['total_miss_count'];
        return $return;
    }//End Function

}//End Class