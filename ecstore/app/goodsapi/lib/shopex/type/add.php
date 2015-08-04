<?php

class goodsapi_shopex_type_add extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->type_model = app::get('b2c')->model('goods_type');
        $this->obj_goods = kernel::single('goodsapi_goods');
    }

    //添加商品类型接口
    function shopex_type_add(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

       //检查应用级必填参数
        $must_params = array(
            'name','is_default','is_physical','is_has_brand','is_has_params','must_minfo'
            ,'props','params'
        );
        $this->check_params($must_params);

        //判断新增的类型名称是否已存在
        $type_name = $this->type_model->getList('*',array('name'=>$params['name']));
        if($type_name) {
            $error['code'] = null;
            $error['msg']  = '新增类型名称已经存在';
            $this->send_error($error);
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
        $save_data = array(
            'name'=>$params['name'],
            'alias'=>$params['alias'],
            //'is_def'=>$params['is_default'],
            'is_physical'=>$params['is_physical']=='true'?1:null,
            'setting'=>$setting,
            'disabled'=>$params['disabled'],
        );

        //保存规格数据
        if( !empty($params['spec_names']) ){
            foreach( explode(',',$params['spec_names']) as $s_k=>$spec_name ){
                $filter_spec = $this->obj_goods->get_spec_name($spec_name);
                $specs = app::get('b2c')->model('specification')->dump($filter_spec,'spec_id,spec_show_type');
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
        $arr_props = json_decode($params['props'],true);
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
        foreach( $arr_params['params'] as $params_k => $type_params){
            //参数组名称
            $params_names = $type_params['prop_name'];
            //参数数组
            $name_value = explode(',',$type_params['prop_value']);
            foreach( $name_value as $params_name ){
                $name[$params_name] = '';
            }
            $save_data['params'][$params_names] = $name;
        }

        //保存购物必填信息
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

        $rs = $this->type_model->save( $save_data );
        if( $rs ){
            //品牌绑定关系
            $brandtype = $this->type_model->db->select( 'SELECT brand_id,type_id FROM sdb_b2c_type_brand WHERE type_id = '.intval($save_data['type_id']).' ORDER BY brand_id ASC ' );
            $data['last_modify'] = time();
            $this->send_success($data);
        }else{
            $error['code'] = null;
            $error['msg'] = '类型保存失败';
            $this->send_error($error);
        }
    }
}
