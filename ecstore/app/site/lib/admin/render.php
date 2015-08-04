<?php

 

class site_admin_render extends base_render 
{
    private $__theme = null;
    private $__tmpl = null;
    
    function __construct(&$app) 
    {
        parent::__construct($app);
        if(@constant('WITHOUT_STRIP_HTML')){
            $this->enable_strip_whitespace = false;
        }
    }//End Function

    public function display_admin_widget($tpl, $fetch=false,$widgets_app) 
    {
        $this->_vars = $this->pagedata;
        $tmpl_file = realpath($tpl);
		$cur_theme = kernel::single('site_theme_base')->get_default();
        if($tmpl_file||ECAE_MODE){
			$last_modified = filemtime($tmpl_file);
            $compile_id = $this->compile_id($cur_theme.$tpl);
             if($this->force_compile || !cachemgr::get($compile_id.$last_modified, $compile_code)){

                $file_content = kernel::single('site_theme_tmpl_file')->get_widgets_content($cur_theme, $tpl, $widgets_app);
                $compile_code = $this->_compiler()->compile($file_content);

                if($compile_code!==false){
                    base_kvstore::instance('cache/theme_admin_widget')->store($compile_id, $compile_code);
                }
            }

            ob_start();
            eval('?>'.$compile_code);
            $content = ob_get_contents();
            ob_end_clean();
            
            $this->pre_display($content);
        }else{
            
            $obj = kernel::single('base_render');
            $obj->pagedata['tpl'] = $tpl;
            $content = $obj->fetch('admin/theme/widgets_tpl_lost.html', 'site');    //todo: 无模板提示
        }

        if($fetch === true){
            return $content;
        }else{
            echo $content;
        }
    }//End Function

    public function fetch_admin_widget($tpl,$widgets_app) 
    {
        return $this->display_admin_widget($tpl, true,$widgets_app);
    }//End Function

}//End Class
