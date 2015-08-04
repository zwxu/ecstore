<?php


class site_route_static
{
    static private $_genurl_map;
    private $_kvprefix = 'site_route/statics';
    
    public function set_dispatch($key, $val) 
    {
        return base_kvstore::instance($this->_kvprefix.'/dispatch')->store($key, $val);
    }//End Function

    public function get_dispatch($key) 
    {
        if(base_kvstore::instance($this->_kvprefix.'/dispatch')->fetch($key, $val)){
           return $val;
        }else{
           return false;
        }
    }//End Function

    public function del_dispatch($key) 
    {
        return base_kvstore::instance($this->_kvprefix.'/dispatch')->delete($key);
    }//End Function

    public function set_genurl($key, $val) 
    {
        $this->refresh_genurl_map();
        return base_kvstore::instance($this->_kvprefix.'/genurl')->store($key, $val);
    }//End Function

    public function get_genurl($key) 
    {
        /*
        if(base_kvstore::instance($this->_kvprefix.'/genurl')->fetch($key, $val)){
           return $val;
        }else{
           return false;
        }
        */
        if(!isset(self::$_genurl_map)){
            self::$_genurl_map = array();
            base_kvstore::instance($this->_kvprefix)->fetch('genurl_map', self::$_genurl_map);
        }
        if(isset(self::$_genurl_map[$key])){
            app::get('site')->getConf('statics.genurl_map_modify');     //使cache过期检查生效
            return self::$_genurl_map[$key];
        }else{
            return false;
        }
    }//End Function

    public function del_genurl($key) 
    {
        $this->refresh_genurl_map();
        return base_kvstore::instance($this->_kvprefix.'/genurl')->delete($key);
    }//End Function

    public function refresh_genurl_map() 
    {
        $maps = app::get('site')->model('route_statics')->getList('static, url', array('enable'=>'true'));
        foreach($maps AS $map){
            $data[$map['url']] = $map['static'];
        }
        base_kvstore::instance($this->_kvprefix)->store('genurl_map', $data);
        app::get('site')->setConf('statics.genurl_map_modify', microtime());
    }//End Function

}//End Class