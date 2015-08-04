<?php

class goodsapi_shopex_brand_add extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->obj_goods = kernel::single('goodsapi_goods');
        $this->brand_model = app::get('b2c')->model('brand');
    }

    //添加商品品牌列表接口
    function shopex_brand_add(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'brand_name','order_by',
        );
        $this->check_params($must_params);

        if(empty($params['brand_name'])){
            $error['code'] = null;
            $error['msg'] = '品牌名不能为空';
            $this->send_error($error);
        }

       //将logo图片返回图片id存储
        $brand_logo = $this->obj_goods->get_image_id($params['brand_logo']);

        if(!empty($params['types'])){
            $goods_type = app::get('b2c')->model('goods_type');
            foreach( explode(',',$params['types']) as $type_name){
                $type_id =  $goods_type->dump(array('name'=>$type_name),'type_id');
                $gtype[] = array('type_id'=>$type_id['type_id']);
            }
        }

        $save_data = array(
            'brand_name' => $params['brand_name'],
            'brand_url'  => $params['brand_url'],
            'brand_desc' => $params['brand_desc'],
            'brand_logo' => $brand_logo,
            'brand_keywords' => $params['brand_alias'],
            'disabled'   => $params['disabled'],
            'ordernum'   => intval($params['order_by']),
            'gtype'  => $gtype,
            'brand_setting' => $params['brand_setting'],
        );
        if( $this->brand_model->save($save_data) ){
            $this->send_success();
        }else{
            $error['code'] = '0x004';
            $thsi->send_error($error);
        }
    }
}
