<?php
  
class cellphone_cart_package extends cellphone_cellphone
{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;

        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function get_old_item()
    {
        $objCartObj = app::get('b2c')->model('cart_objects');
        $aReturn = array();
        foreach($objCartObj->getList('*',array('obj_type'=>'package'),0,-1,'time desc') as $row){
            $aReturn[$row['obj_ident']] = $row;
        }
        return $aReturn;
    }
    
    function get_cart_item($aData=array(),$picSize)
    {
        $aReturn = array();
        $goods_id = array();
        foreach((array)$aData['package'] as $row){
            if(!isset($row['obj_items']) || empty($row['obj_items'])){
                continue;
            }
            $aReturn[$row['obj_ident']] = array();
            $aReturn[$row['obj_ident']]['product'][] = array(
                'obj_ident'=>$row['obj_ident'],
                'object_type'=>$row['obj_type'],
                'goods_id'=>$row['package']['id'],
                'product_id'=>$row['package']['id'],
                'name'=>$row['package']['name'],
                'buy_price'=>$row['package']['amount'],
                'consume_score'=>null,
                'quantity'=>$row['quantity'],
                'min_buy'=>1,
                'max_buy'=>$row['package']['store']-$row['package']['freez'],
                'store'=>$row['package']['store']-$row['package']['freez'],
                'spec_info'=>null,
                'image'=>$this->get_img_url($row['package']['image'],$picSize),
                'subtotal'=>(string)$row['subtotal'],
            );
            foreach((array)$row['obj_items'] as $value){
                $aReturn[$row['obj_ident']]['package'][] = array(
                    'obj_ident'=>$row['obj_ident'],
                    'object_type'=>'normal',
                    'goods_id'=>$value['goods_id'],
                    'product_id'=>$value['product_id'],
                    'name'=>$value['name'],
                    'buy_price'=>$value['price']['buy_price'],
                    'consume_score'=>null,
                    'quantity'=>$row['quantity'],
                    'min_buy'=>1,
                    'max_buy'=>$row['package']['store']-$row['package']['freez'],
                    'store'=>$row['package']['store']-$row['package']['freez'],
                    'spec_info'=>$value['spec_info'],
                    'image'=>$this->get_img_url($value['default_image']['thumbnail'],$picSize),
                    'subtotal'=>null,
                    'is_fav'=>0,
                );
                $goods_id[$value['goods_id']] = $value['goods_id'];
            }
            $aReturn[$row['obj_ident']]['storeinfo'] = array(
                'store_name' => $row['store_name'],
                'store_id' => $row['store_id'],
            );
            $aReturn[$row['obj_ident']]['subtotal_consume_score'] = $row['subtotal_consume_score'];
            $aReturn[$row['obj_ident']]['subtotal_gain_score'] = $row['subtotal_gain_score'];
            $aReturn[$row['obj_ident']]['subtotal'] = (string)$row['subtotal'];
            $aReturn[$row['obj_ident']]['subtotal_prefilter_after'] = $row['subtotal_prefilter_after'];
            $aReturn[$row['obj_ident']]['subtotal_price'] = $row['subtotal_price'];
            $aReturn[$row['obj_ident']]['subtotal_weight'] = $row['subtotal_weight'];
            $aReturn[$row['obj_ident']]['discount_amount'] = $row['discount_amount'];
            $aReturn[$row['obj_ident']]['discount_amount_prefilter'] = $row['discount_amount_prefilter'];
            $aReturn[$row['obj_ident']]['freight_bear'] = $row['package']['freight_bear'];
        }
        $obj_fav = app::get('b2c')->model('member_goods');
        $siteMember = $this->get_current_member();
        if(!empty($goods_id) && $siteMember['member_id']){
            $goods_fav = array();
            foreach($obj_fav->getList('goods_id',array('goods_id'=>array_keys($goods_id),'member_id'=>$siteMember['member_id'],'status'=>'ready','disabled'=>'false','type'=>'fav','object_type'=>'goods')) as $item){
                $goods_fav[$item['goods_id']] = $item['goods_id'];
            }
            foreach($aReturn as $pk => $pv){
                foreach((array)$pv['package'] as $ck => $cv){
                    if(in_array($cv['goods_id'],$goods_fav))
                    $aReturn[$pk]['package'][$ck]['is_fav'] = 1;
                }
            }
        }
        return $aReturn;
    }
    
    public function get_item($aData=array(),$picSize,&$totle=0.0)
    {
        $aReturn = array();
        $objPackage = app::get('package')->model('attendactivity');
        $objMath = kernel::single("ectools_math");
        $activity = array();
        $products = array();
        foreach((array)$aData as $row){
            if($row['obj_type'] != 'package' || empty($row['params']['id']))continue;
            $aReturn[$row['obj_ident']]['product'][] = array(
                'obj_ident'=>$row['obj_ident'],
                'object_type'=>'package',
                'goods_id'=>$row['params']['id'],
                'product_id'=>$row['params']['id'],
                'name'=>null,
                'buy_price'=>null,
                'consume_score'=>null,
                'quantity'=>$row['quantity'],
                'min_buy'=>null,
                'max_buy'=>null,
                'store'=>null,
                'spec_info'=>null,
                'image'=>null,
                'subtotal'=>null,
            );
            $activity[] = $row['params']['id'];
            if(!isset($row['params']['products']) || empty($row['params']['products'])){
                unset($aReturn);
            }else{
                foreach((array)$row['params']['products'] as $value){
                    $aReturn[$row['obj_ident']]['package'][] = array(
                        'obj_ident'=>$row['obj_ident'],
                        'object_type'=>'normal',
                        'goods_id'=>$value['goods_id'],
                        'product_id'=>$value['product_id'],
                        'name'=>null,
                        'buy_price'=>null,
                        'consume_score'=>null,
                        'quantity'=>null,
                        'min_buy'=>null,
                        'max_buy'=>null,
                        'store'=>null,
                        'spec_info'=>null,
                        'image'=>null,
                        'subtotal'=>null,
                    );
                    $products[] = $value['product_id'];
                }
            }
        }
        if(empty($activity) ||empty($products)){
            unset($aReturn);
        }else{
            $items = array();
            foreach($objPackage->getList('id,name,amount,store,freez,image',array('id'=>$activity),0,-1) as $row){
                $items['packages'][$row['id']] = $row;
            }
            $sql = "select p.goods_id,p.product_id,p.name,p.price as buy_price,p.store,p.freez,p.spec_info,g.image_default_id,g.udfimg,g.thumbnail_pic 
            from sdb_b2c_products as p left join sdb_b2c_goods as g on p.goods_id=g.goods_id where product_id in (".implode(',',$products).")";
            foreach($objPackage->db->select($sql) as $row){
                $items['products'][$row['product_id']] = $row;
            }
            foreach($aReturn as $k => $row){
                foreach((array)$row['product'] as $ck => $value){
                    if(!array_key_exists($value['product_id'],$items['packages'])){
                        unset($aReturn[$k]);
                        continue 2;
                    }
                    $aReturn[$k]['product'][$ck]['name'] = $items['packages'][$value['product_id']]['name'];
                    $aReturn[$k]['product'][$ck]['buy_price'] = $items['packages'][$value['product_id']]['amount'];
                    $aReturn[$k]['product'][$ck]['store'] = (int)$items['packages'][$value['product_id']]['store']-(int)$items['packages'][$value['product_id']]['freez'];
                    $aReturn[$k]['product'][$ck]['image'] = $this->get_img_url($items['packages'][$value['product_id']]['image'],$picSize);
                    $totle += $aReturn[$k]['product'][$ck]['subtotal'] = (string)$objMath->number_multiple(array($value['quantity'],$items['packages'][$value['product_id']]['amount']));
                }
                foreach((array)$row['package'] as $ck => $value){
                    if(!array_key_exists($value['product_id'],$items['products'])){
                        unset($aReturn[$k]['package'][$ck]);
                        continue;
                    }
                    $aReturn[$k]['package'][$ck]['name'] = $items['products'][$value['product_id']]['name'];
                    $aReturn[$k]['package'][$ck]['buy_price'] = $items['products'][$value['product_id']]['buy_price'];
                    $aReturn[$k]['package'][$ck]['store'] = (int)$items['products'][$value['product_id']]['store']-(int)$items['products'][$value['product_id']]['freez'];
                    $aReturn[$k]['package'][$ck]['spec_info'] = $items['products'][$value['product_id']]['spec_info'];
                    $aReturn[$k]['package'][$ck]['image'] = $items['products'][$value['product_id']]['udfimg']?$this->get_img_url($items['products'][$value['product_id']]['thumbnail_pic'],$picSize):$this->get_img_url($items['products'][$value['product_id']]['image_default_id'],$picSize);
                    
                }
            }
        }
        return $aReturn;
    }
    
    public function get_dlytype(&$aResult)
    {
        if(empty($aResult))return;
        $store_goods_id=array();
        foreach((array)$aResult as $row){
            if($row['product'][0]['object_type'] != 'package')continue;
            $store_goods_id[$row['product'][0]['goods_id']]=$row['storeinfo']['store_id'];
        }
        if(empty($store_goods_id))return;
        $mdl_goods_dly = app::get('b2c')->model('goods_dly');
        $goods_dly=$mdl_goods_dly->getList('*',array('goods'=>array_keys($store_goods_id),'manual'=>'package'));
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
            $mdl_dlytype = app::get('b2c')->model('dlytype');
            $store_dly_list=$mdl_dlytype->getList('dt_id,store_id',array('store_id'=>$temp_store_id,'dt_status'=>'1'));
            foreach($store_dly_list as $key=>$store_dly){
                $store_default_dly[$store_dly['store_id']][]=$store_dly['dt_id'];
            }
        }
        $dlytype_id=array();
        foreach($aResult as $key => &$value){
            if($value['product'][0]['object_type'] != 'package')continue;
            //取的商品对应的模板信息
            if($temp_goods_dly[$value['product'][0]['goods_id']]){//商品设定了配送模板
               $value['dly_template']=$temp_goods_dly[$value['product'][0]['goods_id']];
            }else{//未设定取店铺全部。
               $value['dly_template']=$store_default_dly[$value['storeinfo']['store_id']];
            }
            $dlytype_id=array_merge((array)$dlytype_id,$value['dly_template']);
        }
        
        return $dlytype_id;
    }
    
    public function split_cart_object(&$sdf_cart,&$split_dly,$valite_dt_id=array(),&$index)
    {
        foreach((array)$sdf_cart as $key =>$value){
            if($value['product'][0]['object_type'] != 'package')continue;
            //如果某商品的配送方式为空。则该商品不支持该地区配送。
            $tmp_index=0;
            $t_dly=array();
            foreach($value['dly_template'] as $dkey=>$id){
                //如果不支持该地区配送，则删除该配送方式
                if(!in_array($id,$valite_dt_id)){
                    unset($sdf_cart[$key]['dly_template'][$dkey]);
                    unset($value['dly_template'][$dkey]);
                }
            }
            if(!empty($value['dly_template'])){
                if(empty($split_dly[$value['storeinfo']['store_id']]['slips'][$index])){
                    $t_dly=$value['dly_template'];
                }else{
                   $has_uintersect=false;
                   foreach($split_dly[$value['storeinfo']['store_id']]['slips'] as $i=>$v){
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
            $slip=$split_dly[$value['storeinfo']['store_id']]['slips'][$tmp_index];
            $slip['object'][]=$key;
            //$slip['object']['package']['obj_ident'][$key]=$key;
            $slip['dly_type']=$t_dly;

            $slip['subtotal_consume_score']+=$value['subtotal_consume_score'];
            $slip['subtotal_gain_score']+=$value['subtotal_gain_score'];
            $slip['subtotal']+=$value['subtotal'];
            $slip['subtotal_prefilter_after']+=$value['subtotal_prefilter_after'];
            $slip['subtotal_price']+=$value['subtotal_price'];
            
            //如果不是卖家包邮
            $slip['subtotal_weight']+=$value['package']['freight_bear']=='business'?0:$value['subtotal_weight'];
            //订单折扣优惠。
            //$slip['discount_amount_order']+=$value['discount_amount_order'];
            $slip['discount_amount']+=$value['discount_amount'];
            $slip['discount_amount_prefilter']+=$value['discount_amount_prefilter'];
            //商家是否免运费。
            $slip['store_free_shipping']+=$value['package']['freight_bear']=='business'?0:1;             
            $split_dly[$value['storeinfo']['store_id']]['slips'][$tmp_index]=$slip;
            $split_dly[$value['storeinfo']['store_id']]['info'] = $value['storeinfo'];
        }
    }
    
    function params_to_add($params,&$data=array())
    {
        if(empty($params) || $params['type'] != 'package') return;
        $params['products'] = json_decode($params['products'],1);
        $data = array(
            'package'=>array(
                'id'=>(string)$params['goods_id'],
                'products'=>$params['products'],
                'num'=>$params['num'],
            ),
            'package',
        );
    }
    
    function params_to_update($params,&$data=array())
    {
    }
    
    function params_to_delete($params,&$data=array())
    {
    }
}