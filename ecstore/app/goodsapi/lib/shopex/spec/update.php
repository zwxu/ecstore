<?php

class goodsapi_shopex_spec_update extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->obj_goods = kernel::single('goodsapi_goods');
        $this->spec_model = app::get('b2c')->model('specification');
    }

    //更新商品规格列表接口
    function shopex_spec_update (){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'spec_name','new_spec_name','spec_type','spec_show_type',
            'spec_values','is_show','disabled','is_force_update','last_modify'
        );
        $this->check_params($must_params);

       //规格值不能为空
        if( empty($params['spec_name'])  || empty($params['spec_values'])){
            $error['code'] = null;
            $error['msg'] = '规格名称或规格值为空';
            $this->send_error($error);
        }

        $filter = array(
            'spec_name' => $params['spec_name'],
        );
        if($params['alias']){
            $filter['alias'] = $params['alias'];
        }

        //获取到要更新的spec_id
        $spec_id = $this->spec_model->getList('*',$filter);
        if( !$spec_id ){
            if( $params['unexist_add'] == 'false'){
                $error['code'] = null;
                $error['msg'] = '更新的规格不存在';
                $this->send_error($error);
            }
        }else{
            $spec_id = $spec_id[0]['spec_id'];
            $save_data['spec_id'] = $spec_id;
        }

        if(empty($params['new_alias'])){
            $alias = $params['alias'];
        }else{
            $alias = $params['new_alias'];
        }

        if(empty($params['new_spec_name']))
            $spec_name = trim($params['spec_name']);
        else
            $spec_name = trim($params['new_spec_name']);

        $arr_spec_value = json_decode($params['spec_values'],true);
        foreach( $arr_spec_value['spec_values']  as  $key=>$value){
            $spec_index = app::get('b2c')->model('goods_spec_index');
            //获取到规则值的id   spec_id和spec_values 不能确定唯一
            $spec_value_id = app::get('b2c')->model('spec_values')->getList('spec_value_id',array('spec_id'=>$spec_id,'spec_value'=>trim($value['spec_value'])));

            if( !empty($value['new_spec_value']) ){
                //判断此规格值是否已经关联商品
                if( $spec_index->dump(array('spec_id'=>$spec_id,'spec_value_id'=>$spec_value_id)) ){
                    //此规格值已有关联商品
                    $error['msg'] = '此规格值已有关联商品，不能更新';
                    $this->send_error($error);
                }else{
                    $value['spec_value'] = $value['new_spec_value'];
                }
            }

            //处理图片
            $image_id = '';
            if($value['image_url']){
                $image_id = $this->obj_goods->get_image_id($value['image_url']);
            }

            $spec_value[$key] = array(
                'spec_value' => $value['new_spec_value'],
                'alias' => $value['spec_value_alias'],
                'spec_image' => $image_id,
                'p_order' => intval($value['order_by']),
            );
            if( !empty($spec_value_id[0]['spec_value_id']) )
            {
                $spec_value[$key]['spec_value_id'] = $spec_value_id[0]['spec_value_id'];
            }
        }

        $spec_type = 'image';
        if($params['spec_type'] == '文字'){
            $spec_type = 'text';
        }

        $spec_show_type = 'flat';
        if($params['spec_show_type'] == '下拉'){
            $spec_show_type = 'select';
        }

        $save_data['spec_name'] = $spec_name;
        $save_data['alias'] = $alias;
        $save_data['spec_memo'] = trim($params['memo']);
        $save_data['spec_type'] = $spec_type;
        $save_data['spec_show_type'] = $spec_show_type;
        $save_data['p_order'] = intval($params['order_by']);
        $save_data['spec_value'] = $spec_value;

        $rs = $this->spec_model->save($save_data);
        if( $rs ){
            $data = array('last_modify' =>time());
            $this->send_success($data);
        }else{
            $error['code'] = null;
            $error['msg']  = '更新失败';
            $this->send_error($error);
        }
    }
}
