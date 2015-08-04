<?php


class site_service_view_helper 
{
    function function_header($params, &$smarty)
    {
        return $smarty->fetch('header.html', app::get('site')->app_id);
    }//End Function

}//End Class