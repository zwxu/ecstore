<?php

class goodsapi_shopex_type_list extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->type = app::get('b2c')->model('goods_type');
    }

    //获取商品分类列表接口
    function shopex_type_list(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //如果当前用户不是系统管理员，检查当前用户操作权限
        //if( !$this->is_admin )
            //$this->user_permission($this->user_id,'goods');

        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        if($params['page_no'] == -1){
            $item_total = $this->type->count();
            $data['item_total'] = $item_total;
            $this->send_success($data);
        }else{
            $item_total = $this->type->count();
            $gtype = $this->type->getList('*',array(),$page_offset,$page_size);
        }

        foreach($gtype as $key=>$value){
               $subsdf = array(
                'spec'=>array('*',array('spec:specification'=>array('spec_name,spec_memo,alias'))),
                'brand'=>array('brand_id'),
                'props'=>array('*',array('props_value'=>array('*',null, array( 0,-1,'order_by ASC' ))) )
            );
            $gtypes = $this->type->dump($value['type_id'],'*',$subsdf);

            $spec_names = array();
            $spec_alias = array();
            //获取关联规格的名称,别名
            foreach($gtypes['spec'] as $spec_k=>$spec_v){
                $spec_names[$spec_k] = $spec_v['spec']['spec_name'];
                $spec_alias[$spec_k] = $spec_v['spec']['alias'];
            }

            $brand_names = array();
            //获取到关联品牌名称
            foreach($gtypes['brand'] as $b_k => $b_v){
                $brand_id = $b_v['brand_id'];
                $brand = app::get('b2c')->model('brand')->getList('brand_name',array('brand_id'=>$brand_id));
                $brand_names[$b_k] = $brand[0]['brand_name'];
            }

            $props = array();
            //获取类型扩展属性数据
            foreach($gtypes['props'] as $p_k=>$p_v){
                $props[$p_k]['prop_name'] = $p_v['name'];
                $props[$p_k]['alias'] = $p_v['alias'];
                $props[$p_k]['memo'] = '';
                $props[$p_k]['show_type'] = $this->_props_show_type($p_v);
                $props[$p_k]['order_by'] = intval($p_v['ordernum']);
                if($p_v['show'] == 'on')
                    $props[$p_k]['is_show'] = 'true';
                else
                   $props[$p_k]['is_show'] = 'false';
                $props[$p_k]['disabled'] = 'false';
                $props[$p_k]['prop_type'] = 1;
                $props[$p_k]['prop_value'] = implode(',',$p_v['options']);
            }

            //获取到详细参数列表数据
            $goods_params = array();
            foreach($gtypes['params'] as $params_k=>$params_v){
                $goods_params[] = array(
                    'prop_name'=>$params_k,
                    'show_type'=>'true',
                    'is_show'=>'true',
                    'prop_type'=>2,
                    'prop_value' =>implode(',',array_keys($params_v)),
                );
            }

            //获取到购物必填项列表数据
            $minfo = array();
            if(!empty($gtypes['minfo'])){
                foreach($gtypes['minfo'] as $m_k=>$m_v){
                    if($m_v['type'] == 'input'){
                        $minfo_show_type = 0;
                    }elseif($m_v['type'] == 'text'){
                        $minfo_show_type = 1;
                    }else{
                        $minfo_show_type = 2;
                    }

                    $minfo[$m_k] = array(
                        'prop_name'=>$m_v['label'],
                        'show_type'=>$minfo_show_type,
                        'is_show'=>'true',
                        'prop_type'=>3,
                        'prop_value'=>implode(',',$m_v['options'])
                    );
                }
            }else{
                $minfo = array();
            }
            //api返回的基本数据
            $data[$key] = array(
                'name'=>$value['name'],
                'alias'=>$value['alias'],
                'is_default'=> $value['is_def'],
                'is_physical'=>$value['is_physical']?'true':'false',
                'is_has_brand' => $value['setting']['use_brand']?'true':'false',
                'is_has_prop'=>$value['setting']['use_props']?'true':'false',
                'is_has_params'=>$value['setting']['use_params']?'true':'false',
                'is_must_minfo'=>$value['setting']['use_minfo']?'true':'false',
                'disabled' => $value['disabled'],
                'spec_names' => implode(",", $spec_names),
                'spec_alias' => implode("->", $spec_alias),
                'brand_names' => implode(',',$brand_names),
                'props' => $props,
                'params'=>$goods_params,
                'must_minfo'=>$minfo,
                'last_modify'=>time(),
            );
        }

        $data['item_total'] = $item_total;
        $this->send_success($data);
    }

    function _props_show_type($row){
        $show_type = 2;
        switch($row){
            case $row['type'] == 'input' && $row['search'] == 'input':
                $show_type = 0;
            break;
            case $row['type'] == 'input' && $row['search'] == 'disabled':
                $show_type = 1;
            break;
            case $row['type'] == 'select' && $row['search'] == 'nav':
                $show_type = 2;
            break;
            case $row['type'] == 'select' && $row['search'] == 'select':
                $show_type = 3;
            break;
            case $row['type'] == 'select' && $row['search'] == 'disabled':
                $show_type = 4;
            break;
        }
        return $show_type;
    }
}
