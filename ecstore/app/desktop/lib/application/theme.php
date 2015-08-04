<?php

 
class desktop_application_theme extends base_application_prototype_filepath{

    var $path = 'desktop_themes';

    function filter(){
        $dirname = $this->iterator()->getFilename();
        return $dirname{0}!='.' && is_dir($this->getPathname());
    }

    function content_typename(){
        return 'desktop theme';
    }
    
    static function get_files($theme_define){
        list($theme_app,$theme_dir) = explode('/',$theme_define);
        $handle = opendir(app::get($theme_app)->app_dir.'/desktop_themes/'.$theme_dir);
        $theme_base_url = kernel::base_url().'/app/'.$theme_app.'/desktop_themes/'.$theme_dir;
        while(false!==($file=readdir($handle))){
            if($file{0}!='.'){
                if(substr($file,-4,4)=='.css'){
                    $css[] = $theme_base_url.'/'.$file;
                }elseif(substr($file,-3,3)=='.js'){
                    $js[] = $theme_base_url.'/'.$file;                    
                }
            }
        }
        closedir($handle);
        return array($js,$css);
    }

}