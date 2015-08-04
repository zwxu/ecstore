<?php

class b2c_service_site_theme_helper
{

    public function function_header()
    {
        $path = app::get('b2c')->res_full_url;
        return '<link rel="stylesheet" href="'.$path.'/css/b2c.css" type="text/css" />';
    }//End Function

}//End Class
