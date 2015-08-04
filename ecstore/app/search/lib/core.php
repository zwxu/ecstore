<?php

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../'));
require_once('Zend/Search/Lucene.php');
class search_core
{
    const SEARCH_SEGMENT_DEFAULT = '__SEARCH_SEGMENT_DEFAULT__';

    private static $_instance = array();

    private static $_segment = array();

    static public function instance($server)
    {
        if(!isset(self::$_instance[$server])){
            $service = kernel::service('search_server.'.$server);
            if(app::get('base')->getConf('server.search_server.'.$server) && $service instanceof search_interface_model){
                self::$_instance[$server] = $service;
            }else{
                return false;
            }
        }
        return self::$_instance[$server];
    }//End Function

    static public function segment($type='')
    {
        $type = ($type) ? $type : self::SEARCH_SEGMENT_DEFAULT;
        if(!isset(self::$_instance[$type])){
            $service_name = ($type == self::SEARCH_SEGMENT_DEFAULT) ? 'search_segment' : 'search_segment.' . $type;
            $service = kernel::service($service_name);
            self::$_segment[$type] = $service;
        }
        return self::$_segment[$type];
    }//End Function

}//End Class