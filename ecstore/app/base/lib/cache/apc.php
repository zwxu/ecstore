<?php


/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_cache_apc extends base_cache_abstract implements base_interface_cache 
{

    function __construct() 
    {
        $this->check_vary_list();
    }//End Function

    public function fetch($key, &$result) 
    {
        $result = apc_fetch($key);
        return $result !== false;
    }//End Function

    public function store($key, $value) 
    {
        return apc_store($key, $value, 0);
    }//End Function

    public function status() 
    {
        $minfo = apc_sma_info();
        $cinfo = apc_cache_info('user');
        foreach($minfo['block_lists'] as $c){
            $blocks[] = count($c);
        }

        $return['缓存命中'] = $cinfo['num_hits'];
        $return['缓存未命中'] = $cinfo['num_misses'];
        $return['可用内存'] = $minfo['avail_mem'];
        return $return;
    }//End Function

}//End Class