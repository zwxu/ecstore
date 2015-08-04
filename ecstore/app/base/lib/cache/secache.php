<?php


/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_cache_secache extends base_cache_secache_model implements base_interface_cache
{

    function __construct() 
    {
        $workat = DATA_DIR . '/cache';
        if(!is_dir($workat))    utils::mkdir_p($workat);        
        $this->workat($workat . '/secache');
        $this->check_vary_list();
    }//End Function

    public function status() 
    {
        $data = parent::status($cur, $total);
        foreach($data AS $val){
            $status[$val['name']] = $val['value'];
        }
        //$status[app::get('base')->_('已使用缓存')] = $cur;
        $status['可使用缓存'] = $total;
        return $status;
    }//End Function
    
}//End Class
