<?php
  
class cellphone_service_image implements image_interface_set
{
    public function setconfig($data)
    {
        $image_set = $data['pic'];
        $phoneSize = array(
            'CL' => 'site.large_pic',
            'CS' => 'site.small_pic'
        );

        foreach($image_set as $size=>$item){
            app::get('cellphone')->setConf( $phoneSize[$size].'_width',$item['width'] );
            app::get('cellphone')->setConf( $phoneSize[$size].'_height',$item['height'] );
        }
    }
    
    public function getconfig(&$sizes=array(), &$cursize, &$allsize)
    {
        $allow = array();
        if(in_array('L', (array)$sizes) || in_array('l', (array)$sizes)){
            $allow[] = 'CL';
        }
        if(in_array('S', (array)$sizes) || in_array('s', (array)$sizes)){
            $allow[] = 'CS';
        }
        foreach((array)app::get('cellphone')->getConf('image.set') as $key => $value){
            $key = strtoupper($key);
            if(!isset($cursize[$key]) && $allow && in_array($key, $allow)){
                $cursize[$key] = $value;
            }
        }
        foreach((array)app::get('cellphone')->getConf('image.cellphone.set') as $key => $value){
            $key = strtoupper($key);
            if(!isset($allsize[$key]) && $allow && in_array($key, $allow)){
                $allsize[$key] = $value;
                $sizes[] = $key;
            }
        }
    }
    
    public function getsize(&$sizes=array())
    {
        $allow = array();
        if(in_array('L', (array)$sizes) || in_array('l', (array)$sizes)){
            $allow[] = 'CL';
        }
        if(in_array('S', (array)$sizes) || in_array('s', (array)$sizes)){
            $allow[] = 'CS';
        }
        foreach((array)app::get('cellphone')->getConf('image.cellphone.set') as $key => $value){
            $key = strtoupper($key);
            if($allow && in_array($key, $allow)){
                $sizes[] = $key;
            }
        }
    }
    
    public function delete_image($data, $host, &$fsizefsize=0)
    {
        foreach((array)app::get('cellphone')->getConf('image.cellphone.set') as $key => $value){
            if (file_exists($host.'/'.$data[0][strtolower($key).'_url'])){
                $fsize += abs(filesize($host.'/'.$tmp[0][strtolower($key).'_url']));
                @unlink($host.'/'.$tmp[0][strtolower($key).'_url']);
            }
        }
    }
}