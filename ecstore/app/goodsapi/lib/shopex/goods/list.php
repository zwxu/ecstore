<?php

class goodsapi_shopex_goods_list extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->goods_model = app::get('b2c')->model('goods');
    }

    //查找商品信息列表接口
    function shopex_goods_list (){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        if(!isset($params['bns'])){
            $this->send_error(array('code'=>'0x003'));
        }elseif(empty($params['bns'])){
            $data = array();
            $this->send_success($data);
        }

        /** 生成过滤条件 **/
        $filter = explode(',',$params['bns']);


        //生成要返回的列
        $columns = '*';
        if( $params['columns']){
            $columns = explode('|',$params['columns']);
        }

        foreach( $filter as $goods_bn){
            $goods_filter = array('bn'=>$goods_bn);
            //得到基本的商品数据
            $rows = $this->goods_model->getList($columns,$filter);
            if( !$rows ){
                $this->send_error(array('code'=>'0x004'));
            }

            /**
             * 得到返回的商品数据
             */
            $data_goods = array();
            $data_goods[] = $this->_get_item_detail($row[0]);
        }
        $data['item_total'] = count($rows);
        $data['goods'] = $data_goods;
        $this->send_success($data);
    }

}
