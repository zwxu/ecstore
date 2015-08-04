<?php

class site_service_tpltheme 
{
    
    public function last_modified($path) 
    {
        $file = THEME_DIR . '/' . kernel::single('site_theme_base')->get_default() . '/' . $path;
        if(is_file($file)){
            return filemtime($file);
        }else{
            return 1;
        }
    }//End Function

    public function get_file_contents($path) 
    {
        $file = THEME_DIR . '/' . kernel::single('site_theme_base')->get_default() . '/' . $path;
        if(is_file($file)){
            return file_get_contents($file);
        }else{
            return '';
        }
    }//End Function

}//End Class