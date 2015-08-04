<?php

 

class base_status_system extends base_status_abstract{
    
    function get_cache_status(){
        $ret = array(
            'cache.engine'=>CACHE_STORAGE,
            );
            
        if(method_exists(CACHE_STORAGE,'status')){
            foreach(kernel::single(CACHE_STORAGE) as $k=>$v){
                $ret['cache.'.$k] = $v;
            }
        }
        return $ret;
    }
    
    function get_kvstore_status(){
        $ret = array(
            'kvstore.engine'=>KVSTORE_STORAGE,
            );
            
        if(method_exists(KVSTORE_STORAGE,'status')){
            foreach(kernel::single(KVSTORE_STORAGE) as $k=>$v){
                $ret['kvstore.'.$k] = $v;
            }
        }
        return $ret;
    }
    
    function get_mysql_status(){
        $aResult = array(
            'mysql.server_host'=>DB_HOST,
            'mysql.server_dbname'=>DB_NAME,
            'mysql.server_user'=>DB_USER,
        );
        foreach(kernel::database()->select("show status") as $row) 
        {
            $aResult['mysql.'.strtolower($row["Variable_name"])] = $row["Value"];
        }
        return $aResult;
    }

}