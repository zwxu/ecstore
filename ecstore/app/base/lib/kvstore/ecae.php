<?php
/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_ecae extends base_kvstore_abstract implements base_interface_kvstore_base 
{
    function __construct($prefix) 
    {
        $this->prefix = $prefix;
    }//End Function

    public function create_key($key) 
    {
        if(isset($key[201])){
            return parent::create_key($key);
        }
        return sprintf("%s/%s/%s", base_kvstore::kvprefix(), $this->prefix, $key);
    }//End Function

    public function store($key, $value, $ttl=0) 
    {
        $data['key'] = $key;
        $data['prefix'] = $this->prefix;
        $data['value'] = $value;
        $data['ttl'] = $ttl;
        $data['dateline'] = time();
        return ecae_kvstore_write($this->create_key($key), $data);
    }//End Function

    public function fetch($key, &$value, $timeout_version=null) 
    {
        if(ecae_kvstore_read($this->create_key($key), $data)){
            if($timeout_version < $data['dateline']){
                if($data['ttl'] > 0 && ($data['dateline'] + $data['ttl']) < time()){
                    return false;
                }
                $value = $data['value'];
                return true;
            }
        }
        return false;
    }//End Function

    public function delete($key) 
    {
        return ecae_kvstore_delete($this->create_key($key));
    }//End Function

    public function recovery($record) 
    {
        $key = $record['key'];
        $data['key'] = $key;
        $data['prefix'] = $this->prefix;
        $data['value'] = $record['value'];
        $data['ttl'] = $record['ttl'];
        $data['dateline'] = $record['dateline'];
        return ecae_kvstore_write($this->create_key($key), $data);
    }//End Function

}//End Class