<?php

class b2c_goods_promotion_price
{

    function __construct( &$app )
    {
        $this->app = $app;
    }

    public function process( $arrGoods ) {
        $return = kernel::single('b2c_cart_prefilter_promotion_goods')->get_goods_sales( $arrGoods );
        return $return;
    }
}
