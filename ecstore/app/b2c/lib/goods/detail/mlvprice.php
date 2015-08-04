<?php
class b2c_goods_detail_mlvprice{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null,$siteMember=null ){
        $render = $this->app->render();
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }

        if ($aGoods['product']){
            if(empty($siteMember))$siteMember = $this->app->model("members")->get_current_member();
            $priceArea = array();
            if ($siteMember['member_lv'])
                $mlv = $siteMember['member_lv'];
            else{
                $level=&$this->app->model('member_lv');
                $mlv=$level->get_default_lv();
            }
            if ($mlv){
               $aConfig = kernel::single('b2c_cart_prefilter_promotion_goods')->_init_rule_public(array($gid),array('member_lv'=>'false'));
                foreach($aGoods['product'] as $gpk => &$gpv){
                   $promotion_price = kernel::single('b2c_goods_promotion_price')->process($gpv,$aConfig);
                   $gpv['price']['price']['current_price'] = empty($promotion_price['price'])?$gpv['price']['price']['current_price']:$promotion_price['price'];
                   $gpv['price']['price']['price'] = empty($promotion_price['price'])?$gpv['price']['price']['price']:$promotion_price['price'];
                   if(is_array($gpv['price']['member_lv_price'])){
                       foreach($gpv['price']['member_lv_price'] as $mk=>&$mv){
                           $mv['price'] = empty($promotion_price['price'])?$mv['price']:$promotion_price['price'];
                       }
                   }
                }
            }
        }

        $oMlv = $this->app->model('member_lv');
        if(!cachemgr::get('member_evel_list',$mLevelList)){
            cachemgr::co_start();
            $mLevelList = $oMlv->getList('*','',0,-1);
            cachemgr::set('member_evel_list', $mLevelList, cachemgr::co_end());
        }
        $render->pagedata['mLevel'] = $mLevelList;

        $render->pagedata['goods'] = $aGoods;

        return $render->fetch('site/product/mlv_price.html');
    }

}

