<?php
class b2c_goods_detail_adjunct{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null,$custom_view=''){
        $render = $this->app->render();
        if( !$aGoods ){
            $aGoods = $this->_get_goods($gid);
        }

        $aGoods['setting']['buytarget'] = $this->app->getConf('site.buy.target');

        //不是上架商品不显示 @lujy
        $adjuncts = $aGoods['adjunct'];
        if(count($adjuncts) > 0){
            $goods_model = $this->app->model('goods');
            foreach($adjuncts as $key=>$adjunct){
                $items = $adjunct['items'];
                foreach($items as $k=>$item){
                    $g_id = $item['goods_id'];
                    $goods_obj = $goods_model->dump(array('goods_id'=>$g_id));
                    $goods_status = $goods_obj['status'];
                    if($goods_status == 'false'){
                        unset($aGoods['adjunct'][$key]['items'][$k]);
                    }
                }

                //判断配件中的关联商品是否为空，如果为空，配件置空
                if(count($aGoods['adjunct'][$key]['items']) == 0){
                    unset($aGoods['adjunct'][$key]);
                }
            }
        }

        //end
        $render->pagedata['goods'] = $aGoods;
        $render->pagedata['goods_primary'] = 'adjunct';
		$file = $custom_view?$custom_view:"site/product/info/adjunct.html";
	    if($custom_view){
			return $render->fetch($file,'',true);
        }
		return $render->fetch($file);
    }

    function _get_goods($gid){
        $objGoods = $this->app->model('goods');
        $aGoods = $objGoods->getList('goods_id,adjunct,spec_desc,name,image_default_id,price,store,marketable,nostore_sell', array('goods_id'=>$gid),0,1);
        $aGoods = $aGoods[0];
        if(!is_array($aGoods['adjunct'])){
            $aGoods['adjunct'] = unserialize($aGoods['adjunct']);}
        else
            $aGoods['adjunct'] = $aGoods['adjunct'];

        //exit;
        empty($aGoods['spec_desc'])?$aGoods['is_spec']=false:$aGoods['is_spec']=true;

        if(is_array($aGoods['adjunct']) && $aGoods['adjunct']){
            foreach($aGoods['adjunct'] as $key => $rows){    //loop group
                if($rows['set_price'] == 'minus'){
                    $cols = 'product_id,goods_id,name, spec_info, store, freez, price, price-'.$rows['price'].' AS adjprice,marketable';
                }else{
                    $cols = 'product_id,goods_id,name, spec_info,store, freez, price, price*'.($rows['price']?$rows['price']:1).' AS adjprice,marketable';
                }

                if($rows['type'] == 'goods'){
                    if(!$rows['items']['product_id']) $rows['items']['product_id'] = array(-1);
                    $arr = $rows['items'];
                }else{
                    parse_str($rows['items'].'&dis_goods[]='.$gid, $arr);
                }
                $gfilter = array();
                if(isset($arr['type_id'])){
                    if(is_array($arr['props'])){
                        $c = 1;
                        foreach($arr['props'] as $pk=>$pv){
                            $p_id= 'p_'.$c;
                             foreach($pv as $sv){
                                 if($sv == '_ANY_'){
                                     unset($pv);
                                 }
                             }
                             if(isset($pv))
                                 $arr[$p_id] = $pv;
                             $c++;
                        }
                        unset($arr['props']);
                    }

                    $gId = $objGoods->getList('goods_id',$arr,0,-1);
                    if(is_array($gId)){
                        foreach($gId as $gv){
                            $gfilter['goods_id'][] = $gv['goods_id'];
                        }
                        if(empty($gfilter))
                        $gfilter['goods_id'] = '-1';
                    }
                }else{

                    $gfilter = $arr;
                }
                if($aAdj = $this->app->model('products')->getList($cols,$gfilter,0,-1)){
                    /*截取配件规格的值，去掉规格的键值*/
                    foreach($aAdj as $aAdj_key=>$spec_info){
                        $goods_ids['goods_id'][] = $spec_info['goods_id'];
                        $edit_spec_info = $this->edit_spec_info($spec_info['spec_info']);
                        $aAdj[$aAdj_key]['spec_info'] = $edit_spec_info;
                    }
                    /* end */
                    $aGoods['adjunct'][$key]['items'] = $aAdj;
                }else{
                    unset($aGoods['adjunct'][$key]);
                }
            }
        }
        //构造配件商品默认图片数据
        $adjGoodsInfo = $objGoods->getList('goods_id,image_default_id',$goods_ids);
        foreach($adjGoodsInfo  as $adjGoodsInfo_value){
            $adjunct_images[$adjGoodsInfo_value['goods_id']] = $adjGoodsInfo_value['image_default_id'];
        }
        $aGoods['adjunct_images'] = $adjunct_images;

        return $aGoods;
    }


    /**
     *截取规格值
     *@params $spec_info stirng
     *@return stirng
     */
    function edit_spec_info($spec_info){
        if(empty($spec_info)) return $spec_info;
        $arr_spec = explode('、',$spec_info);
        $edit_spec_info = '';
        foreach ($arr_spec as $value) {
            $edit_spec_info .= substr($value,stripos($value,'：')+3).'  ';
        }
        return $edit_spec_info;
    }
}
