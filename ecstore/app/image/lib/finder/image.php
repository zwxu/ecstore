<?php
 
/**
 * 实现finder页面列表
 */
class image_finder_image{

    var $detail_basic = '图片详细信息';
    var $column_img = '图片';
    function __construct($app){
        $this->app = $app;
    }
	
    /**
     * finder下拉详细展示页面
     * @param string image id
     * @return string 详细页面的html
     */
    function detail_basic($image_id){
        $app = app::get('image');

        $render = $app->render();

        $image = $this->app->model('image');
        $image_info = $image->dump($image_id);
        $allsize = app::get('image')->getConf('image.default.set');

        $render->pagedata['allsize'] = $allsize;
        $render->pagedata['image'] = $image_info;
     
    
        return $render->fetch('finder/image.html');
    }
    
    /**
     * finder img列的链接修改
     * @param array 某行具体数据的数组
     * @return string 链接html
     */
    function column_img($row){
 
        $obj = app::get('image')->model('image');
        $row = $obj->dump($row['image_id']);
        $limitwidth = 50;
        
        $maxsize = max($row['width'],$row['height']);
        
        if($maxsize>$limitwidth){
            $size ='width=';
            $size.=$row['width']-$row['width']*(($maxsize-50)/$maxsize);
            $size.=' height=';
            $size.=$row['height']-$row['height']*(($maxsize-50)/$maxsize);
        }else{
            $size ='width='.$row['width'].' height='.$row['height'];
        }
        
        
       
        if($row['storage']=='network'){
         return '<a href="'.$row['ident'].'" target="_blank"><div title="'.$row['ident'].'" style="line-height:41px;width:50px;text-align:center;background:#efefef;">网络图片</div></a><input type="text" value="'.$row['ident'].'" style="font-size:9px;font-family:verdana;border:none;width:50px;padding:0;margin:0;display:block;background:#333;color:#fff"/>';
        }
        return '<div  style="width:50px;height:50px;display:block;font-family:Arail;vertical-align: middle;display:table-cell;font-size:42.5px;padding:1px;background:#fff;"><a href="'.(base_storager::image_path($row['image_id'])).'" target="_blank" style="display:block;">
<img src="'.(base_storager::image_path($row['image_id'],'s')).'" '.$size.' /></a></div>';
    }
}
