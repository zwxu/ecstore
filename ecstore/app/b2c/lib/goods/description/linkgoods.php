<?php
class b2c_goods_description_linkgoods{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null, $custom_view=""){
        $render = $this->app->render();
        if( !$aGoods ){
            $objGoods = $this->app->model("goods");
            $objProduct = $this->app->model("products");
            $aLinkId['goods_id'] = array();
            foreach($objGoods->getLinkList($gid) as $rows){
                if($rows['goods_1']==$gid) $aLinkId['goods_id'][] = $rows['goods_2'];
                else $aLinkId['goods_id'][] = $rows['goods_1'];
            }
            if(count($aLinkId['goods_id'])>0){
                $aLinkId['marketable'] = 'true';
                $aGoods['link'] = $objGoods->getList('*',$aLinkId,0,500);
                $aGoods['link_count'] = count($aLinkId['goods_id']);
            }

            $oGoodsLv = &$this->app->model('goods_lv_price');

            $siteMember = kernel::single('b2c_ctl_site_product')->get_current_member();

            $oMlv = &$this->app->model('member_lv');
            $mlv = $oMlv->db_dump( $siteMember['member_lv'],'dis_count' );
            if(is_array($aGoods['link'])){
                foreach ($aGoods['link'] as $key=>&$val) {
                    $temp = $objProduct->getList('product_id, spec_info, price, freez, store,   marketable, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'));
                    if( $this->site_member_lv_id ){
                        $tmpGoods = array();
                        foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=>$this->site_member_lv_id ) ) as $k => $v ){
                            $tmpGoods[$v['product_id']] = $v['price'];
                        }
                        foreach( $temp as &$tv ){
                            $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                        }
                        $val['price'] = $tv['price'];
                    }
                    $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                    if(!empty($promotion_price['price'])){
                        $val['price'] = $promotion_price['price'];
                        $val['show_button'] = $promotion_price['show_button'];
                        $val['timebuy_over'] = $promotion_price['timebuy_over'];
                    }
                    $aGoods['link'][$key]['spec_desc_info'] = $temp;
                    $aGoods['link'][$key]['product_id'] = $temp[0]['product_id'];
                }
            }
        }

        $render->pagedata['setting']['buytarget'] = $this->app->getConf('site.buy.target');
        //$siteMember = kernel::single('b2c_ctl_site_product')->get_current_member();
        if(!$siteMember['member_id']){
            $render->pagedata['login'] = 'nologin';
        }
        $render->pagedata['goods'] = $aGoods;
		$file = $custom_view?$custom_view:"site/product/description/goodslink.html";
        if($custom_view){
			return $render->fetch($file,'',true);
        }
        return $render->fetch($file);
    }

}

