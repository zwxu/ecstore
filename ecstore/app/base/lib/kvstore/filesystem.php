<?php

/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_filesystem extends base_kvstore_abstract implements base_interface_kvstore_base
{

    public $header = '<?php exit(); ?>';

    function __construct($prefix) 
    {
        $this->prefix= $prefix;
        $this->header_length = strlen($this->header);
    }//End Function

    public function store($key, $value, $ttl=0) 
    {
        $this->check_dir();
        $data = array();
        $data['value'] = $value;
        $data['ttl'] = $ttl;
        $data['dateline'] = time();
        $org_file = $this->get_store_file($key);
        $tmp_file = $org_file . '.' . str_replace(' ', '.', microtime()) . '.' . mt_rand();
        if(file_put_contents($tmp_file, $this->header.serialize($data))){
            if(copy($tmp_file, $org_file)){
                @unlink($tmp_file);
                return true;
            }
        }
        return false;
    }//End Function

    public function fetch($key, &$value, $timeout_version=null) 
    {
        $file = $this->get_store_file($key);
        if(file_exists($file)){
            $data = unserialize(substr(file_get_contents($file),$this->header_length));
            if(!isset($data['dateline']))   $data['dateline'] = @filemtime($file);  //todo:兼容老版本
            if($timeout_version < $data['dateline']){
                if(isset($data['expire'])){
                    if($data['expire'] == 0 || $data['expire'] >= time()){
                        $value = $data['value'];
                        return true;
                    }
                    return false;
                    //todo:兼容老版本
                }else{
                    if($data['ttl'] > 0 && ($data['dateline']+$data['ttl']) < time()){
                        return false;
                    }
                    $value = $data['value'];
                    return true;
                }
            }
        }
        return false;
    }//End Function

    public function delete($key) 
    {
        $file = $this->get_store_file($key);
        if(file_exists($file)){
            return @unlink($file);
        }
        return false;
    }//End Function

    public function recovery($record) 
    {
        $this->check_dir();
        $key = $record['key'];
        $data['value'] = $record['value'];
        $data['dateline'] = $record['dateline'];
        $data['ttl'] = $record['ttl'];
        $org_file = $this->get_store_file($key);
        $tmp_file = $org_file . '.' . str_replace(' ', '.', microtime()) . '.' . mt_rand();
        if(file_put_contents($tmp_file, $this->header.serialize($data))){
            if(copy($tmp_file, $org_file)){
                @unlink($tmp_file);
                return true;
            }
        }
        return false;
    }//End Function

    private function check_dir() 
    {
        if(!is_dir(DATA_DIR.'/kvstore/'.$this->prefix)){
            utils::mkdir_p(DATA_DIR.'/kvstore/'.$this->prefix);
        }
    }//End Function

    private function get_store_file($key) 
    {
        return DATA_DIR.'/kvstore/'.$this->prefix.'/'.$this->create_key($key).'.php';
    }//End Function
}//End Class
