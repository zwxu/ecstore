<?php 
class package_order_service_package implements b2c_order_service_interface
{
    
    function __construct(&$app)
    {
        $this->app = $app;
        $this->oGoods = kernel::single("b2c_order_service_goods");
    }
    
    public function get_goods_type()
    {
        return 'package';
    }
        
    /*
     * return sdf order
     */
    function gen_order( $arrObjInfo=array(), &$order_data, &$msg='' )
    {
        $store_mark = app::get('b2c')->getConf('system.goods.freez.time');
        $order_type = $this->get_goods_type();
        $o = kernel::single('ectools_math');
        if( !$arrObjInfo || !is_array($arrObjInfo) ) return false;
        $is_freez = true;
        foreach( $arrObjInfo as $row ) {
            $package = $row['package'];
            $arrParams = array(
                'goods_id' => $package['id'],
                'quantity' => $row['quantity'],
                'info' => array(),
            );//用于冻结库存
            
            
            $tmp = array();
            $package = $row['package'];
            $tmp = array(
                'order_id' => $order_data['order_id'],
                'obj_type' => $order_type,
                'obj_alias' => '捆绑商品区块',
                'goods_id' => $package['id'],
                'bn' => '',
                'name' => $package['name'],
                'price' => $package['price'],
                'quantity' => $row['quantity'],
                'amount' => $package['price'],
                'weight' => $package['weight'],
                'score' => $package['score'],
            );
            $order_items = array();
            foreach ($row['obj_items'] as $key => $obj_items) {
                $order_items[] = array(
                    'products' => array('product_id'=>$obj_items['product_id']),
                    'goods_id' => $obj_items['goods_id'],
                    'order_id' => $order_data['order_id'],
                    'item_type' => 'product',
                    'bn' => $obj_items['bn'],
                    'name' => $obj_items['name'],
                    'type_id' => $obj_items['type_id'],
                    'cost' => $obj_items['price']['cost'],
                    'quantity' => $row['quantity'],
                    'sendnum' => 0,
                    'amount' => $o->number_multiple( array($obj_items['price']['buy_price'],$row['quantity']) ),
                    'score' => 0,
                    'price' => $obj_items['price']['buy_price'],
                    'g_price'=>$obj_items['price']['buy_price'],
                    'weight'=>$obj_items['weight'],
                    'addon' => '',
                );
                $arrParams['info'][] = array(
                    'goods_id' => $obj_items['goods_id'],
                    'product_id'=>$obj_items['product_id'],
                );
            }
            $tmp['order_items'] = $order_items;
            $tmp['bn'] = $order_items[0]['bn'];
            $order_data['order_objects'][] = $tmp;
            if ($store_mark == '1')
            {
                $is_freez = $this->freezeGoods( $arrParams );
                if (!$is_freez)
                {
                    $msg = app::get('b2c')->_('库存冻结失败！');
                    return false;
                }
            }
        }
        return true;
    }
    #End Func
    
    
    public function freezeGoods($arrParams=array())
    {
        if (!$arrParams) return false;
        $is_freeze = false;
        $o = $this->app->model('attendactivity');
        if ( isset($arrParams['goods_id']) && $arrParams['goods_id'] )
            $is_freeze = $o->freez($arrParams['goods_id'], $arrParams['quantity']);

        $is_freeze_b2c = $this->doFunction( 'freezeGoods',$arrParams );
        return $is_freeze&&$is_freeze_b2c;
    }
    
    
    /**
     * @params $order_objects array() 订单对象数据结构
     **/
    public function unfreezeGoods($order_objects=array())
    {
        if (!$order_objects) return false;
        $arrParams['id'] = $order_objects['goods_id'];
        $arrParams['quantity'] = $order_objects['quantity'];
        $arrParams['info'] = array();
        
        if( !is_array($order_objects['order_items']) ) return false;
        foreach( $order_objects['order_items'] as $row ) {
            $arrParams['info'][] = array(
                'goods_id' => $row['goods_id'],
                'product_id' => $row['products']['product_id'],
            );
        }
        
        $is_unfreeze = false;
        $o = $this->app->model('attendactivity');
        if ( isset($arrParams['id']) && $arrParams['id'] )
            $is_unfreeze = $o->unfreez($arrParams['id'], $arrParams['quantity']);
        
        #$this->doFunction( 'unfreezeGoods',$arrParams );
        return $is_unfreeze;
    }
    
    //////////////////////////////////////////////////////////////////////////
    // 修改数量
    ///////////////////////////////////////////////////////////////////////////
    public function minus_store($order_objects=array())
    {
        if (!$order_objects) return false;
        $arrParams['id'] = $order_objects['goods_id'];
        $arrParams['quantity'] = $order_objects['quantity'];
        $arrParams['info'] = array();
        
        if( !is_array($order_objects['order_items']) ) return false;
        foreach( $order_objects['order_items'] as $row ) {
            $arrParams['info'][] = array(
                'goods_id' => $row['goods_id'],
                'product_id' => $row['products']['product_id'],
            );
        }
        
        $o = $this->app->model('attendactivity');
        $arr = $o->dump( $arrParams['id'] );  //捆绑商品数据
        $objMath = kernel::single('ectools_math');
        $update_data = array(
            'store' => $arr['store']-$arrParams['quantity'],
        );
        
        $o->update($update_data, array('id'=>$arrParams['id']));
        
        #$this->doFunction( 'minus_store',$arrParams );
        
    }
    
    public function recover_store($order_objects=array())
    {
        if (!$order_objects) return false;
        $arrParams['id'] = $order_objects['goods_id'];
        $arrParams['quantity'] = $order_objects['quantity'];
        $arrParams['info'] = array();
        
        if( !is_array($order_objects['order_items']) ) return false;
        foreach( $order_objects['order_items'] as $row ) {
            $arrParams['info'][] = array(
                'goods_id' => $row['goods_id'],
                'product_id' => $row['products']['product_id'],
            );
        }
        
        $o = $this->app->model('attendactivity');
        
        $objMath = kernel::single('ectools_math');
        
        $arr = $o->dump( $arrParams['id'] ); //捆绑商品数据
        
        $update_data = array(
            'store' => $arr['store']+$arrParams['quantity'],
        );
        
        $o->update($update_data, array('id'=>$arrParams['id']));
        
        #$this->doFunction( 'recover_store',$arrParams );
    }
    
    public function get_order_object($arr_object=array(), &$order_items, $tml='member_order_detail')
    {
        $order_items = array();
        $objMath = kernel::single("ectools_math");
        foreach($arr_object['order_items'] as $k => $item)
        {  
            if($item['addon'] && unserialize($item['addon'])){
                $gItems[$k]['minfo'] = unserialize($item['addon']);
            }else{
                $gItems[$k]['minfo'] = array();
            }
            
            if ($item['item_type'] == 'product')
            {  
                kernel::single("b2c_order_service_goods")->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods);
                #$order_items[$k] = $item;
                $order_items[$k]['obj_id'] = $item['obj_id'];
                $order_items[$k]['obj_title'] = '捆绑销售('.$arr_object['name'].')包含的商品';
                $order_items[$k]['goods_id'] = $item['goods_id'];
                $order_items[$k]['product_id'] = $item['products']['product_id'];
                $order_items[$k]['bn'] = $item['bn'];
                $order_items[$k]['category']['cat_name'] = $arr_object['obj_type'];
                $order_items[$k]['price'] = $item['price'];
                $order_items[$k]['quantity'] = $item['quantity'];
                $order_items[$k]['sendnum'] = $item['sendnum'];
                $order_items[$k]['small_pic'] = $arrGoods['image_default_id'];
                $order_items[$k]['nums'] = $item['quantity'];
                $order_items[$k]['obj_type'] = $this->get_goods_type();
                $order_items[$k]['total_amount'] = $objMath->number_multiple(array($item['price'], $item['quantity']));
                $order_items[$k]['link'] = $arrGoods['link_url'];
                $order_items[$k]['item_id'] = $item['item_id'];
                if (isset($item['products']['spec_info']) && $item['products']['spec_info'])
                {
                    $order_items[$k]['name'] = $item['products']['name'] . '(' . $item['products']['spec_info'] . ')';
                }
                else
                {
                    $order_items[$k]['name'] = $item['products']['name'];
                }
                // 判断是否有goods_type
                if (isset($arrGoods['type']['floatstore']) && $arrGoods['type']['floatstore'])
                {
                    $order_items[$k]['floatstore'] = 1;
                }
                else
                {
                    $order_items[$k]['floatstore'] = 0;
                }
            }
            
        }
        $render = $this->app->render();
        
        $arr_object['total_amount'] = $objMath->number_multiple(array($arr_object['price'], $arr_object['quantity']));
        $arr_object['order_items'] = $order_items;
        
        $render->pagedata['giftpackage_order'] = $arr_object;
        $arr_giftpackage = $this->app->model('attendactivity')->getList( '*',array('id'=>$arr_object['goods_id']) );
        $arr_order = app::get('b2c')->model('orders')->getList( '*',array('order_id'=>$arr_object['order_id']) );
        $this->pagedata['orderdata'] = $arr_order[0];

        foreach($arr_giftpackage as $k=>$v) {
            if($v['id'] == $render->pagedata['giftpackage_order']['goods_id'])
                $render->pagedata['giftpackage_order']['thumb'] = $v['image'];
        }

        //默认图片
        $imageDefault = app::get('image')->getConf('image.set');
        $render->pagedata['default_image'] = $imageDefault['S']['default_image'];

        $render->pagedata['res_url'] = app::get('b2c')->res_url;
        if( strpos($tml,'admin')!==false ) {
            $tpl = 'admin/order/'.$tml.'.html';
        } else {
            $tpl = 'site/order/'.$tml.'.html';
        }
        
        return $render->fetch( $tpl );
    }
    
    public function get_default_dly_order_info($val_list=array(), &$data)
    {
        $objMath = kernel::single("ectools_math");
        $order_item = app::get('b2c')->model('order_items');
        foreach($val_list['order_items'] as $k => $item)
        { 
            if ($item['item_type'] == 'product')
            {  
                if (!$item['products'])
                {                                
                    $tmp = $order_item->getList('*', array('item_id'=>$item['item_id']));
                    $item['products']['bn'] = $tmp[0]['bn'];
                    $item['products']['spec_info'] = $tmp[0]['bn'];
                }
                
                //订单商品名称
                $data['order_name'] .= app::get('b2c')->_('名称：%s',$item['name']);
                //订单商品名称+数量                            
                $data['order_name_a'] .= app::get('b2c')->_('名称：%s&nbsp;&nbsp;数量：%s',$item['name'],$item['quantity'])."\n";
                //订单商品名称+规格+数量
                $data['order_name_as'].= app::get('b2c')->_('名称：%s&nbsp;&nbsp;规格：%s&nbsp;&nbsp;数量：%s',$item['name'],$item['products']['spec_info'],$item['quantity'])."\n";
                //订单商品名称+货号+数量
                $data['order_name_ab'].= app::get('b2c')->_("名称：%s&nbsp;&nbsp;货号：%s&nbsp;&nbsp;数量：%s",$item['name'],$item['products']['bn'],$item['quantity'])."\n";
            }
        }        
    }
    
    /*
     * 处理 延伸到b2c商品
     */
    private function doFunction( $func,$arr )
    {
        if( is_array($arr['info']) ) {
            foreach( $arr['info'] as $row ) {
                
                $arrParams = array(
                                    'goods_id'=>$row['goods_id'],
                                    'product_id'=>$row['product_id'],
                                    'quantity'=>$arr['quantity'],
                                );
                //b2c库存处理
                if( method_exists($this->oGoods,$func) ){
                    if(!$this->oGoods->$func( $arrParams )){
                        return false;
                    }
                }
                #else 
                #    die('函数没有!');
            }
        }
        return true;
    }
    #End Func
    
    /* 
     * @$nonGoods_extends['delivery_finish']  全部发完
     * @$nonGoods_extends['delivery_start']   未发过
     * @$nonGoods_extends['delivery_process'] 发过但未发完
     */
    public function store_change( $sdf,$type,$nonGoods_extends ) {
        if( $sdf['goods_id'] ) {
            
            $arr = kernel::single('b2c_order_checkorder')->checkOrderFreez( $type,$sdf['order_id'] );
            if( $nonGoods_extends['delivery_finish'] ) {
                foreach( $arr as $key => $val ) {
                    if( !$val ) continue;
                    switch($key) {
                        case "freez":
                            $this->freezeGoods( $sdf );break;
                        case "unfreez":
                            $this->unfreezeGoods( $sdf );break;
                        case "store":
                            $this->minus_store( $sdf );break;
                        case "unstore":
                            #$this->recover_store( $sdf );break;
                    }
                }
            }
        }
    }
    
    public function check_freez($arrParams)
    {
        if (!$arrParams) return false;
        $is_freeze = false;
        $o = $this->app->model('attendactivity');
        if ( isset($arrParams['goods_id']) && $arrParams['goods_id'] )
            $is_freeze = $o->check_freez($arrParams['goods_id'], $arrParams['quantity']);
        
        $is_freeze_b2c = $this->doFunction( 'check_freez',$arrParams );
        return $is_freeze&&$is_freeze_b2c;
    }
    
    public function get_aftersales_order_info($arr_data)
    {
        $order_items = array();
        if (!$arr_data)
            return $order_items;
            
        $objMath = kernel::single("ectools_math");
        foreach($arr_data['order_items'] as $k => $item)
        {  
            if($item['addon'] && unserialize($item['addon'])){
                $gItems[$k]['minfo'] = unserialize($item['addon']);
            }else{
                $gItems[$k]['minfo'] = array();
            }
            
            if ($item['item_type'] == 'product')
            {  
                kernel::single("b2c_order_service_goods")->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods);
                #$order_items[$k] = $item;
                $order_items[$k]['obj_title'] = '捆绑销售('.$arr_object['name'].')包含的商品';
                $order_items[$k]['goods_id'] = $item['goods_id'];
                $order_items[$k]['product_id'] = $item['products']['product_id'];
                $order_items[$k]['bn'] = $item['bn'];
                $order_items[$k]['category']['cat_name'] = $arr_object['obj_type'];
                $order_items[$k]['price'] = $item['price'];
                $order_items[$k]['quantity'] = $item['quantity'];
                $order_items[$k]['sendnum'] = $item['sendnum'];
                $order_items[$k]['small_pic'] = $arrGoods['image_default_id'];
                $order_items[$k]['nums'] = $item['quantity'];
                $order_items[$k]['obj_type'] = $this->get_goods_type();
                $order_items[$k]['total_amount'] = $objMath->number_multiple(array($item['price'], $item['quantity']));
                $order_items[$k]['link'] = $arrGoods['link_url'];
                $order_items[$k]['item_id'] = $item['item_id'];
                
                if ($item['addon'])
                {
                    $arrAddon = $arr_addon = unserialize($item['addon']);
                    if ($arr_addon['product_attr'])
                        unset($arr_addon['product_attr']);
                    $order_items[$k]['product']['minfo'] = $arr_addon;
                }
                
                if ($arrAddon['product_attr'])
                {
                    foreach ($arrAddon['product_attr'] as $arr_product_attr)
                    {
                        $order_items[$k]['product']['attr'] .= $arr_product_attr['label'] . app::get('b2c')->_(":") . $arr_product_attr['value'] . app::get('b2c')->_(" ");
                    }
                }
                
                if ($order_items[$k]['product']['attr'])
                    $order_items[$k]['name'] = $item['name'] . '(' . $order_items[$k]['product']['attr'] . ')';
                else
                    $order_items[$k]['name'] = $item['name'];
            }
        }
        
        return $order_items;
    }
        
    public function is_decomposition($goods_type)
    {
        return false;
    }
    
    public function is_item_edit($item_type)
    {
        return false;
    }
}