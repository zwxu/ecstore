<?php


class site_theme_tmpl_dbfile
{
    //sdb_site_themes_file表，content字段为url或代码时，获取其content信息，如html，php文件等存的是源代码
    private function get_themes_file_content($theme, $uriname){
        $obj_themes_file = app::get('site')->model('themes_file');
        $file_rows = $obj_themes_file->getList('content',array('theme'=>$theme,'fileuri'=>$theme.':'.$uriname));
        return $file_rows[0]['content'];
    }

    //sdb_site_themes_file表，content字段为url时，获取其url，如js，css，图片等存的是地址
    private function get_themes_file_url($uri){
        $storager = kernel::single('base_storager');
        $ident = $storager->parse($uri);
        return $ident['url'];
    }

    function get_file_url($file_content){
        $storager = kernel::single('base_storager');
        $indent = $storager->parse($file_content);
        return $indent['url'];
    }

    function get_src($theme, $uriname){
        $uri = $this->get_themes_file_content($theme, $uriname);
        $ident_url = $this->get_themes_file_url($uri);
        return $ident_url;
    }

    //todo merge to get_src()
    function get_style_css($theme, $uriname){
        $uri = $this->get_themes_file_content($theme, $uriname);
        $ident_url = $this->get_themes_file_url($uri);
        return $ident_url;
    }

    public function is_themme_bk($theme, $uriname){
        $uri = $this->get_themes_file_content($theme, $uriname);
        if($uri) {
            $is_themme_bk = 'true';
        }else{
            $is_themme_bk = 'false';
        }
        return $is_themme_bk;
    }

    function preview_prefix($theme){
        return '';
    }

    function bak_save($theme, $data){
        $model_file = app::get('site')->model('themes_file');
        $filter = array('theme'=>$theme,'fileuri'=>$theme.':theme_bak.xml');
        $file_rows = $model_file->getList('*',$filter);
        $array_data = array('filename'=>'theme_bak.xml','filetype'=>'xml','content'=>$data);
        if($file_rows[0]){
            $array_data['id'] = $file_rows[0]['id'];
        }
        $array_data = array_merge($array_data,$filter);
        if($model_file->save($array_data)){
            $flag = true;
        }else{
            $flag = false;
        }

        return $flag;
    }

    function get_theme_dir($theme, $open_path){
        return THEME_DIR . '/' . $theme . str_replace(array('-','.'), array('/','/'), $open_path);
    }

    function get_file($dir, $file_name){
        return $file_name;
    }

    function get_content($file_content){
        $indent_url = $this->get_themes_file_url($file_content);
        $http = kernel::single('base_httpclient');
        $http->set_timeout(10);
        $file_content = $http->action(__FUNCTION__,$indent_url,null,null,array());
        return $file_content;
    }

    function get_source_code($theme, $tmpl_type){
        $tmplThemesObj = app::get('site')->model('themes_tmpl');
        $content=$tmplThemesObj->dump(array('theme'=>$theme,'tmpl_type'=>$tmpl_type),'*');
        if(!$content){
            $content=$tmplThemesObj->dump(array('theme'=>$theme,'tmpl_type'=>'default'),'*');
        }
        return $content['content'] ;
    }

    function check($theme,&$msg=''){
        return true;
    }

    function get_theme_xml($theme, $uriname){
        $uri = $this->get_themes_file_content($theme, $uriname);
        return $uri;
    }

    function get_tmpl_content($theme, $tmpl){
        $uri = $this->get_themes_file_content($theme, $tmpl);
        return $uri;
    }

    function get_widgets_content($theme, $tpl, $widgets_app){
        if($widgets_app){
            $file_path = realpath($tpl);
            $compile_code = file_get_contents($file_path);
        }else{
            $tmpl = substr($tpl,strpos($tpl,'/widgets/')+1);
            $compile_code = $this->get_themes_file_content($theme, $tmpl);
        }
        return $compile_code;
    }

    function get_func_phpcode($theme, $func_file, $widgets_app){
        if($widgets_app){
            return 'require(\''.$func_file.'\');';
        }else{
            $tmpl = substr($func_file,strpos($func_file,'/widgets/')+1);
            $widgets_code = $this->get_themes_file_content($theme, $tmpl);
            $func_php_code = preg_replace("/\<\?php(.*)\?\>/isU", "$1", $widgets_code);
            $func_php_code = base64_encode($func_php_code);
            return 'eval(base64_decode("'.$func_php_code.'"));';
        }
    }

    function get_full_file_url($theme, $file_content, $open_path, $file_name){
        $url = $this->get_themes_file_url($file_content);
        return $url;
    }

    function get_widgets_code($theme, $app, $widgets_dir){
        if($theme){
            $theme = kernel::single('site_theme_base')->get_default();
            $setting_file = $widgets_dir . '/widgets.php';
            $tmpl = substr($setting_file,strpos($setting_file,'/widgets/')+1);
            $widgets_code = $this->get_themes_file_content($theme, $tmpl);
        }elseif($app){
           $widgets_code = file_get_contents($widgets_dir . '/widgets.php');
        }
        return $widgets_code;
    }

    function get_xml_content($theme, $sDir, $loadxml){
        $file_rows = $this->get_themes_file_content($theme, $loadxml);

        if($file_rows){
            $loadxml_content = $file_rows;
        }
        return $loadxml_content;
    }

}//End Class