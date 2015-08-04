<?php
class b2c_goods_description_recommend{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show($custom_view=""){
        $render = $this->app->render();
        
        $render->pagedata['goodsRecommend'] = $this->app->getConf('goods.recommend');
	$file = $custom_view?$custom_view:'site/product/description/recommend.html';
		if($custom_view){
			return $render->fetch($file,'',true);
        }
        return $render->fetch($file);
    }

}

