<?php
class b2c_goods_description_intro{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null, $custom_view=""){
    	$render = $this->app->render();
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }

        $render->pagedata['goods'] = $aGoods;
		$render->pagedata['goodsbndisplay'] = $this->app->getConf('goodsbn.display.switch');
		// 商品详情页添加项埋点
		foreach( kernel::servicelist('goods_description_add_section') as $services ) {
			if ( is_object($services) ) {
				if ( method_exists($services, 'addSection') ) {
					$services->addSection($render,$render->pagedata['goods']['type']);
				}
			}
		}
		$file = $custom_view ? $custom_view : "site/product/description/intro.html";
        if($custom_view){
			return $render->fetch($file,'',true);
        }
        return $render->fetch($file);

    }

}

