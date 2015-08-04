<?php

 
class site_application_prototype_themefile extends base_application_prototype_filepath{

    var $current;
    var $path;
    private $_mtime = 0;

    public function detect($theme,$current=null){
        $this->iterator = null;
        $this->target_theme = $theme;
        if($current){
            $this->set_current($current);
        }
        return $this;
    }

    function init_iterator(){
        if(is_dir(THEME_DIR.'/'.$this->target_theme.'/'.$this->path)){
            $this->_mtime = filemtime(THEME_DIR.'/'.$this->target_theme.'/'.$this->path);
            return new DirectoryIterator(THEME_DIR.'/'.$this->target_theme.'/'.$this->path);
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

}
