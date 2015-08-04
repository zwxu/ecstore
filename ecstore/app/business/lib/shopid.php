<?php

class business_shopid
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
	
    //插入咨询添加store_id
    public function setShopId(&$arr){
        
        $ogoods = app::get('b2c')->model('goods');

        $oMemberComments = app::get('b2c')->model('member_comments');
        $oMemberComments->use_meta();
        if($arr['type_id'])
        $goods = $ogoods->getList('store_id',array('goods_id'=>$arr['type_id']));

        $arr['store_id'] = $goods[0]['store_id'];

    }
}