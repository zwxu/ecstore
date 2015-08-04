<?php
class gift_goods_detail_button{
    function __construct( &$app ) {
        $this->app = $app;
    }
    /**
     * 取得赠品按钮模板
     *
     * @param int $gid 赠品ID
     * @param array &$aGoods 商品信息
     * @return string
     */
    function show( $gid, &$aGoods=null ){
        $render = $this->app->render();
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }
        $render->pagedata['goods'] = $aGoods;
        return $render->fetch('site/product/gallerybutton.html');
    }


}
