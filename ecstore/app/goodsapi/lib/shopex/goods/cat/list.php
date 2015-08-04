<?php

class goodsapi_shopex_goods_cat_list extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    //获取商品分类列表接口
    function shopex_goods_cat_list(){
        $params = $this->params;

        //检查参数 判断调用api合法性
        $this->check($params);
        $obj_cat = app::get('b2c')->model('goods_cat');

        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        if($params['page_no'] == -1){
            $item_total = $obj_cat->count();
            $data['item_total'] = $item_total;
            $this->send_success($data);
        }else{
            $arr_cat = $obj_cat->getList('*',array(),$page_offset,$page_size);
            $item_total = $obj_cat->count();
        }

        foreach ($arr_cat as $key => $value) {
            $cat_path = '';
            if(strlen($value['cat_path']) > 1){
                $cat_path_id = explode(',', $value['cat_path']);
                $arr_cat_path = array();
                foreach ($cat_path_id as $k=>$cat_path_row) {
                    if($cat_path_row){
                        $arr_cat_path = $obj_cat->getList('cat_name',array('cat_id'=>$cat_path_row));
                        $cat_path[$k] = $arr_cat_path[0]['cat_name'];
                    } 
                }
                $cat_path = implode('->', $cat_path);
            }
            $arr_type_name = app::get('b2c')->model('goods_type')->getList('name',array('type_id'=>$value['type_id']));

            $data[$key]['cat_path'] = $cat_path;
            $data[$key]['cat_name'] = $value['cat_name'];
            $data[$key]['order_by'] = intval($value['p_order']);
            $data[$key]['desc'] = '';
            $data[$key]['disabled'] = $value['disabled']?'true':'false';
            $data[$key]['type_name'] = $arr_type_name[0]['name'];
        }

        $data['item_total'] = $item_total;
        $this->send_success($data);
    }
}
