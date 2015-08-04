<?php

class goodsapi_shopex_goods_listing extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->goods_model = app::get('b2c')->model('goods');
    }

    //商品上下架接口
    function shopex_goods_listing (){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        if(!isset($params['bns']) && !isset($params['time']) && !isset($params['listing']) ){
            $error['code'] = null;
            $error['msg']  = '必填参数未定义';
            $this->send_error($error);
        }

        if(empty($params['bns'])){
            $data = array();
            $this->send_success($data);
        }

        foreach( explode(',',$params['bns']) as $goods_bn ){
            $goods_id = $this->get_goods_id($goods_bn);
            if(!$goods_id) break;
            $arr_goods['goods_id'][] = $goods_id;
        }

        $flag = $this->goods_model->setEnabled($arr_goods,$params['listing']);
        if($flag)
            $this->send_success();
        else
            $this->send_error(array('code'=>'0x004'));
    }//end api

}

