<?php
class spike_goods_detail_store{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null ){
        //获取活动库存
        $applyObj = app::get('spike')->model('spikeapply');
        $act_id = $applyObj->getNoEndActIdByGoodsId($gid);
        if($act_id){
            $apply = $applyObj->dump(array('gid'=>$gid,'id'=>$act_id),'remainnums');
            $act_real_store = $apply['remainnums'];
        }else{
            $act_real_store = 0;
        }

        $render = $this->app->render();
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }
        
        if( !isset($aGoods['_real_store']) ) $aGoods['_real_store'] = $aGoods['store'] - $aGoods['store_freeze'];

        if($act_real_store != ''){
            $aGoods['store'] = $act_real_store;
        }

        $aGoods['store'] = intval($aGoods['store']);
        //计算商品冻结总数
        $aGoods['freez'] = 0;
        if(count($aGoods['product'])){
            foreach($aGoods['product'] as $pdk=>$pdv){
                if($pdv['freez']) {
                    $aGoods['freez'] +=  $pdv['freez'];
                }

                //处理货品库存与商品真实总库 取小的值
                $aGoods['product'][$pdk]['store'] = min($pdv['store'],$goods_real_store);
            }
        }

        $render->pagedata['goods'] = $aGoods;
        //product-index内调用b2c_goods_detail_show时，会把数组product-index方法的$this->pagedata['goods']['product_freez']的值给替换掉,将冻结库存位置加到这里@lujy,

        //--end
        $render->pagedata['site_show_storage'] = app::get('b2c')->getConf('site.show_storage');
        return $render->fetch('site/product/info/store.html');
    }
    
    function init_store( $gid,&$aGoods=null ) {
        if( !$aGoods ){
            $o = kernel::single('b2c_goods_model');
            $aGoods = $o->getGoods($gid);
        }
        $aGoods['store'] = 0;
        if( $aGoods['product'] && is_array($aGoods['product']) ) {
            foreach( $aGoods['product'] as $row ) {
                $aGoods['store'] += $row['store']-$row['freez'];
            }
        }
    }

}

