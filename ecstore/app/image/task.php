<?php

 

class image_task 
{
    function post_install() 
    {
        kernel::log('Initial image');
        kernel::single('base_initial', 'image')->init();
        $conf = app::get('image')->getConf('image.default.set');
        app::get('image')->setConf('image.set',$conf);
        $obj_image = app::get('image')->model('image');
        $app_dir = app::get('image')->app_dir;
        foreach($conf as $item){
            $obj_image->store($app_dir.'/initial/default_images/'.$item['default_image'].'.gif',$item['default_image']);
        }
    }//End Function
}//End Class
