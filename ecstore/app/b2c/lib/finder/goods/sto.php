<?php

 
class b2c_finder_goods_sto{    
    function __construct(&$app){
        $this->app=$app;
        $this->ui = new base_component_ui($this);
    }    
    
    /*
    var $detail_basic = '基本信息';
    function detail_basic($product_id){

         $obj_product = app::get('b2c')->model('products');
         $obj_goods =app::get('b2c')->model('goods');
         $member_goods = app::get('b2c')->model('member_goods');
         $sto_product = $member_goods->getList('distinct product_id,status',array('product_id'=>$product_id,'type'=>'sto'));
         $aTotal = $member_goods->getList('count(*) total',array('product_id'=>$product_id,'type'=>'sto'));
            $total = $aTotal[0]['total'];
            if($sto_product[0]['status'] =='ready'){
            $send_status = '未通知';
            }
              if($sto_product[0]['status'] =='send'){
            $send_status = '已通知';
            }
            $sdf = $obj_product->dump($product_id);
            $aGoods  = $obj_goods->getList('store',array('goods_id' =>$sdf['goods_id']));
            $product_name = $sdf['name'];
            $product_bn = $sdf['bn'];
            $product_store = $sdf['store'];
            if($aGoods[0]['store']>0.00){
            if($product_store>0.00){
            $sto_status = '已到货';
            }
            else{
               $sto_status = '缺货中，请紧急备货';
            }
            }
            else{
            $sto_status = '缺货中，请紧急备货';
            }
        $result = array('product_id'=>$product_id,'product_name'=>$product_name,'sto_total'=>$total,'product_bn'=>$product_bn,'sto_status'=>$sto_status,'send_status'=> $send_status);
        $render = app::get('b2c')->render();
        $render->pagedata['sto'] = $result;
        return $render->fetch('admin/goods/sto.html');
    }  */
}
