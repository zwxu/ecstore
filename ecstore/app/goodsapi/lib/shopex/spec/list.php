<?php

class goodsapi_shopex_spec_list extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->spec_model = app::get('b2c')->model('specification');
    }

    //获取商品规格列表接口
    function shopex_spec_list($params){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        /*如果当前用户不是系统管理员，检查当前用户操作权限（暂时不限制权限）
        if( !$this->is_admin )
            $this->user_permission($this->user_id,'catgoods');
        */
        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        if($params['page_no'] == -1){
            $item_total = $this->spec_model->count();
            $data['item_total'] = $item_total;
            $this->send_success($data);
        }else{
            $item_total = $this->spec_model->count();
            $specs = $this->spec_model->getList('*',array(),$page_offset,$page_size);
        }


        if( !$specs ){
            $this->send_success();
        }

        foreach( $specs as $key=>$value){

            $spec_id = $value['spec_id'];
            $spec_value = app::get('b2c')->model('spec_values')->getList('*',array('spec_id'=>$spec_id));

            $spec_valuse = array();
            if( $spec_value ){
                //获取规格值
                $spec_values = array();
                foreach( $spec_value as $spec_k=>$spec_v){
                    $image_url = base_storager::image_path($spec_v['spec_image']);

                    $spec_values[$spec_k] = array(
                        'spec_value' =>$spec_v['spec_value'],
                        'spec_value_alias' => $spec_v['alias'],
                        'order_by' => intval($spec_v['p_order']),
                        'image_url' => substr($image_url,0,-13),
                    );
                }

            }

            $spec_type = '图片';
            if($value['spec_type'] == 'text'){
                $spec_type = '文字';
            }
            $spec_show_type = '下拉';
            if($value['spec_show_type'] == 'flat'){
                $spec_show_type = '平铺';
            }

            //得到所有需要返回的数据
            $data[$key] = array(
                'spec_name' => $value['spec_name'],
                'alias' => $value['alias'],
                'memo' => $value['spec_memo'],
                'spec_type' => $spec_type,
                'spec_show_type' => $spec_show_type,
                'spec_values' => $spec_values,
                'order_by' => intval($value['p_order']),
                'is_show' => 'true',
                'disabled' => $value['disabled'],
                'last_modify' => time(),
            );
        }
        
        $data['item_total'] = $item_total;
        $this->send_success($data);

    }
}
