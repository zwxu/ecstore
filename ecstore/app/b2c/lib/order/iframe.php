<?php

 
class b2c_order_iframe extends base_controller{

    private $key_timeout = 3600; //key的超时时间，单位：秒
    
    /**
     * 构造方法
     * @params object app object
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->objMath = kernel::single('ectools_math');
    }


    private function check_secret_key( $key )
    {
        if( !$key )
            return false;

        $timestamp = strpos( $key, '.' );
        if( $timestamp === false )
            return false;
        $timestamp = substr( $key, $timestamp+1, 10 );

        if( (time() - $timestamp) > $this->key_timeout )
            return false;

        base_kvstore::instance('b2c.iframe')->fetch('iframe.whitelist', $whitelist);
        if( !$whitelist )
            return false;

        $index = array_search( $key, $whitelist );
        if( $index === false )
            return false;

        return true;

    }


    private function change_secret_key( $key, $newkey=NULL )
    {
        base_kvstore::instance('b2c.iframe')->fetch('iframe.whitelist', $whitelist);
        if( !$whitelist )
            return false;
        
        $index = array_search( $key, $whitelist );
        if( $index === false )
            return false;

        if( $newkey == NULL )
            unset( $whitelist[$index] );
        else
            $whitelist[$index] = $newkey;

        base_kvstore::instance('b2c.iframe')->store('iframe.whitelist', $whitelist);
        return true;
    }


    public function edit($params)
    {
        
        $secret_key = $params['secret_key'];
        if( !$this->check_secret_key($secret_key) ) {
            exit('server reject');
        }
        else {
            $new_secret_key = $secret_key . '.edited';
            $this->change_secret_key( $secret_key, $new_secret_key );
        }

        if( !( $orderid = $params['tid'] ) )
            exit('server reject');

        if( !( $notify_url = base64_decode(str_replace('%252F', '/', $params['notify_url'])) ) )
            exit('server reject');

        $objOrder = &$this->app->model('orders');
        $aOrder = $objOrder->dump($orderid,'*');

        $objCurrency = app::get('ectools')->model("currency");
        $aCur = $objCurrency->getSysCur();
    
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;                
        }
            
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $aORet = $objOrder->dump($orderid,'*',$subsdf);
        $order_items = array();
        foreach($aORet['order_objects'] as $k=>$v)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            if ($v['obj_type'] == 'goods')
            {
                foreach($v['order_items'] as $key => $item)
                {     
                    if (!$item['products'])
                    {
                        $o = $this->app->model('order_items');
                        $tmp = $o->getList('*', array('item_id'=>$item['item_id']));
                        $item['products']['product_id'] = $tmp[0]['product_id'];
                    }
                        
                    if ($item['item_type'] != 'gift')
                    {                        
                        $gItems[$k]['addon'] = unserialize($item['addon']);
                        if($item['minfo'] && unserialize($item['minfo'])){
                            $gItems[$k]['minfo'] = unserialize($item['minfo']);
                        }else{
                            $gItems[$k]['minfo'] = array();
                        }
                        
                        if ($item['item_type'] == 'product')
                        {
                            if ($arr_service_goods_type_obj['goods'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['goods'];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods, 'admin_order_edit');
                            }
                                
                            $order_items[$k] = $item;
                            $order_items[$k]['small_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['is_type'] = $v['obj_type'];
                            $order_items[$k]['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['link_url'] = $arrGoods['link_url'];
                            
                            $order_items[$k]['name'] = $item['name'];
                            if ($item['addon'])
                            {                                        
                                $item['addon'] = unserialize($item['addon']);
                                if ($item['addon']['product_attr'])
                                {
                                    $order_items[$k]['name'] .= '(';
                                    foreach ($item['addon']['product_attr'] as $arr_special_info)
                                    {
                                        $order_items[$k]['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                    }
                                    $order_items[$k]['name'] = substr($order_items[$k]['name'], 0, strpos($order_items[$k]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['name'] .= ')';
                                }
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj['adjunct'])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj['adjunct'];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods, 'admin_order_edit');
                            }
                                
                            $order_items[$k]['adjunct'][$index_adj] = $item;
                            $order_items[$k]['adjunct'][$index_adj]['small_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['adjunct'][$index_adj]['is_type'] = $v['obj_type'];
                            $order_items[$k]['adjunct'][$index_adj]['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['adjunct'][$index_adj]['link_url'] = $arrGoods['link_url'];
                            
                            $order_items[$k]['adjunct'][$index_adj]['name'] = $item['name'];
                            if ($item['addon'])
                            {                                        
                                $item['addon'] = unserialize($item['addon']);
                                if ($item['addon']['product_attr'])
                                {
                                    $order_items[$k]['adjunct'][$index_adj]['name'] .= '(';
                                    foreach ($item['addon']['product_attr'] as $arr_special_info)
                                    {
                                        $order_items[$k]['adjunct'][$index_adj]['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                    }
                                    $order_items[$k]['adjunct'][$index_adj]['name'] = substr($order_items[$k]['adjunct'][$index_adj]['name'], 0, strpos($order_items[$k]['adjunct'][$index_adj]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['adjunct'][$index_adj]['name'] .= ')';
                                }
                            }
                            
                            $index_adj++;
                        }
                    }
                    else
                    {
                        if ($arr_service_goods_type_obj['gift'])
                        {
                            $str_service_goods_type_obj = $arr_service_goods_type_obj['gift'];
                            $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods, 'admin_order_edit');
                                
                            $order_items[$k]['gifts'][$index_gift] = $item;
                            $order_items[$k]['gifts'][$index_gift]['small_pic'] = $arrGoods['image_default_id'];
                            $order_items[$k]['gifts'][$index_gift]['is_type'] = $v['obj_type'];
                            $order_items[$k]['gifts'][$index_gift]['item_type'] = $arrGoods['category']['cat_name'];
                            $order_items[$k]['gifts'][$index_gift]['link_url'] = $arrGoods['link_url'];
                            
                            $order_items[$k]['gifts'][$index_gift]['name'] = $item['name'];
                            if ($item['addon'])
                            {                                        
                                $item['addon'] = unserialize($item['addon']);
                                if ($item['addon']['product_attr'])
                                {
                                    $order_items[$k]['gifts'][$index_gift]['name'] .= '(';
                                    foreach ($item['addon']['product_attr'] as $arr_special_info)
                                    {
                                        $order_items[$k]['gifts'][$index_gift]['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                    }
                                    $order_items[$k]['gifts'][$index_gift]['name'] = substr($order_items[$k]['gifts'][$index_gift]['name'], 0, strpos($order_items[$k]['gifts'][$index_gift]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['gifts'][$index_gift]['name'] .= ')';
                                }
                            }
                                
                            $index_gift++;
                        }
                    }
                    //获取商品类型的库存是否设置为小数库存---anjiaxin--start
                    if($item['type_id']){
                        $type=app::get('b2c')->model('goods_type')->dump($item['type_id']);
                        $order_items[$k]['numtype'] = $type['floatstore'];
                    }
                    //----------end
                }
            }
            else
            {
                if ($v['obj_type']=='gift')
                {
                    $str_service_goods_type_obj = $arr_service_goods_type_obj['gift'];
                    foreach ($v['order_items'] as $gift_key => $gift_item)
                    {
                        if (!$gift_item['products'])
                        {
                            $o = $this->app->model('order_items');
                            $tmp = $o->getList('*', array('item_id'=>$gift_item['item_id']));
                            $gift_item['products']['product_id'] = $tmp[0]['product_id'];
                        }
                                
                        if (isset($gift_items[$gift_item['goods_id']]) && $gift_items[$gift_item['goods_id']])
                            $gift_items[$gift_item['goods_id']]['nums'] = $this->objMath->number_plus(array($gift_items[$gift_item['goods_id']]['nums'], $item['quantity']));
                        else
                        {                    
                            $str_service_goods_type_obj->get_order_object(array('goods_id' => $gift_item['goods_id'], 'product_id'=>$gift_item['products']['product_id']), $arrGoods, 'admin_order_edit');
                            
                            $gift_name = $gift_item['name'];
                            if ($gift_item['addon'])
                            {
                                $arr_addon = unserialize($gift_item['addon']);

                                if ($arr_addon['product_attr'])
                                {
                                    $gift_name .= '(';

                                    foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                    {
                                        $gift_name .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }

                                    if (strpos($gift_name, $this->app->_(" ")) !== false)
                                    {
                                        $gift_name = substr($gift_name, 0, strrpos($gift_name, $this->app->_(" ")));
                                    }

                                    $gift_name .= ')';
                                }
                            }
                                    
                            $gift_items[$gift_item['products']['product_id']] = array(
                                'goods_id' => $gift_item['goods_id'],
                                'product_id' => $gift_item['products']['product_id'],
                                'bn' => $gift_item['bn'],
                                'nums' => $gift_item['quantity'],
                                'name' => $gift_name,
                                'item_type' => $arrGoods['category']['cat_name'],
                                'price' => $gift_item['price'],
                                'quantity' => $gift_item['quantity'],
                                'sendnum' => $gift_item['sendnum'],
                                'small_pic' => $arrGoods['image_default_id'],
                                'is_type' => $v['obj_type'],
                                'link_url' => $arrGoods['link_url'],
                                'item_id' => $gift_item['item_id'],
                                );
                        }
                    }
                }
                else
                {
                    // 赠品以外的其他区块的解析.
                    if ($arr_service_goods_type_obj[$v['obj_type']])
                    {
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$v['obj_type']];
                        $extends_items[] = $str_service_goods_type_obj->get_order_object($v, $arrGoods, 'admin_order_edit');
                    }
                }
            }
        }
        $aOrder['items'] = $order_items;
        $aOrder['gifts'] = $gift_items;
        $aOrder['extends_items'] = $extends_items;
        
        if ($aOrder['member_id'] > 0)
        {
            $objMember = &$this->app->model('members');
            $aOrder['member'] = $objMember->dump($aOrder['member_id'], '*',array( ':account@pam'=>array('*')));
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }
        else
        {
            $aOrder['member'] = array();
        }
        
        $objDelivery = &$this->app->model('dlytype');
        $aArea = app::get('ectools')->model('regions')->getList('*',null,0,-1);
        foreach ($aArea as $v)
        {
            $aTmp[$v['name']] = $v['name'];
        }
        $aOrder['deliveryArea'] = $aTmp;

        $aRet = $objDelivery->getList('*',null,0,-1);
        foreach ($aRet as $v)
        {
            $aShipping[$v['dt_id']] = $v['dt_name'];
        }
        $aOrder['selectDelivery'] = $aShipping;

        $objPayment = app::get('ectools')->model('payment_cfgs');
        
        $aRet = $objPayment->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));
        if (!$aORet['member_id'])
        {
            if ($aRet)
            {
                foreach ($aRet as $key=>$arr_payments)
                {
                    if (trim($arr_payments['app_id']) == 'deposit')
                    {
                        unset($aRet[$key]);
                    }
                }
            }
        }
        $aPayment[-1] = app::get('b2c')->_('货到付款');
        foreach ($aRet as $v)
        {
            $aPayment[$v['app_id']] = $v['app_name'];
        }

        $aOrder['selectPayment'] = $aPayment;

        $objCurrency = app::get('ectools')->model("currency");
        $aRet = $objCurrency->curAll();
        foreach ($aRet as $v)
        {
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        
        $site_trigger_tax = $this->app->getConf('site.trigger_tax');
        $this->pagedata['site_trigger_tax'] = $site_trigger_tax;
        
        $aOrder['curList'] = $aCurrency;
        $aOrder['cur_name'] = $aCurrency[$aOrder['currency']];
        
        $this->pagedata['order'] = $aOrder;
        $this->pagedata['finder_id'] = $_GET['finder_id'];

        $this->pagedata['base_url'] = kernel::base_url(1).kernel::url_prefix().'/openapi/b2c.iframe.order.edit';
        $this->pagedata['res_url'] = app::get('desktop')->res_url;

        $this->pagedata['secret_key'] = $new_secret_key;
        $this->pagedata['notify_url'] = $notify_url;


        $this->page('admin/order/order_edit_iframe.html');

    }



    public function save($params)
    {
        $secret_key = $_POST['secret_key'];
        //校验key
        if( !$this->check_secret_key($secret_key) )
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"server reject",_:null}';exit;
        }


        /** 检查订单是否可以被操作 **/
        $obj_order_check = kernel::single('b2c_order_checkorder');
        if (!$obj_order_check->checkfor_order_update($_POST, $msg))
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.$msg.'",_:null}';exit;
        }

        //事务开始
        $db = kernel::database();
        $db->beginTransaction();
            
        $arr_data = $this->_process_fields($_POST);
        $obj_order = $this->app->model('orders');
        $result = $obj_order->save($arr_data);
        
        if (count($_POST['aItems']))
        {
            if ($result)
            {
                $obj_order_update = kernel::single('b2c_order_update');
                
                $_p = $_POST;
                $result = $obj_order_update->generate($_p, true, $msg);

                if( $result )
                {
                    $db->commit();

                    $order_id = $arr_data['order_id'];

                    $order_sdf = $obj_order->dump($order_id, '*');
                    //变更订单支付状态
                    if( $order_sdf['payed'] > 0 ) {
                        $arr_data['order_id'] = $order_id;
                        if( $order_sdf['total_amount'] > $order_sdf['payed'] && $order_sdf['pay_status'] == '1' ) {
                            $arr_data['pay_status'] = '3';
                        }
                    
                        if( $order_sdf['total_amount'] <= $order_sdf['payed'] && $order_sdf['pay_status'] == '3' ) {
                            $arr_data['pay_status'] = '1';
                        }
                        $obj_order->save($arr_data);                        
                    }

                    //发送订单变更信息到OCS
                    $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                    $apiv_params = array(
                        'order_id' => $order_id,
                        );
                    $req_result = $obj_apiv->rpc_caller_request($apiv_params, 'iframeedit');
                    if( !$req_result )
                    {
                        $db->rollback();
                        header('Content-Type:text/jcmd; charset=utf-8');
                        echo '{error:"'.app::get('b2c')->_("订单编辑信息同步异常，请进行同步重试！！！").'",synced:"fail",_:null}';exit;
                    }
 
                    $this->change_secret_key( $secret_key, NULL );
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{success:"'.app::get('b2c')->_("成功.").'",_:null,synced:"success",order_id:"'.$order_id.'"}';
                }
                else
                {
                    $db->rollback();
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';exit;
                }
            }
            else
            {
                $db->rollback();
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_("保存失败.").'",_:null}';exit;
            }
        }
        else
        {
            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $arr_orders = $obj_order->dump($_POST['order_id'], '*', $subsdf);
            if (count($arr_orders['order_objects']) == 0)
            {
                $db->rollback();
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('订单详细不存在，请确认！').'",_:null}';exit;
            }
            else
            {
                $db->commit();
                $this->change_secret_key( $secret_key, NULL );
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_("成功.").'",_:null,order_id:"'.$_POST['order_id'].'"}';
            }
        }
    }


    //同步数据到ocs
    public function do_sync()
    {
        $order_id = $_POST['order_id'];
        $secret_key = $_POST['secret_key'];
        if( !$order_id )
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('订单号不能为空！').'",_:null}';exit;
        }
        //校验key
        if( !$this->check_secret_key($secret_key) )
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"server reject",_:null}';exit;
        }

        //发送订单变更信息到OCS
        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
        $apiv_params = array(
            'order_id' => $order_id,
            );
        $req_result = $obj_apiv->rpc_caller_request($apiv_params, 'iframeedit');
        if( $req_result )
        {
            $this->change_secret_key( $secret_key, NULL );
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('b2c')->_("同步成功.").'",_:null,synced:"success",order_id:"'.$order_id.'"}';            
        }
        else
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("订单编辑信息同步异常，请进行同步重试！！！").'",synced:"fail",_:null}';exit;
        }
 
    }


    /**
     * 规整sdf数据
     * @params null
     * @return array 格式数据
     */
    private function _process_fields($sdf)
    {
        $sdf['is_protect'] = isset($sdf['is_protect']) ? $sdf['is_protect'] : 'false';
        $sdf['cost_protect'] = isset($sdf['cost_protect']) ? $sdf['cost_protect'] : '0.00';
        $sdf['is_tax'] = isset($sdf['is_tax']) ? $sdf['is_tax'] : 'false';
        $sdf['order_id'] = $sdf['order_id'];


        $sdf['cost_tax'] = trim($sdf['cost_tax']) ? trim($sdf['cost_tax']) : 0;
        unset($sdf['discount']);
        $sdf['is_protect'] = $sdf['is_protect'];
        $sdf['is_tax'] = $sdf['is_tax'];

        $sdf['pmt_order'] = $sdf['pmt_order'];

        $shipping = &$this->app->model('dlytype');
        $aShip = $shipping->dump($sdf['shipping_id']);
        
        $sdf['shipping'] = array(
            'shipping_id'=>$sdf['shipping_id'],    
            'shipping_name'=>$aShip['dt_name'],    
            'cost_shipping'=>$sdf['cost_freight'],    
            'is_protect'=>$sdf['is_protect'],    
            'cost_protect'=>$sdf['cost_protect'],    
            );



        $sdf['payinfo'] = array(
            'cost_payment'=>$sdf['cost_payment'],
            'pay_app_id' => $sdf['payment']
            );

        $sdf['consignee'] = array(
            'name'=>$sdf['receiver_name'],  
            'addr'=>$sdf['ship_addr'],
            'zip'=>$sdf['ship_zip'],
            'telephone'=>$sdf['ship_tel'],
            'r_time'=>$sdf['ship_time'],
            'mobile'=>$sdf['ship_mobile'],
            'email'=>$sdf['ship_email'],
            'area'=>$sdf['ship_area']
            );

        $sdf['tax_title'] = $sdf['tax_company'];
        $sdf['weight'] = $sdf['weight'];
        $sdf['last_modified'] = time();
        
        return $sdf;
    }




    /**
     * 添加货品项目
     * @param null
     * @return string 生成后的html.
     */
    public function addItem()
    {
        if($_POST['order_id']){
            $flag = true;
            while($flag){
                $randomValue = rand(1,200);
                if(!in_array($randomValue, (array)$_POST['aItems'])){
                    $flag = false;
                }
            }
            $loopValue = count($_POST['aItems']) + 1;
            $objOrder = &$this->app->model('orders');
            $productInfo = $objOrder->getProductInfo($_POST['order_id'], $_POST['newbn']);
            if (isset($productInfo['spec_info']) && $productInfo['spec_info'])
            {
                $productInfo['name'] = $productInfo['name'] . '(' . $productInfo['spec_info'] . ')';
            }

            if($productInfo == 'none'){
                $aOrder['alertJs'] = app::get('b2c')->_("商品货号输入不正确，没有该商品或者商品已经下架。\n注意：如果是多规格商品，请输入规格编号.");
            }elseif($productInfo == 'exist'){
                $aOrder['alertJs'] = app::get('b2c')->_('订单中存在相同的商品货号。');
            }
            elseif($productInfo == 'understock'){
                $aOrder['alertJs'] = app::get('b2c')->_('商品库存不足。');
            }
            if(in_array($_POST['newbn'],(array)$_POST['add_bn'])){
                $aOrder['alertJs'] = app::get('b2c')->_('该商品货号已存在。');
            }
            if($aOrder['alertJs']){
                echo $aOrder['alertJs'];
                exit;
            }
            $returnValue = '<tr>';
            $returnValue .= '<input type="hidden" value="'.$productInfo['product_id'].'" name="aItems[product_id]['.$productInfo['product_id'].'_0]">';
            $returnValue .= '<input type="hidden" value="0" name="aItems[object_id]['.$productInfo['product_id'].'_0]">';
            $returnValue .= '<td>'.$productInfo['bn'].'<input type="hidden" name="add_bn[]" value="'.$productInfo['bn'].'"></td>';
            $returnValue .= '<td>'.$productInfo['name'].'</td>';
            $returnValue .= '<td><input type="text" vtype="unsigned" size="8" value="'.$productInfo['mprice'].'" name="aPrice['.$productInfo['product_id'].'_0]" class="x-input itemPrice_'.$productInfo['product_id'] . '-0 itemrow" required="true" autocomplete="off"></td>';
            $returnValue .= '<td><input type="text" vtype="positive" size="4" value="1" name="aNum['.$productInfo['product_id'].'_0]" class="x-input itemNum_'.$productInfo['product_id'].'-0 itemrow" required="true" autocomplete="off"></td>';
            $returnValue .= '<td class="itemSub_'.$productInfo['product_id'] . '-0 itemCount Colamount">'.$productInfo['mprice'].'</td>';
            $returnValue .= '<td><img class="imgbundle" app="desktop" onclick="delgoods(this)" style="cursor: pointer;" title="删除" src="' . kernel::base_url() . '/app/desktop/statics/bundle/delecate.gif"></td>';
            $returnValue .= '</tr>';
            echo $returnValue;
        }
    }


    /**
     * 计算订单交互数据
     * @param null
     * @return null
     */
    public function caculate_item_total()
    {
        if ($_POST)
        {
            if ($_POST['json_arr'] && $_POST['operaction'])
            {
                $arr_org_obj = json_decode($_POST['json_arr']);
                $arr_org = array();
                foreach ($arr_org_obj as $str_obj)
                {
                    $arr_org[] = strval($str_obj);
                }
                
                $result = "";
                switch (trim($_POST['operaction']))
                {
                case 'plus':
                    $result = $this->objMath->number_plus($arr_org);
                    break;
                case 'minus':
                    $result = $this->objMath->number_minus($arr_org);
                    break;
                case 'multiple':
                    $result = $this->objMath->number_multiple($arr_org);
                    break;
                case 'div':
                    $result = $this->objMath->number_div($arr_org);
                    break;
                default:
                    break;
                }                
                
                echo $result;exit;
            }
        }
    }


}