<?php


class base_service_render 
{
    public function pre_display(&$content) 
    {
        $content = base_storager::image_storage($content);
    }//End Function

}//End Class