<?php

class b2c_goods_list2dump{

    function __construct($app)
    {
        $this->app = $app;
    }

    function get_goods($listgoods=null,$member_lv_id=-1)
    {
        if(empty($listgoods)) return false;
        $aGoods['goods_id'] = $listgoods['goods_id'];
        $aGoods['bn'] = $listgoods['bn'];
        $aGoods['name'] = $listgoods['name'];
        $goods_type = app::get("b2c")->model("goods_type");
        cachemgr::co_start();
        if(!cachemgr::get("goods_type_props_value_list2dump".$aGoods['type_id'], $goods_type_data)){
            $goods_type_data = $goods_type->dump($listgoods['type_id']);
            cachemgr::set("goods_type_props_value_list2dump".$aGoods['type_id'], $goods_type_data, cachemgr::co_end());

        }
        $aGoods['type'] =$goods_type_data;
        $cat['cat_id'] = $listgoods['cat_id'];
        $aGoods['category'] = $cat;
        $brand_row = $goods_type->db->selectrow("select brand_id,brand_name from sdb_b2c_brand where brand_id=".intval($listgoods['brand_id']));
        $aGoods['brand'] = $brand_row;
        $aGoods['status'] = $listgoods['status'];
        $aGoods['marketable'] = $listgoods['marketable'];
        $aGoods['store'] = $listgoods['store'];
        $aGoods['notify_num'] = $listgoods['notify_num'];
        $aGoods['gain_score'] = $listgoods['gain_score'];
        $aGoods['unit'] = $listgoods['unit'];
        $aGoods['brief'] = $listgoods['brief'];
        $aGoods['image_default_id'] = $listgoods['image_default_id'];
        $aGoods['udfimg'] = $listgoods['udfimg'];
        $aGoods['thumbnail_pic'] = $listgoods['thumbnail_pic'];
        $aGoods['small_pic'] = $listgoods['small_pic'];
        $aGoods['big_pic'] = $listgoods['big_pic'];
        $aGoods['min_buy'] = $listgoods['min_buy'];
        $aGoods['package_scale'] = $listgoods['package_scale'];
        $aGoods['package_unit'] = $listgoods['package_unit'];
        $aGoods['package_use'] = $listgoods['package_use'];
        $aGoods['score_setting'] = $listgoods['score_setting'];
        $aGoods['nostore_sell'] = $listgoods['nostore_sell'];
        $aGoods['goods_setting'] = $listgoods['goods_setting'];
        $aGoods['disabled'] = $listgoods['disabled'];
        $aGoods['adjunct'] = $listgoods['adjunct'];//meta数据
        $aGoods['seo_info'] = $listgoods['seo_info'];
        $aGoods['price'] = $listgoods['price'];
        $aGoods['cost'] = $listgoods['cost'];

        $products = $this->app->model("products");
        //市场价
         if( $listgoods['mktprice'] == '' || $listgoods['mktprice'] == null )
            $aGoods['mktprice'] = $products->getRealMkt($aGoods['price']);
         else
            $aGoods['mktprice'] = $listgoods['mktprice'];

        $aGoods['weight'] = $listgoods['weight'];
        $member_lv = $this->app->model("member_lv");
        $memLv = $member_lv->getList("member_lv_id,name,dis_count");
        foreach ($memLv as $memLv_k => $memLv_v) {
            $memLvaData[$memLv_v['member_lv_id']] = $memLv_v;
        }
        $products_row = $products->db->select("SELECT p.*,l.level_id,l.price as lv_price
FROM  `sdb_b2c_products` AS p
LEFT JOIN  `sdb_b2c_goods_lv_price` AS l ON p.product_id = l.product_id
WHERE p.goods_id =".intval($aGoods['goods_id']));

        foreach($products_row as $pro_k=>$pro_v)
        {
            $price = $pro_v['price'];
            unset($pro_v['price']);
            $pro_v['spec_desc'] = unserialize($pro_v['spec_desc']);
            $productAmp[$pro_v['product_id']] = $pro_v;
            $productAmp[$pro_v['product_id']]['price']['price']['price'] = $price;
            $productAmp[$pro_v['product_id']]['price']['cost']['price'] = $pro_v['cost'];
            $productAmp[$pro_v['product_id']]['price']['mktprice']['price'] = $pro_v['mktprice'];
            if($pro_v['level_id'])$product_lv_price[$pro_v['product_id']][$pro_v['level_id']] = $pro_v['lv_price'];
            $product_id_filter[$pro_v['product_id']] = $pro_v['product_id'];
        }
        foreach($product_id_filter as $pid)
        {
            $price =  $productAmp[$pid]['price']['price']['price'];
            foreach ($memLvaData as $memLv_k => $memLv_v) {
                $rs = array(
                    'level_id' => $memLv_v['member_lv_id'],
                    'price' => ($memLv_v['dis_count']>0?$memLv_v['dis_count'] * $price:$price),
                    'title' => $memLv_v['name'],
                    'custom' => 'false'
                );
                if($product_lv_price[$pid][$memLv_v['member_lv_id']]) $rs['price'] = $product_lv_price[$pid][$memLv_v['member_lv_id']];
                $productAmp[$pid]['price']['member_lv_price'][$memLv_v['member_lv_id']] = $rs;
            }
            if (isset($productAmp[$pid]['price']) && $productAmp[$pid]['price'] && is_array($productAmp[$pid]['price']) && isset($productAmp[$pid]['price']['member_lv_price']) && $productAmp[$pid]['price']['member_lv_price'] && is_array($productAmp[$pid]['price']['member_lv_price']))
            {
                if( array_key_exists( 'member_lv_price', $productAmp[$pid]['price'] ) && array_key_exists( $member_lv_id, $productAmp[$pid]['price']['member_lv_price'] ) ){
                    $productAmp[$pid]['price']['price']['current_price'] = $productAmp[$pid]['price']['member_lv_price'][$member_lv_id]['price'];
                }else{
                    $productAmp[$pid]['price']['price']['current_price'] = $productAmp[$pid]['price']['price']['price'];
                }
            }
        }
        $aGoods['product'] = $productAmp;
        $image = app::get("image")->model("image_attach");
        $image_data = $image->getList("attach_id,image_id",array("target_id"=>intval($aGoods['goods_id']),'target_type'=>'goods'),0,-1,"attach_id asc");
        foreach($image_data as $img_k=>$img_v)
        {
            $aGoods['images'][$img_v['attach_id']] = $img_v;
        }
        //$aGoods['spec'] = 下面

        $oSpec = &$this->app->model('specification');
        if( $listgoods['spec_desc'] && is_array( $listgoods['spec_desc'] ) ){
            foreach( $listgoods['spec_desc'] as $specId => $spec ){
                $aRow = $oSpec->getList("*",array('spec_id'=>$specId));
                $aGoods['spec'][$specId] = $aRow[0];
                foreach( $spec as $pSpecId => $specValue ){
                    $aGoods['spec'][$specId]['option'][$pSpecId] = array_merge( array('private_spec_value_id'=>$pSpecId), $specValue );
                }
            }
        }
        unset($aGoods['spec_desc']);
        if( $aGoods['product'] ){
            $aProduct = current( $aGoods['product']);
            if( isset( $aProduct['price']['price']['current_price'] ) )
                $aGoods['current_price'] = $aProduct['price']['price']['current_price'];
        }else{
            if( $aGoods['price'] )
                $aGoods['current_price'] = $aGoods['price'];
        }
        foreach ($listgoods as $aGoods_k => $aGoods_v) {
                if(strpos($aGoods_k,"p_")===0)$aGoods['props'][$aGoods_k]['value'] = $aGoods_v;
            }
        $aGoods['description'] = $listgoods['intro'];
        return $aGoods;
    }
}
