<?php

 
class b2c_cart_object_split_goods
{
    public function __construct(&$app) {
         $this->app=$app;
    }
    public function get_dlytype(&$aResult){
        if(empty($aResult['object']['goods']))return;
        $store_goods_id=array();
		foreach($aResult['object']['goods'] as $trkey => $trval){
            $store_goods_id[$trval['params']['goods_id']]=$trval['store_id'];
		}
        $mdl_goods_dly = $this->app->model('goods_dly');
        $goods_dly=$mdl_goods_dly->getList('*',array('goods'=>array_keys($store_goods_id),'manual'=>'normal'));
        $temp_goods_dly=array();
        $temp_store_id=array();
        foreach($goods_dly as $key=>$tpl){
            //删除存在快递模板的商品所对应的店铺
            if(array_key_exists($tpl['goods_id'],$store_goods_id)){
                unset($store_goods_id[$tpl['goods_id']]);
            }
            $temp_goods_dly[$tpl['goods_id']][]=$tpl['dly_id'];
        }
        //取出所有未设定快递模版的商品所对应的店铺的快递模板。
        $store_default_dly=array();
        if(!empty($store_goods_id)){
            $temp_store_id=array_values($store_goods_id);
            $mdl_dlytype = $this->app->model('dlytype');
            $store_dly_list=$mdl_dlytype->getList('dt_id,store_id',array('store_id'=>$temp_store_id,'dt_status'=>'1'));
            foreach($store_dly_list as $key=>$store_dly){
                $store_default_dly[$store_dly['store_id']][]=$store_dly['dt_id'];
            }
        }
        $dlytype_id=array();
        foreach ($aResult['object']['goods'] as $key => &$value) {
            //取的商品对应的模板信息
            if($temp_goods_dly[$value['params']['goods_id']]){//商品设定了配送模板
               $value['dly_template']=$temp_goods_dly[$value['params']['goods_id']];
            }elseif($store_default_dly[$value['store_id']]){//未设定取店铺全部。
               $value['dly_template']=$store_default_dly[$value['store_id']];
            }else{
                $value['dly_template']=array();
            }
            $dlytype_id=array_merge($dlytype_id,$value['dly_template']);
        }
        
        return $dlytype_id;
    }
    
    public function split_cart_object(&$sdf_cart,&$split_dly,$valite_dt_id=array(),&$index){
        if(empty($sdf_cart['object']['goods'])) return;
        foreach ((array)$sdf_cart['object']['goods'] as $key =>$value) {
            //如果某商品的配送方式为空。则该商品不支持该地区配送。
            $tmp_index=0;
            $t_dly=array();
            foreach($value['dly_template'] as $dkey=>$id){
                //如果不支持该地区配送，则删除该配送方式
                if(!in_array($id,$valite_dt_id)){
                    unset($sdf_cart['object']['goods'][$key]['dly_template'][$dkey]);
                    unset($value['dly_template'][$dkey]);
                }
            }
            if(!empty($value['dly_template'])){
                if(empty($split_dly[$value['store_id']]['slips'][$index])){
                    $t_dly=$value['dly_template'];
                }else{
                   $has_uintersect=false;
                   foreach($split_dly[$value['store_id']]['slips'] as $i=>$v){
                       if($i!==0){
                           $t_dly=array_uintersect($v['dly_type'], $value['dly_template'], "strcasecmp");
                           if($t_dly){
                               $has_uintersect=true;
                               $index=$i;
                               break;
                           }
                       }
                    }
                    if($has_uintersect==false){
                        $index--;
                        $t_dly=$value['dly_template'];
                    }
                }
                $tmp_index=$index;
            }
            $slip=$split_dly[$value['store_id']]['slips'][$tmp_index];
            $slip['object']['goods']['index'][]=$key;
            $slip['object']['goods']['obj_ident'][$key]=$value['obj_ident'];
            $slip['dly_type']=$t_dly;

            $slip['subtotal_consume_score']+=$value['subtotal_consume_score'];
            $slip['subtotal_gain_score']+=$value['subtotal_gain_score'];
            $slip['subtotal']+=$value['subtotal'];
            $slip['subtotal_prefilter_after']+=$value['subtotal_prefilter_after'];
            $slip['subtotal_price']+=$value['subtotal_price'];
            
            //如果不是卖家包邮
            $slip['subtotal_weight']+=$value['freight_bear']=='business'?0:$value['subtotal_weight'];
            //订单折扣优惠。
            //$slip['discount_amount_order']+=$value['discount_amount_order'];
            $slip['discount_amount']+=$value['discount_amount'];
            $slip['discount_amount_prefilter']+=$value['discount_amount_prefilter'];
            //商家是否免运费。
            $slip['store_free_shipping']+=$value['freight_bear']=='business'?0:1;             
            $split_dly[$value['store_id']]['slips'][$tmp_index]=$slip;
        }
        
        $oPickup = &app::get('business')->model('dlycorp');
        $oAddress = &app::get('business')->model('dlyaddress');
        foreach ($split_dly as $store_id=>$sgoods) {
            $pickup = $oPickup->count(array('store_id'=>$store_id, 'corp_id'=>0));
            if ($pickup) {
                $split_dly[$store_id]['pickup'] = true;
                $addrlist = $oAddress->getList('*', array('store_id'=>$store_id, 'pickup'=>'true'));
        		foreach($addrlist as $k=>$v){
        			$area = array();
        			$area = explode(':',$v['region']);
        			$area = explode('/',$area[1]);
        			if(in_array($area[0],array('北京','天津','上海','重庆'))){
        				$area[0] = '';
        			}
        			$addrlist[$k]['area_arr'] = $area;
        		}
                $split_dly[$store_id]['pickaddress'] = $addrlist;
            } else {
                $split_dly[$store_id]['pickup'] = false;
                $split_dly[$store_id]['pickaddress'] = array();
            }
        }
        
        
        /*$temp_goods=array();
        foreach($split_dly as $store_id=>$sgoods){
            $slip_split=array_keys($sgoods);
            $startSlip=current($slip_split);
            $endSlip=end($slip_split);
            foreach($sgoods['slips'] as $order_sp=>$order){
                $arr_index=$order['object']['goods']['index'];
                $end=end($arr_index);
                $start=reset($arr_index);
                foreach($arr_index as $index){
                   $value= $sdf_cart['object']['goods'][$index];
                   $value['is_first']=$index==$start? 'true':'false';
                   $value['is_last']=$index==$end? 'true':'false';
                   $value['is_first_slip']=$startSlip==$order_sp?'true':'false';
                   $value['is_last_slip']=$endSlip==$order_sp?'true':'false';
                   $value['order_split_key']=$order_sp;
                   $temp_goods[]=$value;
                }
            }
        }
        $sdf_cart['object']['goods']=$temp_goods;
        unset($temp_goods);*/
    }
}
