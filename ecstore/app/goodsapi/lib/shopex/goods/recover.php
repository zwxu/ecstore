<?php

class goodsapi_shopex_goods_recover extends goodsapi_goodsapi{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->recycle_model = app::get('desktop')->model('recycle');
        $this->goods_model = app::get('b2c')->model('goods');
    }

    //恢复商品信息接口
    function shopex_goods_recover(){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        //检查应用级必填参数
        if( !isset($params['bns'])  ){
            $error['code']  = null;
            $error['msg']  = '必填参数未定义';
            $this->send_error($error);
        }elseif(empty($params['bns'])){
            $data = array();
            $this->send_success($data);
        }

        if($params['bns']){
            foreach( explode(',',$params['bns']) as $goods_bn ){
                $arr_recover = $this->recycle_model->getList('item_id,item_sdf',array('item_sdf|has'=>$goods_bn));
                if(!$arr_recover) break;
                if($this->goods_model->save($arr_recover[0]['item_sdf'])){
                    $this->recycle_model->delete(array('item_id'=>$arr_recover[0]['item_id']));
                }else{
                    $this->send_error(array('code'=>'0x004'));
                }
            }
        }
        $this->send_success();
    }
}

