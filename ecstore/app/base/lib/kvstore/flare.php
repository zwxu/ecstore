<?php
/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_flare extends base_kvstore_abstract implements base_interface_kvstore_base
{
    static private $_cacheObj;

    function __construct($prefix) 
    {
        $this->connect();
        $this->prefix = $prefix;
    }//End Function

    public function connect() 
    {
        if(!isset(self::$_cacheObj)){
            if(defined('KVSTORE_MEMCACHE_CONFIG') && constant('KVSTORE_MEMCACHE_CONFIG')){
                self::$_cacheObj = new Memcache;
                $config = explode(',', KVSTORE_MEMCACHE_CONFIG);
                foreach($config AS $row){
                    $row = trim($row);
                    if(strpos($row, 'unix:///') === 0){
                        continue;   //暂不支持
                    }else{
                        $tmp = explode(':', $row);
                        self::$_cacheObj->addServer($tmp[0], $tmp[1]);
                    }
                }
            }else{
                trigger_error('can\'t load KVSTORE_MEMCACHE_CONFIG, please check it', E_USER_ERROR);
            }
            //检查是否有可用kv服务器
            $status = self::$_cacheObj->getExtendedStats();
            if( !$status || !is_array($status) ) {
                trigger_error('can\'t connect to kv-storage system', E_USER_ERROR);
            }
            foreach($status as $key => $value)
            {
                if($value === false) {
                    unset($status[$key]);
                }
            }
            if(count($status) == 0){
                trigger_error('can\'t connect to kv-storage system', E_USER_ERROR);
            }

        }
    }//End Function

    public function fetch($key, &$value, $timeout_version=null) 
    {
        $store = self::$_cacheObj->get($this->create_key($key));
        if($store !== false){
            if($timeout_version < $store['dateline']){
                if($store['ttl'] > 0 && ($store['dateline']+$store['ttl']) < time()){
                    return false;
                }
                $value = $store['value'];
                return true;
            }
        }
        return false;
    }//End Function

    public function store($key, $value, $ttl=0) 
    {
        $store['key'] = $key;               //todo:持久冗余
        $store['prefix'] = $this->prefix;   //todo:持久冗余
        $store['value'] = $value;
        $store['dateline'] = time();
        $store['ttl'] = $ttl;
        return self::$_cacheObj->set($this->create_key($key), $store, MEMCACHE_COMPRESSED, 0);
    }//End Function

    public function delete($key) 
    {
        return self::$_cacheObj->delete($this->create_key($key));
    }//End Function

    public function recovery($record) 
    {
        $key = $record['key'];
        $store['value'] = $record['value'];
        $store['dateline'] = $record['dateline'];
        $store['ttl'] = $record['ttl'];
        return self::$_cacheObj->set($this->create_key($key), $store, MEMCACHE_COMPRESSED, 0);
    }//End Function

}//End Class
