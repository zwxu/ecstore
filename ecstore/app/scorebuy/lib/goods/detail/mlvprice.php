<?php
class scorebuy_goods_detail_mlvprice{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid,$aid){
        $render = $this->app->render();
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }

        if ($aGoods['product']){
            $memLvScoreObj = $this->app->model('memberlvscore');
            $memLvs = $memLvScoreObj->getMemLvScoreByIds($aid,$gid);
            $render->pagedata['mlvPrice'] = $memLvs;
        }

        return $render->fetch('site/product/mlv_price.html');
    }

}

