<?php

class goodsapi_shopex_brand_list extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->brand_model = app::get('b2c')->model('brand');
    }

    //获取商品品牌列表接口
    function shopex_brand_list(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        if($params['page_no'] == -1){
            $item_total = $this->brand_model->count();
            $data['item_total'] = $item_total;
            $this->send_success($data);
        }else{
            $item_total = $this->brand_model->count();
            $brands = $this->brand_model->getList("*",array(),$page_offset,$page_size);
        }

        foreach( $brands as $key=>$value){
            //将brand_logo图片id 转化为可直接访问的地址
            $brand_logo = base_storager::image_path($value['brand_logo']);
            $data[$key] = array(
                'brand_name' => $value['brand_name'],
                'brand_url'  => $value['brand_url'],
                'brand_desc' => $value['brand_desc'],
                'brand_logo' => substr($brand_logo,0,-13),
                'brand_alias' => $value['brand_keywords'],
                'disabled'   => ($value['disabled'])? 'true' : 'false',
                'order_by'   => $value['ordernum'],
                'brand_setting' => serialize($value['brand_setting']),
                'last_modify' => time(),
            );
        }
        $data['item_total'] = $item_total;
        $this->send_success($data);
    }
}
