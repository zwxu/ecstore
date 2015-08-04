<?php

 
class b2c_service_view_menu{
    function function_menu(){
        $shop_base = app::get('site')->router()->gen_url(array('app'=>'site', 'ctl'=>'default'));
        $html[] = "<a href='$shop_base' target='_blank'>".app::get('b2c')->_('浏览商店')."</a>";
       
        return $html;
    
    }
}