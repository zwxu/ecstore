<?php
  
class cellphone_cart_split
{
    public function __construct(){
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    private $arr_parea=array();
    private $arr_area=array();
    
    public function cart_object(){
        /*$objects = array();
        $objects[] = new cellphone_cart_goods();
        $objects[] = new cellphone_cart_package();
        $objects[] = new cellphone_cart_coupon();
        return $objects;*/
        return kernel::servicelist('cellphone_cart_object');
    }
    
    public function split_order($area_id=null,&$sdf_cart,$no_coupon){
        if($no_coupon)$_SESSION['S[Cart_Fastbuy]']['coupon']=array();
        if(!isset($area_id)){
            //return;
        }
        $dt_id=array();
        //取得所有购物车类型对应的配送方式。
        foreach( $this->cart_object() as $object ) {
            if( !is_object($object) ) continue;
            if( method_exists($object,'get_dlytype') && is_callable(array($object,'get_dlytype')) ) {
               $temp_dt_id=$object->get_dlytype($sdf_cart['enable_object']);
               if(is_array($temp_dt_id)){
                    $dt_id=array_merge($dt_id,$temp_dt_id);
               }
            }
        }
        $dt_id=array_unique($dt_id);
        //取得结算单中所有的配送方式（不分店铺）。
        $all_dly_types=$this->get_dlytpye($dt_id,$area_id); 

        $all_dly_types=utils::array_change_key($all_dly_types,'dt_id');
        $valite_dt_id=array_keys($all_dly_types);
        
        //分单数据保存。
        $split_dly=array();
        $index=800;
        //根据配送方式对现有购物车类型分单。
        foreach( $this->cart_object() as $object ) {
            if( !is_object($object) ) continue;
            if( method_exists($object,'split_cart_object') && is_callable(array($object,'split_cart_object')) ) {
                $object->split_cart_object($sdf_cart['enable_object'],$split_dly,$valite_dt_id,$index);
            }
        }
        //每一张分单中的商品重量。
        foreach($split_dly as $store_id=>$sgoods){
            foreach($sgoods['slips'] as $order_sp=>$order){
                if(!empty($order['dly_type'])){
                    foreach($order['dly_type'] as $dkey=>$type_id){
                       $all_dly_types[$type_id]['subtotal_weight']=$order['subtotal_weight'];
                       $all_dly_types[$type_id]['subtotal_prefilter_after']=$order['subtotal_prefilter_after'];
                       $all_dly_types[$type_id]['store_free_shipping']=$order['store_free_shipping'];
                    }
                }
            }
        }
        
        $this->select_delivery_method($area_id,$sdf_cart,'',$all_dly_types);
        
        foreach($split_dly as $store_id=>$sgoods){
            foreach($sgoods['slips'] as $order_sp=>$order){
                $temp_dly=array();
                if(!empty($order['dly_type'])){
                    foreach($order['dly_type'] as $dkey=>$type_id){
                       
                       $temp_dly[$type_id]=array(
                           'dt_id'=>$all_dly_types[$type_id]['dt_id'],
                           'dt_name'=>$all_dly_types[$type_id]['dt_name'],
                           'store_id'=>$all_dly_types[$type_id]['store_id'],
                           'protect'=>($all_dly_types[$type_id]['protect']&&$all_dly_types[$type_id]['protect']!='false')?1:0,
                           'protect_money'=>$all_dly_types[$type_id]['protect_money'],
                           'protect_text'=>$all_dly_types[$type_id]['protect_text'],
                           'money'=>$all_dly_types[$type_id]['money'],
                           'default_type'=>'false'
                       );
                       //$temp_dly[$type_id]['dt_id']=$all_dly_types[$type_id];dt_name
                       //$temp_dly[$type_id]['default_type']='false';
                    }
                }
                if($temp_dly){
                    reset($temp_dly);
                    $temp_dly[key($temp_dly)]['default_type']='true';
                    
                }
                $split_dly[$store_id]['slips'][$order_sp]['shipping']=$temp_dly;
            }
        }
        //$sdf_cart['order_split']=$split_dly;
        //$sdf_cart['all_dly_types']=$all_dly_types;
        return $split_dly;
    }
    
    public function select_delivery_method($area_id='', &$sdf_cart, $shipping_method='',&$all_dly_types)
    {   
        $pay_app_id = $sdf_cart['pay_app_id'] ? $sdf_cart['pay_app_id'] : '';
        $controller->pagedata['shipping_method'] = json_decode($shipping_method, true);
        $shipping_id = $controller->pagedata['shipping_method']['shipping_id'];
        $controller->pagedata['is_shipping_match'] = 0;
		/** 阶梯费用只能根据优惠后的金额来处理 - 除去商品优惠和订单优惠后的最终价格 **/
        $objMath = kernel::single('ectools_math');
        $cost_item = $objMath->number_minus(array($sdf_cart['subtotal'], $sdf_cart['discount_amount_prefilter'], $sdf_cart['discount_amount_order']));

        foreach($all_dly_types as $rows)
        {
            if ($rows['protect'] && $rows['protect']!='false')
            {  
                /** 保价费界定为商品的最原始价格 **/
                $protect = $objMath->number_multiple(array($rows['subtotal_prefilter_after'], $rows['protect_rate']));
                $rows['protect_money'] = $protect>$rows['minprice']?$protect:$rows['minprice'];//保价费
                $rows['protect_text']=app::get('b2c')->_('商品价格的').($rows['protect_rate']*100).'%，'. app::get('b2c')->_('不足').$rows['minprice'].app::get('b2c')->_('元按').$rows['minprice'].app::get('b2c')->_('元计算');
            }
            if ($rows['is_threshold'])
            {
                if ($rows['threshold'])
                {
                    $rows['threshold'] = unserialize(stripslashes($rows['threshold']));
                    if (isset($rows['threshold']) && $rows['threshold'])
                    {
                        foreach ($rows['threshold'] as $res)
                        {
                            if ($res['area'][1] > 0)
                            {
                                if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                            else
                            {
                                if ($cost_item >= $res['area'][0])
                                {
                                    $rows['firstprice'] = $res['first_price'];
                                    $rows['continueprice'] = $res['continue_price'];
                                }
                            }
                        }
                    }
                }
            }
			if($rows['store_free_shipping'] === 0){
				$rows['money'] = 0;
			}else{
				$rows['money'] = @utils::cal_fee($rows['dt_expressions'], $rows['subtotal_weight'], $sdf_cart['subtotal'], $rows['firstprice'], $rows['continueprice'], $rows['firstprice']);
			}
            $shipping[$rows['dt_id']] = $rows;
            
        }
		foreach ((array)$obj_dlytype_detail_extends = kernel::servicelist('b2c.dlytype.detail.extends') as $obj)
		{
			if (method_exists($obj, 'extends_shipping_detail'))
			{
				$obj->extends_shipping_detail($shipping);
			}
		}
        $all_dly_types=$shipping;
    }
    private function get_dlytpye($dt_id=array(),$area_id=null){
        if(empty($dt_id)){
            return array();
        }
        $objdlytype = app::get('b2c')->model('dlytype');
        $filter = array('dt_status'=>'1');
        $filter['dt_id'] = $dt_id;
        $dlytype = $objdlytype->getList('*',$filter,0,-1,'ordernum ASC');
        
        if ($dlytype && is_array($dlytype))
        {	
            $areaId = $area_id;
            $setting_0 = $setting_1 = array();
            foreach ($dlytype as $key=>$value)
            {
                if ($value['setting']==1)
                {
                    //统一费用
                    $setting_1[$value['dt_id']] = $value;
                }
                else
                {
                    if ($value['def_area_fee'] == 'true')
                    {
                        $setting_0[$value['dt_id']] = $value;
                    }
                    
                    $area_fee_conf = unserialize($value['area_fee_conf']);
                    if ($area_fee_conf && is_array($area_fee_conf))
                    {
                        foreach ($area_fee_conf as $k=>$v)
                        {
                            $areas = explode(',',$v['areaGroupId']);
                            
                            // 再次解析字符
                            foreach ($areas as &$strArea)
                            {
                                if (strpos($strArea, '|') !== false)
                                {
                                    $strArea = substr($strArea, 0, strpos($strArea, '|'));
                                     if(!empty($areaId)){
                                         // 取当前area id对应的最上级的区域id
                                        $objRegions = app::get('ectools')->model('regions');
                                        if(!in_array($areaId,$this->arr_area)){
                                            $this->arr_area[$areaId]= $objRegions->dump($areaId);
                                        }
                                        $arrRegion =$this->arr_area[$areaId];
                                        
                                        while ($row = $objRegions->getRegionByParentId($arrRegion['p_region_id']))
                                        {
                                            $arrRegion = $row;
                                            $tmp_area_id = $row['region_id'];
                                            if ($tmp_area_id == $strArea)
                                            {
                                                $areaId = $tmp_area_id;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if(!empty($areaId)){
                                if(in_array($areaId,$areas)){//如果地区在其中，优先使用地区设置的配送费用，及公式
                                    $value['firstprice'] = $v['firstprice'];
                                    $value['continueprice'] = $v['continueprice'];
                                    $value['dt_expressions'] = $v['dt_expressions'];
                                    $setting_0[$value['dt_id']] = $value;
                                    break;
                                }
                            }else{
                                $value['firstprice'] = $v['firstprice'];
                                $value['continueprice'] = $v['continueprice'];
                                $value['dt_expressions'] = $v['dt_expressions'];
                                $setting_0[$value['dt_id']] = $value;
                            }
                        }
                    }
                }
            }
            
            $return = array_merge($setting_1,$setting_0);
            ksort($return);
            return $return;
        }
        return array();
    }
    
}