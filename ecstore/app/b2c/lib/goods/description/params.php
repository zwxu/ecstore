<?php
class b2c_goods_description_params{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show($gid=0, $aGoods=null, $custom_view=""){
        $render = $this->app->render();
        if( !$aGoods ){
            //$o = kernel::single('b2c_goods_model');
            //$aGoods = $o->getGoods($gid);
            $aGoods = $this->_get_goods($gid);
        }

        $render->pagedata['goods'] = $aGoods;
        $file = $custom_view?$custom_view:"site/product/goods_param.html";
		if($custom_view){
			return $render->fetch($file,'',true);
        }
        return $render->fetch($file);

    }

    function _get_goods($gid){
        $render = $this->app->render();
        $objGoods = &$this->app->model('goods');
        $aGoods_list = $objGoods->getList("goods_id,name,bn,brief,brand_id,type_id,unit,params,p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20,p_21,p_22,p_23,p_24,p_25,p_26,p_27,p_28,p_29,p_30,p_31,p_32,p_33,p_34,p_35,p_36,p_37,p_38,p_39,p_40,p_41,p_42,p_43,p_44,p_45,p_46,p_47,p_48,p_49,p_50",array('goods_id'=>$gid));
        $aGoods['name'] = $aGoods_list[0]['name'];
        $aGoods['bn'] = $aGoods_list[0]['bn'];
        $aGoods['brief'] = $aGoods_list[0]['brief'];
        $aGoods['params'] = $aGoods_list[0]['params'];

        $goods_type = app::get("b2c")->model("goods_type");
        cachemgr::co_start();
        if(!cachemgr::get("goods_type_props_value_list2dump".$aGoods_list[0]['type_id'], $goods_type_data)){
            $goods_type_data = $goods_type->dump($aGoods_list[0]['type_id']);
            cachemgr::set("goods_type_props_value_list2dump".$aGoods_list[0]['type_id'], $goods_type_data, cachemgr::co_end());
        }
        $aGoods['type'] = $goods_type_data;

        $brand_row = $goods_type->db->selectrow("select brand_id,brand_name,brand_keywords from sdb_b2c_brand where brand_id=".intval($aGoods_list[0]['brand_id']));
        $aGoods['brand'] = $brand_row;
        foreach ($aGoods_list[0] as $aGoods_k => $aGoods_v) {
            if(strpos($aGoods_k,"p_")===0)$aGoods['props'][$aGoods_k]['value'] = $aGoods_v;
        }
        return $aGoods;

    }

}

