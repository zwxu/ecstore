<?php
class b2c_goods_detail_pic{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null ){
    	$render = $this->app->render();
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }
		if( !$aGoods['images'] ){
            $render->pagedata['noimage'] = 'true';
            $imageDefault = app::get('image')->getConf('image.set');
            $aGoods['images'][]['image_id'] = $imageDefault['M']['default_image'];
            $aGoods['goods']['image_default_id'] = $imageDefault['M']['default_image'];
        }else{
            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aGoods['images'] as $k=>$v){
                //todo 暂时cache 处理，以后优化的时候在storager中判断
                //判断是否对应的图片是否存在，不存在则用默认图片显示
                if(!cachemgr::get('goods_image'.intval($v['image_id']),$image_id)){
                    cachemgr::co_start();
                    $image_id = $oImage->getList("image_id",array('image_id'=>$v['image_id']));
                    cachemgr::set('goods_image'.intval($v['image_id']), $image_id, cachemgr::co_end());
                }
                if(!$image_id){
                    if($aGoods['image_default_id'] == $v['image_id']){
                        $aGoods['image_default_id'] = $imageDefault['M']['default_image'];
                    }
                    $aGoods['images'][$k]['image_id'] = $imageDefault['M']['default_image'];
                }
            }
        }
        $render->pagedata['goods'] = $aGoods;
        return $render->fetch('site/product/goodspic.html');
    }



}

