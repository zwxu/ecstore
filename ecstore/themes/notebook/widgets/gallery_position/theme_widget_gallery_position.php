<?php

 
function theme_widget_gallery_position($setting,&$app){
    $data=$GLOBALS['runtime'];
    
    $data['path_count']=count($data['path']);
    if(isset($data['brand']['options'])){
        $data['brand']['brand_name']=implode(',',$data['brand']['options']);
     
        $data['brand']['brand_id']=implode(',',array_keys($data['brand']['options']));
    }
    if(isset($data['props'][0])){
       foreach($data['props'] as $key=>&$prop){
            $prop['prop_name']=implode(',',$prop['options']);
            $prop['prop_id']=$prop['goods_p'].','.implode(',',array_keys($prop['options']));
       }
    }
    //print_r($data);
    return $data;
}
?>
