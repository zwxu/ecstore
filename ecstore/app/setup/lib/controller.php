<?php

 
class setup_controller extends base_controller{

    function __construct(&$app) 
    {
        parent::__construct($app);
        $helper = kernel::single('base_view_helper');
        foreach(get_class_methods($helper) as $method){
            $this->_compiler()->set_view_helper($method,'base_view_helper');
        }
    }//End Function

    function display($tmpl_file,$app_id=null){
        array_unshift($this->_files,$tmpl_file);
        $this->_vars = $this->pagedata;

        if($p = strpos($tmpl_file,':')){
            $object = kernel::service('tpl_source.'.substr($tmpl_file,0,$p));
            if($object){
                $tmpl_file_path = substr($tmpl_file,$p+1);
                $last_modified = $object->last_modified($tmpl_file_path);
            }
        }else{
            $tmpl_file = realpath(APP_DIR.'/'.($app_id?$app_id:$this->app->app_id).'/view/'.$tmpl_file);
            $last_modified = filemtime($tmpl_file);
        }

        if(!$last_modified){
            //无文件
        }

        $compile_id = $this->compile_id($tmpl_file);

        if($object){
            $compile_code = $this->_compiler()->compile($object->get_file_contents($tmpl_file_path));
        }else{
            $compile_code = $this->_compiler()->compile_file($tmpl_file);
        }

        eval('?>'.$compile_code);
        array_shift($this->_files);
    }

    function fetch($tmpl_file,$app_id=null){
        ob_start();
        $this->display($tmpl_file, $app_id);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

}
