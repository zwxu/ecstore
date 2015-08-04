<?php
/*
 * Created on 2011-11-23
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function widget_pic_flash(&$setting,&$render){
    $app=app::get('poster');
    $o=$app->model('poster');
    $filter=array(
            'poster_position'=>$setting['poster_position'],
            'poster_starttime|lthan'=>time(),
            'poster_endtime|than'=>time(),
            'disabled'=>'false',
        );
    
    $data=$o->getList('*',$filter,0,1,'poster_starttime asc');
    $data=$data[0];
    if(is_array($data['poster_imgurl']))
    {
        $data['poster_imgurl']=array_values($data['poster_imgurl']);
    }
    $data['width'] = isset($setting['width'])?$setting['width']:0;
    $data['height'] = isset($setting['height'])?$setting['height']:0;
    
    return $data;//根据挂件配置信息,取出数据,返回给挂件模板
}

