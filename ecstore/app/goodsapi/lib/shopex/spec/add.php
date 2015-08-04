<?php

class goodsapi_shopex_spec_add extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->obj_goods = kernel::single('goodsapi_goods');
        $this->spec_model = app::get('b2c')->model('specification');
    }

    //增加商品规格列表接口
    function shopex_spec_add (){
        $params = $_POST;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'spec_name','spec_type','spec_show_type','spec_values','is_show'
        );
        $this->check_params($must_params);

        if( empty($params['spec_name'])  || empty($params['spec_values'])){
            $error['code'] = null;
            $error['msg']  = '规格名称或规格值不能为空';
            $this->send_error($error);
        }

        $arr_spec_value = json_decode($params['spec_values'],true);
        foreach($arr_spec_value['spec_values'] as  $key=>$value){
            if($value['image_url']){
                $image_id = $this->obj_goods->get_image_id($value['image_url']);
            }
            $spec_value['new_'.$key] = array(
                'spec_value' => $value['new_spec_value'],
                'alias' => $value['spec_value_alias'],
                'spec_image' => $image_id,
                'p_order' => intval($value['order_by']),
            );
        }

        $spec_type = 'image';
        if($params['spec_type'] == '文字'){
            $spec_type = 'text';
        }

        $spec_show_type = 'flat';
        if($params['spec_show_type'] == '下拉'){
            $spec_show_type = 'select';
        }

        $save_data = array(
            'spec_name' => trim($params['spec_name']),
            'alias' => trim($params['alias']),
            'spec_memo' => trim($params['memo']),
            'spec_type' => $spec_type,
            'spec_show_type' => $spec_show_type,
            'p_order' => intval($params['order_by']),
            'spec_value' => $spec_value,
        );

        $rs = $this->spec_model->save($save_data);
        if( $rs ){
            $data = array('last_modify' =>null);
            $this->send_success($spec_value);
        }else{
            $error['code'] = '0x004';
            $this->send_error($error);
        }
    }
}
