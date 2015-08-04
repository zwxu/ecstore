<?php

class goodsapi_shopex_goods_image_upload extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->goods_model = app::get('b2c')->model('goods');
        $this->image_model = app::get('image')->model('image');
    }

    //上传商品图片接口
    function shopex_goods_image_upload(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        if(!isset($params['img'])  && empty($params['img'])){
            $error['code'] = null;
            $error['msg'] = '应用级必填参数未定义';
            $this->send_error($error);
        }

        $image_name = $_FILES['img']['name'];
        $image_id  = $this->image_model->store($_FILES['img']['tmp_name'],null,null,$image_name);
        if(!$image_id) {
            $error['code'] = '0x004';
            $this->send_error($error);
        }

        $image_path = kernel::single('base_storager')->image_path($image_id);
        $data['img_path'] = substr($image_path,0,-13);
        $this->send_success($data);

    }//end api

}

