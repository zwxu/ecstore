<?php

/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_cache_ecae extends base_cache_abstract implements base_interface_cache  
{

    function __construct() 
    {
        $this->check_vary_list();
    }//End Function

    public function fetch($key, &$result) 
    {
        $result = ecae_cache_read($key);
        return $result !== false;
    }//End Function

    public function store($key, $value) 
    {
        return ecae_cache_write($key, $value);
    }//End Function

    public function status() 
    {
        $status = ecae_cache_stats();
        foreach($status AS $key=>$value){
            $return[$key.'=>缓存获取'] = $value['cmd_get'];
            $return[$key.'=>缓存存储'] = $value['cmd_set'];
            $return[$key.'=>可使用缓存'] = $value['limit_maxbytes']/1024/1024 ." MB";
        }
        return $return;
    }//End Function
}//End Class