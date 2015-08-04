<?php



class site_theme_tmpl_fsfile
{
    function get_src($theme, $uriname){
        $theme_preview = kernel::base_url(1) . strrchr(THEME_DIR,'/');
        $theme_url = defined('THEMES_IMG_URL') ? THEMES_IMG_URL : $theme_preview;
        $preview_prefix = $theme_url.'/'.$theme;
        $src = $preview_prefix.'/'.$uriname;
        return $src;
    }

    function get_style_css($theme, $uriname){
        $src = kernel::base_url() . "/themes/" . $theme .'/'. $uriname; 
        return $src;
    }

    function is_themme_bk($theme, $uriname){
        if(file_exists(THEME_DIR . '/' . $theme . '/'.$uriname)) {
            $is_theme_bk = 'true';
        }else{
            $is_theme_bk = 'false';
        }
        return $is_theme_bk;
    }

    function preview_prefix($theme){
        $theme_preview = kernel::base_url(1) . strrchr(THEME_DIR,'/');
        $theme_url = defined('THEMES_IMG_URL') ? THEMES_IMG_URL : $theme_preview;
        $preview_prefix = $theme_url.'/'.$theme;
        return $preview_prefix;
    }

    function bak_save($theme,$data){
        if(file_put_contents(THEME_DIR . '/' . $theme . '/theme_bak.xml', $data)) {
            $flag = true;
        } else {
            $flag = false;
        }
        return $flag;
    }

    function get_theme_dir($theme, $open_path){
        return realpath(THEME_DIR . '/' . $theme . '/' . str_replace(array('-','.'), array('/','/'), $open_path));
    }

    function get_file($dir, $file_name){
        return $dir . '/' . $file_name;
    }

    function get_content($file_content){
        return $file_content;
    }

    function get_source_code($theme, $tmpl_type){
        $file = THEME_DIR . '/' . $theme . '/' . $tmpl_type.'.html';
        if(!is_file($file)){
            $file = THEME_DIR . '/' . $theme . '/default.html';
        }

        if(is_file($file)){
            $content = file_get_contents($file);
        }else{
            $content = '<{main}>';
        }

        /** 默认第一次为首页 **/
/*         if (!$tmpl_type){
            $default_file = THEME_DIR . '/' . $theme . '/index.html';
        }else{
            $default_file = THEME_DIR . '/' . $theme . '/' . $tmpl_type.'.html';
        }

        if(is_file($default_file)){
            $content = file_get_contents($default_file);
        }else{
            $content = '<{require file="block/header.html"}>
                        <div class="AllWrapInside clearfix">
                          <div class="mainColumn pageMain"><{widgets id="nav"}>  <{main}> </div>
                          <div class="sideColumn pageSide"> <{widgets id="sideritems"}> </div>
                        </div>
                        <{require file="block/footer.html"}>';
        } */

        return $content;
    }

    function check($theme,&$msg=''){
        if(empty($theme)){
            $msg = app::get('site')->_('缺少参数');
            return false;
        }
        /** 权限校验 **/
        if($theme && preg_match('/(\..\/){1,}/', $theme)){
            $msg = app::get('site')->_('非法操作');
            return false;
        }
        $dir = THEME_DIR . '/' . $theme;
        if (!is_dir($dir)){
            $msg = app::get('site')->_('路径不存在');
            return false;
        }
        return true;
    }

    function get_theme_xml($theme, $uriname){
        $content = file_get_contents($uriname);
        return $content;
    }

    function get_tmpl_content($theme, $tmpl){
        $file_path = realpath(THEME_DIR . '/' . $theme . '/' . $tmpl);
        if(file_exists($file_path)){
            return file_get_contents($file_path);
        }else{
            trigger_error('compile file does\'s not exists ['.$file_path.']', E_USER_ERROR);
            return false;
        }
    }

    function get_widgets_content($theme, $tpl, $widgets_app){
        $file_path = realpath($tpl);
        if(file_exists($file_path)){
            return file_get_contents($file_path);
        }else{
            trigger_error('compile file does\'s not exists ['.$file_path.']', E_USER_ERROR);
            return false;
        }
    }

    function get_func_phpcode($theme, $func_file, $widgets_app){
        return 'require(\''.$func_file.'\');';
    }

    function get_full_file_url($theme, $file_content, $open_path, $file_name){
        return kernel::base_url(1) . rtrim(str_replace('//', '/', '/themes/' . $theme . '/' . str_replace(array('-','.'), array('/','/'), $open_path) . '/' . $file_name));
    }

    function get_widgets_code($theme, $app, $widgets_dir){
        return file_get_contents($widgets_dir . '/widgets.php');
    }

    function get_xml_content($theme, $sDir, $loadxml){
        return file_get_contents($sDir . $loadxml);
    }

}//End Class
