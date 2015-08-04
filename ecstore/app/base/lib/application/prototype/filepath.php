<?php

 
class base_application_prototype_filepath extends base_application_prototype_content{

    var $current;
    var $path;
    private $_mtime = 0;

    function init_iterator(){
        if(is_dir($this->target_app->app_dir.'/'.$this->path)){
            if(defined('CUSTOM_CORE_DIR') && is_dir(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path)){
                 $this->_mtime = filemtime(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path);
                 return new DirectoryIterator(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path);
            }else{
                 $this->_mtime = filemtime($this->target_app->app_dir.'/'.$this->path);
                 return new DirectoryIterator($this->target_app->app_dir.'/'.$this->path);
            }
            
        }else{
            return new ArrayIterator(array());
        }
    }

    public function getPathname(){
        return $this->iterator()->getPathname();
    }

    public function current() {
        $this->key = $this->iterator()->getFilename();
        return $this;
    }

    function prototype_filter(){
        $filename = $this->iterator()->getFilename();
        if($filename{0}=='.'){
            return false;
        }else{
            return $this->filter();
        }
    }
    
    function last_modified($app_id){
        $info_arr = array();
        foreach($this->detect($app_id) as $item){
            //$modified = max($modified,filemtime($this->getPathname()));
            //todo: md5
            $filename = $this->getPathname();
            if(is_dir($filename)){
                foreach(utils::tree($filename) AS $k=>$v){
					if (is_dir($v)) continue;
                    $info_arr[$v] = md5_file($v);
                }
            }else{
                $info_arr[$filename] = md5_file($filename);
            }
        }
        ksort($info_arr);
        return md5(serialize($info_arr));
    }



}
