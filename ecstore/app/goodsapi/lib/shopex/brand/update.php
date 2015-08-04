<?php

class goodsapi_shopex_brand_update extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->obj_goods = kernel::single('goodsapi_goods');
        $this->brand_model = app::get('b2c')->model('brand');
    }

    //更新商品品牌接口
    function shopex_brand_update(){
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

        //获取到要更新的id
        $brand_id = $this->brand_model->getList("brand_id",array('brand_name'=>trim($params['brand_name'])));
        if( $brand_id ){
            $brand_id = $brand_id[0]['brand_id'];
            $save_data['brand_id'] = $brand_id;
        }else{
            if( $params['unexist_add'] == 'false'){
                $error['code'] = '0x021';
                $this->send_error($error);
            }
        }

        //确定要更新的名称
        if($params['new_brand_name'] == $params['brand_name']){
            $brand_name = trim($params['brand_name']);
        }else{
            $new_brand_name =  $this->brand_model->dump(array('brand_name'=>trim($params['new_brand_name'])),'brand_id');
            //修改的名称已经存在
            if($new_brand_name){
                $error['code'] = null;
                $error['msg'] = '更新的品牌已存在';
               $this->send_error($error);
            }
            $brand_name = trim($params['new_brand_name']);
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

        $save_data['brand_name'] = $brand_name;
        $save_data['brand_url'] = $params['brand_url'];
        $save_data['brand_desc'] = $params['brand_desc'];
        $save_data['brand_logo'] = $brand_logo;
        $save_data['brand_keywords'] = $params['brand_alias'];
        $save_data['disabled'] = $params['disabled'];
        $save_data['gtype'] = $gtype;
        $save_data['ordernum'] = intval($params['order_by']);
        $save_data['brand_setting'] = $params['brand_setting'];

        if( $this->brand_model->save($save_data) ) {
            $this->send_success();
        }else{
            $error['code'] = null;
            $error['msg'] = '保存失败';
            $this->send_error($error);
       }
    }
}
