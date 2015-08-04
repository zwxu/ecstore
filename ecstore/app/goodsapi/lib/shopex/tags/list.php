<?php

class goodsapi_shopex_tags_list extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->tag_model = app::get('desktop')->model('tag');
    }

    //获取标签列表接口
    function shopex_tags_list(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        if($params['page_no'] == -1){
            $item_total = $this->tag_model->count(array('tag_type'=>'goods'));
            $data['item_total'] = $item_total;
            $this->send_success($data);
        }else{
            $item_total = $this->tag_model->count(array('tag_type'=>'goods'));
            $obj_tags = $this->tag_model->getList('tag_name,tag_type',array('tag_type'=>'goods'),$page_offset,$page_size);
        }

        $data  = $obj_tags;
        $data['item_total'] = $item_total;
        $this->send_success($data);
    }//end api

}

