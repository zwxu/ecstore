<?php

 
/**
 * 处理购物车信息(按商户分组)
 * $ 2010-04-28 20:29 $
 */
class business_cart_process_business implements b2c_interface_cart_process {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }

    
    public function get_order() {
        return 50;
    }
    
    public function process($aData,&$aResult = array(),$aConfig = array()){

		$tmp_aResult = $aResult;
		unset($aResult['object']['goods']);
		foreach($tmp_aResult['object']['goods'] as $trkey => $trval){
			$aResult['object']['goods'][] = $trval;
		}

        $mdl_b2c_goods = app::get('b2c')->model('goods');
        $mdl_business_store = app::get('business')->model('storemanger');
        foreach ((array)$aResult['object']['goods'] as $key => $value) {
            $ginfo = $mdl_b2c_goods->dump($value['params']['goods_id'], 'store_id');
            $business_goods[$ginfo['store_id']][$key] = $value['obj_ident'];
            
            $storeinfo = $mdl_business_store->dump($ginfo['store_id'], 'store_id,store_name,shop_name');

            $aResult['object']['goods'][$key] = array_merge($value, $storeinfo);

        }

        $tmp_goods = array();
        foreach ((array)$business_goods as $bgkey => $bgval) {
            $tmp_goods = array_merge($tmp_goods, $bgval);
        }

        foreach ((array)$aResult['object']['goods'] as $gkey => $gval) {
            $index = array_search($gval['obj_ident'], $tmp_goods);
            $aResult['object']['goods'][$index] = $gval;

            reset($business_goods[$gval['store_id']]);
            if($gval['obj_ident'] == current($business_goods[$gval['store_id']])){
                $aResult['object']['goods'][$index]['is_first'] = 'true';
            }
            end($business_goods[$gval['store_id']]);
            if($gval['obj_ident'] == current($business_goods[$gval['store_id']])){
                $aResult['object']['goods'][$index]['is_last'] = 'true';
            }
            
        }
        
        
        foreach((array)$aResult['object']['package'] as $key => $value){
            $business_goods[$value['store_id']][] = $value['obj_ident'];
        }
        if(!$aResult['object']['goods']){
            $tmp_package = array();
            foreach ((array)$business_goods as $bgkey => $bgval) {
                $tmp_package = array_merge($tmp_package, $bgval);
            }

            foreach ((array)$aResult['object']['package'] as $gkey => $gval) {
                $index = array_search($gval['obj_ident'], $tmp_package);
                $aResult['object']['package'][$index] = $gval;

                reset($business_goods[$gval['store_id']]);
                if($gval['obj_ident'] == current($business_goods[$gval['store_id']])){
                    $aResult['object']['package'][$index]['is_first'] = 'true';
                }
                end($business_goods[$gval['store_id']]);
                if($gval['obj_ident'] == current($business_goods[$gval['store_id']])){
                    $aResult['object']['package'][$index]['is_last'] = 'true';
                }
            }
        }
       
        $aResult['business_goods'] = json_encode($business_goods);
        //echo '<pre>';print_r($aResult);exit;
    }

}

