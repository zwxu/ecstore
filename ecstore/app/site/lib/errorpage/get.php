<?php 

class site_errorpage_get
{
    
    
    
    public function getConf($key='') {
        if( $key )
            return app::get('site')->getConf($key);
        else return false;
    }
}