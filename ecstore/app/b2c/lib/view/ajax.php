<?php

 
class b2c_view_ajax{

    function get_html($html,$class_name,$method_name)
    {
        $obj = kernel::service('replace.ajax.html');
        if(is_object($obj))
        {
            if(method_exists($obj,'get_html'))
            {
                return $obj->get_html($html,$class_name,$method_name);
            }
            else
            {
                return $html;
            }
        }
        else
        {
            return $html;
        }
    }
}
