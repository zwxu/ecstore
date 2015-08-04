<?php

class goodsapi_shopex_type_update extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->type_model = app::get('b2c')->model('goods_type');
        $this->obj_goods = kernel::single('goodsapi_goods');
    }

    //添加商品类型接口
    function shopex_type_update($params){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'name','is_default','is_physical','is_has_brand','is_has_params','is_must_minfo',
            'props','params','must_minfo','is_force_update','last_modify'
        );
        $this->check_params($must_params);

        //判断新增的类型名称是否已存在
        $type = $this->type_model->getList('type_id,lastmodify',array('name'=>$params['name']));
        if( !$type ){
            if( $params['unexist_add'] == 'false'){
                //如果没有对应的名称，则数据错误
                $error['code'] = null;
                $error['msg']  = '要更新的数据已删除，不是最新数据';
                $this->send_error($error);
            }
        }else{
            if( !$params['is_force_update'] && $params['is_force_update'] < $type[0]['lastmodify']  ){
                $error['code'] = '0x021';
                $this->send_error($error);
            }
            $type_id = $type[0]['type_id'];
            $save_data['type_id'] = $type_id;
        }


        $type_name = $params['name'];
        //判断更新的新的类型名称是否已存在
        if($params['new_name'] != $params['name']){
            $new_name = $this->type_model->getList('type_id',array('name'=>$params['new_name']));
            if( $new_name ){
                $error['code'] = null;
                $error['msg']  = '新类型名称已存在';
                $this->send_error($error);
            }else{
                $type_name = $params['new_name'];
            }
        }

        $setting = array();
        if($params['is_has_brand'] == 'true'){
            $setting['use_brand'] = 1;
        }
        if($params['is_has_prop'] == 'true'){
            $setting['use_props'] = 1;
        }
        if($params['is_has_params'] == 'true'){
            $setting['use_params'] = 1;
        }
        if($params['is_must_minfo'] == 'true'){
            $setting['use_minfo'] = 1;
        }

        //保存的数据
        $save_data['name']=$type_name;
        $save_data['alias'] = $params['alias'];
        $save_data['is_physical'] = $params['is_physical']=='true'?1:0;
        $save_data['setting'] = $setting;
        $save_data['disabled'] = $params['disabled'];


        //保存规格数据
        if( !empty($params['spec_names']) ){
            $save_data['spec'] = null;
            foreach( explode(',',$params['spec_names']) as $s_k=>$spec_name ){
                $filter_spec = $this->obj_goods->get_spec_name($spec_name);
                $specs = app::get('b2c')->model('specification')->dump(array('spec_name'=>$filter_spec),'spec_id,spec_show_type');
                if( $specs ){
                    $save_data['spec'][$s_k] = array(
                        'spec_id'=>$specs['spec_id'],
                        'spec_style'=>$specs['spec_show_type'],
                    );
                }
            }
        }

        //保存品牌关联数据
        if( !empty($params['brand_names']) ){
            $save_data['brand'] = null;
            foreach( explode(',',$params['brand_names']) as $brand_k => $brand_name ){
                $brand = app::get('b2c')->model('brand')->dump(array('brand_name'=>$brand_name),'brand_id');
                if( $brand ){
                    $save_data['brand'][$brand_k] = array(
                       'brand_id'=>$brand['brand_id']
                    );
                }else{
                    $error['msg']  = null;
                    $error['code'] = $brand_name.'没有对应关联品牌';
                    $this->send_error($error);
                }
            }
        }

        //保存扩展属性列表信息
        $goods_p_select = 0;
        $goods_p_input = 20;
        $searchType = array(
            '0' => array('type' => 'input', 'search' => 'input'),
            '1' => array('type' => 'input', 'search' => 'disabled'),
            '2' => array('type' => 'select', 'search' => 'nav'),
            '3' => array('type' => 'select', 'search' => 'select'),
            '4' => array('type' => 'select', 'search' => 'disabled'),
        );
        if($params['props']){
            $arr_props = json_decode($params['props'],true);
            $save_props = array();
        }
        foreach( $arr_props['props']  as  $p_k=>$props){
            //显示转换,商品通的数据格式和系统中不一致
            if($props['is_show'])
                $props['is_show'] = 'on';
            else
                $props['is_show'] = '';

            //select goods_p 1-20；input goods_p 21-50；
            if( $searchType[$props['show_type']]['type'] == 'select'){
                $goods_p = ++$goods_p_select;
            }else{
                $goods_p = ++$goods_p_input;
            }
            $save_props = array(
                'name' => $props['prop_name'],
                'alias' => $props['alias'],
                'show' => $props['is_show'],
                'ordernum' => intval($props['order_by']),
                'goods_p' => $goods_p,
                'options' =>empty($props['prop_value'])? '': explode(',',$props['prop_value']),
               'lastmodify' => time()
            );
            $save_props = array_merge($save_props,$searchType[$props['show_type']]);
            $save_data['props'][$p_k] = $save_props;
        }

        //保存详细参数列表数据
        $arr_params = json_decode($params['params'],true);
        $save_data['params'] = array();
        foreach( $arr_params['params'] as $params_k => $type_params){
            //参数组名称
            $params_names = '';
            $params_names = $type_params['prop_name'];
            //参数数组
            $name = array();
            $name_value = explode(',',$type_params['prop_value']);
            foreach( $name_value as $params_name ){
                $name[$params_name] = '';
            }
            $save_data['params'][$params_names] = $name;
        }

        //保存购物必填信息
        $save_data['minfo'] = array();
        $arr_must_minfo = json_decode($params['must_minfo'],true);
        foreach( $arr_must_minfo['must_minfo'] as $m_k=>$minfo ){
            if($minfo['show_type'] == 0){
                $minfo_show_type = 'input';
            }elseif($minfo['show_type'] == 1){
                $minfo_show_type = 'text';
            }else{
                $minfo_show_type = 'select';
            }
            $save_data['minfo'][$m_k] = array(
                'label' => $minfo['prop_name'],
                'name'  => 'M'.md5($minfo['prop_name']),
                'type' => $minfo_show_type,
            );

            if( $minfo_show_type == 'select'){
               $save_data['minfo'][$m_k]['options'] =  explode(',',$minfo['prop_value']);
            }

        }

        $rs = $this->type_model->save($save_data);
        if($rs){
            $data['last_modify'] =  time();
            $this->send_success($data);
        }else{
            $error['code'] = '0x004';
            $this->send_error($error);
        }
    }

}
