<?php
/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_dba extends base_kvstore_abstract implements base_interface_kvstore_base
{
    private $rs = null;
    private $handle = 'db4';

    function __construct($prefix) 
    {
        if(!is_dir(DATA_DIR.'/kvstore/')){
            utils::mkdir_p(DATA_DIR.'/kvstore/');
        }
    }//End Function

    public function fetch($key, &$value, $timeout_version=null) 
    {
        $rs = dba_open(DATA_DIR.'/kvstore/dba.db','r-',$this->handle);
        $store = dba_fetch($this->create_key($key),$rs);
        dba_close($rs);
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
        $rs = dba_open(DATA_DIR.'/kvstore/dba.db','cl',$this->handle);
        $ret = dba_replace($this->create_key($key), serialize($store), $rs);
        dba_close($rs);
        return $ret;
    }//End Function

    public function delete($key) 
    {
        $rs = dba_open(DATA_DIR.'/kvstore/dba.db','wl',$this->handle);
        $ret = dba_delete($this->create_key($key),$rs);
        dba_close($rs);
        return $ret;
    }//End Function

    public function recovery($record) 
    {
        $key = $record['key'];
        $store['value'] = $record['value'];
        $store['dateline'] = $record['dateline'];
        $store['ttl'] = $record['ttl'];
        $rs = dba_open(DATA_DIR.'/kvstore/dba.db','cl',$this->handle);
        $ret = dba_replace($this->create_key($key), serialize($store), $rs);
        dba_close($rs);
        return $ret;
    }//End Function

}//End Class
