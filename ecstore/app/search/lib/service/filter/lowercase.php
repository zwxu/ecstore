<?php

class search_service_filter_lowercase implements search_interface_filter 
{
    public function __construct()
    {
        if (!function_exists('mb_strtolower')) {
            trigger_error('php不支持mb_strtolower', E_USER_ERROR);
        }
    }

    public function normalize($input) 
    {
        return mb_strtolower($input);
    }//End Function
}//End Class