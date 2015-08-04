<?php

 
class base_render{

    var $pagedata = array();
    var $force_compile = 0;
    var $_tag_stack = array();
    private $_compiler;
    static $_vars = array();
    var $_files = array();
    var $_tpl_key_prefix = array();
    var $_ignore_pre_display = false;

    function __construct(&$app){
        $this->app = $app;
        $this->params = &kernel::request()->request_params;
        $this->pagedata = &base_render::$_vars;
    }

    function display($tmpl_file, $app_id=null, $fetch=false, $is_theme=false){
        array_unshift($this->_files,$tmpl_file);
        $this->_vars = $this->pagedata;

        if($p = strpos($tmpl_file,':')){
            $object = kernel::service('tpl_source.'.substr($tmpl_file,0,$p));
            if($object){
                $tmpl_file_path = substr($tmpl_file,$p+1);
                $last_modified = $object->last_modified($tmpl_file_path);
            }
        }else{
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.($app_id?$app_id:$this->app->app_id).'/view/'.$tmpl_file)){  
                 $tmpl_file = CUSTOM_CORE_DIR.'/'.($app_id?$app_id:$this->app->app_id).'/view/'.$tmpl_file;    
            }else{
				 if (!$is_theme)
					$tmpl_file = realpath(APP_DIR.'/'.($app_id?$app_id:$this->app->app_id).'/view/'.$tmpl_file);
				 else
					$tmpl_file = realpath(THEME_DIR.'/'.$tmpl_file);
            }
            $last_modified = filemtime($tmpl_file);
        }

        if(!$last_modified){
            //无文件
        }
        
        $this->tmpl_cachekey('__temp_lang', kernel::get_lang());  //设置模版所属语言包
        $this->tmpl_cachekey('__temp_app_id', $app_id?$app_id:$this->app->app_id);
        $compile_id = $this->compile_id($tmpl_file);

        #if($this->force_compile || base_kvstore::instance('cache/template')->fetch($compile_id, $compile_code, $last_modified) === false){
        if($this->force_compile || !cachemgr::get($compile_id.$last_modified, $compile_code)){
            if($object){
                $compile_code = $this->_compiler()->compile($object->get_file_contents($tmpl_file_path));
            }else{
                $compile_code = $this->_compiler()->compile_file($tmpl_file);
            }
            if($compile_code!==false){
                #base_kvstore::instance('cache/template')->store($compile_id,$compile_code);
                cachemgr::co_start();
                cachemgr::set($compile_id.$last_modified, $compile_code, cachemgr::co_end());
            }
        }

        ob_start();
        eval('?>'.$compile_code);
        $content = ob_get_contents();
        ob_end_clean();
        array_shift($this->_files);

        $this->pre_display($content);

        if($fetch === true){
            return $content;
        }else{
            echo $content;
        }
    }

    public function pre_display(&$content) 
    {
        if($this->_ignore_pre_display === false){
            foreach(kernel::servicelist('base_render_pre_display') AS $service){
                if(method_exists($service, 'pre_display')){
                    $service->pre_display($content);
                }
            }
        }
    }//End Function

    public function _compiler(){
        return $this->single('base_component_compiler');
    }
    
    private function single($classname){
        if(!isset($this->_object[$classname])){
            $this->_object[$classname] = new $classname($this);
        }
        return $this->_object[$classname];
    }

    function fetch($tmpl_file,$app_id=null,$is_theme=false){
    
        return $this->display($tmpl_file, $app_id, true, $is_theme);
    }

    public function tmpl_cachekey($key,$value){
        $this->_tpl_key_prefix[$key] = $value;
    }

    function &ui(){
        return $this->single('base_component_ui');
    }

    function _fetch_compile_include($app_id,$tmpl_file, $vars=null, $is_theme=false){
        $_tmp_pagedata = $this->pagedata;
        $_tmp_vars = $this->_vars;
        if(is_null($vars) || empty($vars)){
             $this->pagedata = $this->_vars;
        }else{
             $this->pagedata = (array)$vars;
        }
        $this->_ignore_pre_display = true;
        $include = $this->fetch($tmpl_file,$app_id,$is_theme);
        $this->_ignore_pre_display = false;     //todo: fetch include的模板时不需要执行pre_display过滤，主模板会最终执行一次
        $this->pagedata = $_tmp_pagedata;
        $this->_vars = $_tmp_vars;
        return $include;
    }

    function compile_id($path){
        ksort($this->_tpl_key_prefix);
        return md5($path.serialize($this->_tpl_key_prefix));
    }

}
