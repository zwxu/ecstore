<?php



class site_theme_tmpl_file
{

    function __construct(){
        if(ECAE_MODE==true){
            $this->fileObj = kernel::single('site_theme_tmpl_dbfile');
        }else{
            $this->fileObj = kernel::single('site_theme_tmpl_fsfile');
        }
    }

    //获取单一模板文件路径，这里是图片和xml等
    function get_src($theme, $uriname){
        return $this->fileObj->get_src($theme, $uriname);
    }

    //是否有备份文件
    function is_themme_bk($theme, $uriname){
        return $this->fileObj->is_themme_bk($theme, $uriname);
    }

    //模板前缀
    function preview_prefix($theme){
        return $this->fileObj->preview_prefix($theme);
    }

    //保存备份文件
    function bak_save($theme, $data){
        return $this->fileObj->bak_save($theme, $data);
    }

    //返回模板real路径
    function get_theme_dir($theme, $open_path){
        return $this->fileObj->get_theme_dir($theme, $open_path);
    }

    function get_file($dir, $file_name){
        return $this->fileObj->get_file($dir, $file_name);
    }

    function get_content($file_content){
        return $this->fileObj->get_content($file_content);
    }

    function get_source_code($theme, $tmpl_type){
        return $this->fileObj->get_source_code($theme, $tmpl_type);
    }

    function check($theme,&$msg=''){
        return $this->fileObj->check($theme,$msg);
    }

    function get_theme_xml($theme, $uriname){
        return $this->fileObj->get_theme_xml($theme, $uriname);
    }

    function get_style_css($theme, $uriname){
        return $this->fileObj->get_style_css($theme, $uriname);
    }

    function get_tmpl_content($theme, $tmpl){
        return $this->fileObj->get_tmpl_content($theme, $tmpl);
    }

    function get_widgets_content($theme, $tpl, $widgets_app){
        return $this->fileObj->get_widgets_content($theme, $tpl, $widgets_app);
    }

    function get_func_phpcode($theme, $func_file, $widgets_app){
        return $this->fileObj->get_func_phpcode($theme, $func_file, $widgets_app);
    }

    function get_full_file_url($theme, $file_content, $open_path, $file_name){
        return $this->fileObj->get_full_file_url($theme, $file_content, $open_path, $file_name);
    }

    function get_widgets_code($theme, $app, $widgets_dir){
        return $this->fileObj->get_widgets_code($theme, $app, $widgets_dir);
    }

    function get_xml_content($theme, $sDir, $loadxml){
        return $this->fileObj->get_xml_content($theme, $sDir, $loadxml);
    }

}
