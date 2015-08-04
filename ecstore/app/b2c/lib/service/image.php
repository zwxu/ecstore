<?php
class b2c_service_image implements image_interface_set{
    
    
    public function setconfig($data){
            $image_set = $data['pic'];
            $b2cSize = array(
                'L' => 'site.big_pic',
                'M' => 'site.small_pic',
                'S' => 'site.thumbnail_pic'
            );

            foreach($image_set as $size=>$item){
                app::get('b2c')->setConf( $b2cSize[$size].'_width',$item['width'] );
                app::get('b2c')->setConf( $b2cSize[$size].'_height',$item['height'] );
            }
   }
}

?>