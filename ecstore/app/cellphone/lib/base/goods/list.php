<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_goods_list extends cellphone_cellphone{

    public function __construct($app){
        parent::__construct();
        $this->app = $app;
        $this->goods_model = app::get('b2c')->model('goods');
    }

    //查找商品信息列表接口
    function base_goods_list (){
        $params = $this->params;


        //api 调用合法性检查
        //$this->check($params);

        //检查应用级必填参数
        $must_params = array(
            'order_bn'=>'商品编号'
        );
        $this->check_params($must_params);

        //测试
        $this->send(true,$params,'sucess');
        exit;

    }

}
