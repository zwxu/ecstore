<?php
class business_ctl_site_mimages extends b2c_frontpage{
  
    public function __construct(&$app){
        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }
    
    function gimage_swf_remote($store_id = 0)
    {
        $image = app::get('business')->model('image');
        $image_name = $_FILES['Filedata']['name'];
        list($w,$h,$t) = getimagesize($_FILES['Filedata']['tmp_name']);
        $image_size = array(
            'width'=>app::get('b2c')->getConf('site.big_pic_width'),
            'height'=>app::get('b2c')->getConf('site.big_pic_height'),
        );
        if(abs($w*$image_size['height']-$h*$image_size['width'])>(min($image_size['width'],$image_size['height'])*2) || $w < $image_size['width'] || $h < $image_size['height']){
            //die("<script>alert('');history.go(0);</script>");
            $msg = "上传图片尺寸不是{$image_size['width']}*{$image_size['height']}以上的图片或上传图片大小超过1M！";
            $msg_type = 1;
        }else{
            $image_id = $image->store($_FILES['Filedata']['tmp_name'],null,null,$image_name,false,$store_id);
            if(!image_id){
                $msg = "上传图片空间已满，请与管理员联系！";
                $msg_type = 2;
            }
        }
        
        $image->rebuild($image_id,array('L','M','S'),true,$store_id);
        
        $this->pagedata['gimage']['image_id'] = $image_id;
        $this->pagedata['gimage']['msg_type'] = $msg_type;
        
        header('Content-Type:text/html; charset=utf-8');
        //echo $this->fetch('site/goods/gimage.html','business');
        $this->page('site/goods/gimage.html', true, 'business');
    }
}