<?php

 
/**
 * mdl_cart 购物车model
 * $ 2010-04-28 20:03 $
 */

class b2c_mdl_cart extends dbeav_model{
    /**
     * 获取购物车数据(订单修改也走这个方法)
     *
     * @param array $aData   // $_GET $POST and so on
     * @param array $aResult // 传出的就是购物车的数据(所有的东东都打过折的 处理过的)
     * @param array $aConfig // 一些设置 (订单修改时可能要用到的当时下单的一些数据)
     * @return array(
     *            'object'=> array(
     *                          'goods'=>array(...),
     *                          'coupon'=>array(....),
     *                          ....
     *                       )
     *             'subtotal'=>'xxx',
     *             ...
     *         )
     */
    public function get_objects($aData=array(),$aResult = array(),$aConfig = array()) {
        foreach(kernel::servicelist('b2c_cart_process_apps') as $object) {
            if(!is_object($object)) continue;
            $tmp[$object->get_order()] = $object;
        }
        
        krsort($tmp);
        
        foreach($tmp as $object){
            $object->process($aData,$aResult,$aConfig);
        }
        app::get('b2c')->model('cart_objects')->setCartNum($aResult);
        
        $this->delete_item( $aResult ); //抛出购物车数据 处理购物车内想个项
        /*$empty_obj=array();
        foreach($aResult['object']as $key=>$obj){
              if(!empty($obj)){
                  foreach($obj as $okey=>$goods){
                      $aResult['order_split'][$goods['store_id']]['object'][$key][]=$goods;
                  }
              }else{
                  $empty_obj[$key]=array();
              }
        }
        foreach($aResult['order_split'] as $key=>$object){
             $aResult['order_split'][$key]['object']=array_merge($empty_obj,$object['object']);
        }*/
        return $aResult;
   }
   
   
   //后台
   public function set_cookie_cart_arr($arr_goods=array(), $member_ident='') {
       if(empty($member_ident)) return false;
       if(empty($arr_goods) || !is_array($arr_goods)) return false;
       return kernel::single("b2c_cart_object_goods")->set_cookie($member_ident, $arr_goods);
   }
   
   public function get_cookie_cart_arr($member_ident='',$memberid='-1') {
       if(empty($member_ident) || empty($memberid) ) return false;
       kernel::single('b2c_cart_objects')->set_member_id($memberid);
       return kernel::single("b2c_cart_object_goods")->get_cookie($member_ident);
   }
   
   public function del_cookie_cart_arr($member_ident='') {
       if(empty($member_ident)) return false;
       return kernel::single("b2c_cart_object_goods")->del_cookie($member_ident);
   }
   
   public function get_cart_object($arr_goods=array()) {
       if( $this->_back_cart_object ) return $this->_back_cart_object;
       if(empty($arr_goods) || !is_array($arr_goods)) return false;
       $return = kernel::single("b2c_cart_object_goods")->no_database(true, $arr_goods, md5(rand().microtime()));
       if( $return['cart_status']=='false' ) return $return;
       foreach(kernel::servicelist('b2c_cart_process_apps') as $object) {
            if(!is_object($object)) continue;
            $tmp[$object->get_order()] = $object;
        }
        krsort($tmp);
        
        foreach($tmp as $object){
            $object->process($aData,$aResult,$aConfig);
        }
       kernel::single("b2c_cart_object_goods")->no_database(false);
       $this->_back_cart_object = $aResult;
       return $aResult;
   }

   public function get_basic_objects(){
       $aResult = array();
       $o = kernel::single("base_session");
       $o->start();
       $arr =  $this->app->model('cart_objects')->getList('*',array('member_ident'=>$o->sess_id()));
       return $arr;
       
       /**
       foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
            if(!is_object($object)) continue;
            $aResult = array_merge($aResult,$object->getAll(false)); // 只从数据库中取出
       }
       print_r($aResult);exit;
       return $aResult;
       */
   }

   /**
    * 购物车项总数据统计
    *
    * @param array $aData // cart_objects sdf
    */
   public function count_objects(&$aData) {
       $aData['subtotal_consume_score'] = 0;
       $aData['subtotal_gain_score'] = 0;
       $aData['subtotal'] = 0;
       $aData['subtotal_price'] = 0;
       $aData['subtotal_discount'] = 0;
       $aData['items_quantity'] = 0;
       $aData['items_count'] = 0;
       $aData['subtotal_weight'] = 0;
       $aData['discount_amount_prefilter'] = 0;
       $aData['discount_amount_order'] = 0;
       $aData['discount_amount'] = 0;
       
       //商品促销之后
       $aData['subtotal_prefilter_after'] = 0;

       foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
            if(!is_object($object)) continue;
           $aResult = $object->count($aData);

           if(empty($aResult)) continue;
           
           
           //商品促销 优惠为负数时处理
           if( $aResult['discount_amount_prefilter']<0 ) {
               $aResult['subtotal'] = $aResult['subtotal'] + abs($aResult['discount_amount_prefilter']);
               $aResult['discount_amount_prefilter'] = 0;
           }
            
            
           if( $aData['discount_amount_order']<0 ) {
               $aResult['subtotal'] = $aResult['subtotal'] + abs($aResult['discount_amount_order']);
               $aResult['discount_amount_order'] = 0;
           }
            
            
           $aData['subtotal_consume_score'] += $aResult['subtotal_consume_score'];
           $aData['subtotal_gain_score'] += $aResult['subtotal_gain_score'];
           $aData['subtotal'] += $aResult['subtotal'];
           $aData['subtotal_price'] += $aResult['subtotal_price'];
           $aData['discount_amount'] += $aResult['discount_amount'];
           $aData['items_quantity'] += $aResult['items_quantity'];
           $aData['items_count'] += $aResult['items_count'];
           $aData['subtotal_weight'] += $aResult['subtotal_weight'];
           $aData['discount_amount_prefilter'] += $aResult['discount_amount_prefilter'];
           
           $aData['discount_amount_order'] += $aResult['discount_amount_order'];
           $aData['discount_amount'] = $aData['discount_amount_prefilter'] + $aData['discount_amount_order'];
           if( $aResult['error_html'] ) {
               $aData['cart_status'] = 'false';
               $aData['cart_error_html'] = $aResult['error_html'];
           }
           
           //区分item_quantity等 此变量用于挂件显示数量（：挂件显示购物车中总和，包含赠品。促销中不计赠品类）
           $aData['items_quantity_widgets'] = $aData['items_quantity'] + $aResult['items_quantity_widgets'];
           $aData['items_count_widgets'] = $aData['items_count'] + $aResult['items_count_widgets'];
       }
       
       //订单促销入参 订单总价处理
       $aData['subtotal_prefilter_after'] = $aData['subtotal'] - $aData['discount_amount_prefilter'];
       
       if( $aData['subtotal']<$aData['discount_amount'] )
           $aData['subtotal_discount'] = $aData['subtotal'];
       else 
           $aData['subtotal_discount'] = $aData['discount_amount'];

        if( $aData['cart_status']!=='false' ) {
            $sMinOrderAmount = app::get('b2c')->getConf('site.min_order_amount');
            if($sMinOrderAmount) {
                if($sMinOrderAmount > ($aData['subtotal']-$aData['discount_amount'])) {
                    $aData['cart_status'] = 'false';
                    $aData['cart_error_html'] = app::get('b2c')->_('订单未满起订金额！起订金额为：'). $sMinOrderAmount;
                }
            }
        }

		foreach($aData['object']['goods'] as $k=>$v){
			$total_prefilter_after[$v['store_id']][] = $v['subtotal_prefilter_after'];
			$total_price[$v['store_id']][] = $v['subtotal_price'];
			$total[$v['store_id']][] = $v['subtotal'];
		}

		foreach((array)$total as $k=>$v){
			$total[$k] = array_sum($v);
			$total_price[$k] = array_sum($total_price[$k]);
			$total_prefilter_after[$k] = array_sum($total_prefilter_after[$k]);
		}
		$aData['store_total_prefilter_after'] = $total_prefilter_after;
		$aData['store_total_price'] = $total_price;
		$aData['store_total'] = $total;
        
   }

   // 购物车物品项render
   public function get_item_render() {
       $aResult = array();
       foreach(kernel::servicelist('b2c_cart_render_items_apps') as $object) {;
           if(!is_object($object)) continue;
           $aResult[$object->index] = (array) $object;
       }
       krsort($aResult);
       return $aResult;
   }

   //团购购物车物品项render
   public function get_group_item_render() {
       $aResult = array();
       foreach(kernel::servicelist('group_cart_render_items_apps') as $object) {;
           if(!is_object($object)) continue;
           $aResult[$object->index] = (array) $object;
       }
       krsort($aResult);
       return $aResult;
   }

   //秒杀购物车物品项render
   public function get_spike_item_render() {
       $aResult = array();
       foreach(kernel::servicelist('spike_cart_render_items_apps') as $object) {;
           if(!is_object($object)) continue;
           $aResult[$object->index] = (array) $object;
       }
       krsort($aResult);
       return $aResult;
   }


   //积分换购购物车物品项render
   public function get_score_item_render() {
       $aResult = array();
       foreach(kernel::servicelist('scorebuy_cart_render_items_apps') as $object) {;
           if(!is_object($object)) continue;
           $aResult[$object->index] = (array) $object;
       }
       krsort($aResult);
       return $aResult;
   }
   
   
   // 购物车物品项render
   public function get_item_goods_render() {
       $aResult = array();
       foreach(kernel::servicelist('b2c_cart_render_items_goods_apps') as $object) {
           if(!is_object($object)) continue;
           $aResult[$object->index] = (array) $object;
       }
       krsort($aResult);

       return $aResult;
   }
   
   
   // 购物车物品项render
   public function get_item_render_view() {
       $aResult = array();
       foreach(kernel::servicelist('b2c_cart_render_items_apps') as $object) {;
           if(!is_object($object)) continue;
           if( method_exists($object,'_get_minicart_view') ) {
               $arr = $object->_get_minicart_view();
               $aResult[$arr['index']] = $arr;
               $aResult[$arr['index']]['app'] = $object->app;
           }
       }
       krsort($aResult);
       return $aResult;
   }
   
   
   // 购物车物品项render
   public function get_item_goods_render_view() {
       $aResult = array();
       foreach(kernel::servicelist('b2c_cart_render_items_goods_apps') as $object) {
           if(!is_object($object)) continue;
           if( method_exists($object,'_get_minicart_view') ) {
               $arr = $object->_get_minicart_view();
               $aResult[$arr['index']] = $arr;
               $aResult[$arr['index']]['app'] = $object->app;
           }
       }
       krsort($aResult);

       return $aResult;
   }
   
   

   // 优惠项render
   public function get_solution_render() {
       $aResult = array();
       foreach(kernel::servicelist('b2c_cart_render_solutions_apps') as $object) {

           $aResult[$object->index] = (array) $object;
       }
       
       arsort($aResult);
       return $aResult;
   }
   
   
   public function get_unuse_solution_cart( $aCart ) {
       return kernel::single('b2c_cart_postfilter_promotion')->get_unuse_rule( $aCart );
   }

   /**
    * 购物车是否为空
    *
    * @param array $aCart
    * @return boolean
    */
   public function is_empty($aCart) {
       if(!is_array($aCart)) return true;
       if(!isset($aCart['object'])) return true;
       if(empty($aCart['object'])) return true;
       $aKey = array_keys($aCart['object']);
       foreach($aKey as $key) {
           if(!empty($aCart['object'][$key])) return false;
       }
       return true;
   }
   
   
   
   public function unset_data() {
       foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
            if( !is_object($object) ) continue;
            if( method_exists($object,'unset_data') && is_callable(array($object,'unset_data')) ) {
                $object->unset_data();
            }
        }
   }
   
   //////////////////////////////////////////////////////////////////////////
    //接口 暂时团购使用
    ///////////////////////////////////////////////////////////////////////////
    private function delete_item( &$aCart ) {
        foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
            if( !is_object($object) ) continue;
            if( method_exists($object,'delete_item') ) {
                $object->delete_item( $aCart );
            }
        }
    }
    
    

}
