<?php
/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_tair extends base_kvstore_abstract implements base_interface_kvstore_base 
{
    private $rs = null;

    function __construct($prefix) 
    {
        $this->rs = tair_init(TAIR_MASTER,TAIR_SLAVE,TAIR_GROUP);
        $this->prefix = $prefix;
    }//End Function

    public function fetch($key, &$value, $timeout_version=null) 
    {
        $store = tair_get($this->rs,$this->create_key($key));
        $store = unserialize($store);
        if($store !== false && $timeout_version < $store['dateline']){
            if($store['ttl'] > 0 && ($store['dateline']+$store['ttl']) < time()){
                return false;
            }
            $value = $store['value'];
            return true;
        }
        return false;
    }//End Function

    public function store($key, $value, $ttl=0) 
    {
        $store['value'] = $value;
        $store['dateline'] = time();
        $store['ttl'] = $ttl;
        $rst = tair_put($this->rs, $this->create_key($key), serialize($store));
        if(!$rst){
            $msg = tair_last_msg();
            trigger_error($msg[1],E_USER_ERROR);    
        }
        return $rst;
    }//End Function

    public function delete($key) 
    {
        return tair_remove($this->rs, $this->create_key($key));
    }//End Function

    public function recovery($record) 
    {
        $key = $record['key'];
        $store['value'] = $record['value'];
        $store['dateline'] = $record['dateline'];
        $store['ttl'] = $record['ttl'];
        $rst = tair_put($this->rs, $this->create_key($key), serialize($store));
        if(!$rst){
            $msg = tair_last_msg();
            trigger_error($msg[1],E_USER_ERROR);    
        }
        return $rst;
    }//End Function

}//End Class
