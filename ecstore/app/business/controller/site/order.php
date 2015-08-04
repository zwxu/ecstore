<?php


class business_ctl_site_order extends business_ctl_site_member{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct($app);
        $this->cur_view = 'order';
        $shopname = $app->getConf('system.shopname');
        if(isset($sysconfig)){
            $this->title = app::get('b2c')->_('订单').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('订单_').'_'.$shopname;
            $this->description = app::get('b2c')->_('订单_').'_'.$shopname;
        }
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->title=app::get('b2c')->_('订单中心');
        $this->objMath = kernel::single("ectools_math");
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
    }

    /**
     * 订单取消
     * @params string order id
     * @return null
     */
    public function docancel()
    {
        /*$form = $_POST['from']?$_POST['from']:'seller';
        if($from == 'seller'){
            $this->begin(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order'));
        }else{
            $this->begin(array('app' =>'b2c','ctl'=>'site_member','act' =>'orders'));
        }*/
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($_POST['order_id'],'',$message))
        {
           echo json_encode($message);
           exit;
        }
        
        $sdf['order_id'] = $_POST['order_id'];
        $sdf['op_id'] = $this->app->member_id;
        //获取用户名
        $obj_account = app::get('pam')->model('account');
        $login_name = $obj_account->dump($this->app->member_id,'login_name');
        $sdf['opname'] = $login_name['login_name'];
        
        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $this, $message))
        {
            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$_POST['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

            
            $order_id = $_POST['order_id'];
            $orderObj = app::get('b2c')->model('orders');
            $orderItemObj = app::get('b2c')->model('order_items');
            $order_info = $orderObj->dump(array('order_id'=>$order_id),'act_id,order_type,itemnum');
            switch($order_info['order_type']){
                case 'group':
                    $buyMod = app::get('groupbuy')->model('memberbuy');
                    $applyObj = app::get('groupbuy')->model('groupapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
                case 'spike':
                    $buyMod = app::get('spike')->model('memberbuy');
                    $applyObj = app::get('spike')->model('spikeapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
                case 'score':
                    $buyMod = app::get('scorebuy')->model('memberbuy');
                    $applyObj = app::get('scorebuy')->model('scoreapply');
                    $apply = $applyObj->dump(array('id'=>$order_info['act_id']),'aid,gid,remainnums,nums');
                    if($apply){
                      $buyMod->update(array('effective'=>'false'),array('order_id'=>$order_id));
                    }
                    break;
                case 'timedbuy':
                    $buyMod = app::get('timedbuy')->model('memberbuy');
                    $businessMod = app::get('timedbuy')->model('businessactivity');
                    $buys = $buyMod->getList('*',array('order_id'=>$order_id));
                    if($buys){
                      $business = $businessMod->getList('*',array('gid'=>$buys[0]['gid'],'aid'=>$buys[0]['aid']));
                      $buyMod->update(array('disable'=>'true'),array('order_id'=>$order_id));
                      if($business[0]['nums']){
                          $arr['remainnums'] = intval($business[0]['remainnums'])+intval($buys[0]['nums']);
                          $businessMod->update($arr,array('id'=>$business[0]['id']));
                      }
                    }
                    break;
            }
            
            //end

            echo json_encode('订单取消成功！');
        }
        else
        {
            echo json_encode('订单取消失败！');
        }
    }

    public function godelivery($order_id,$from){
        if (!$order_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("订单号传递出错.").'",_:null}';exit;
        }
        $this->pagedata['orderid'] = $order_id;
        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $aORet = $objOrder->dump($order_id,'*',$subsdf);
        $order_items = array();

        foreach ($aORet['order_objects'] as $k=>$v)
        {
            $order_items = array_merge($order_items,$v['order_items']);
        }
        $this->pagedata['items'] = $order_items;
        $shippings = $this->app->model('dlytype');
        $this->pagedata['shippings'] = $shippings->getList('*');
        //帅选快递
        $obj_corp = app::get('business')->model('dlycorp');
        $sto= kernel::single("business_memberstore",$this->app->member_id);        
        $filter["store_id"] = $sto->storeinfo['store_id'];
        $corp_id = $obj_corp->getList('corp_id',$filter);
        foreach($corp_id as $v){
            $cids[] = $v['corp_id'];
        }
        $c_filter['corp_id|in'] = $cids;

        $dlycorp = $this->app->model('dlycorp');
        $this->pagedata['corplist'] = $dlycorp->getList('*',$c_filter);
        $this->pagedata['order'] = $aORet;
        $this->pagedata['order']['needAddress'] = true;
        $this->pagedata['order']['needDelivery'] = true;
        if ($aORet['order_kind'] == '3rdparty') {
            foreach(kernel::servicelist('3rdparty_goods_processor') as $processor) {
                if (($processor->goodsKindDetail() == $aORet['order_kind_detail']) && $processor->isCustom('order_delivery')) {
                    $this->pagedata['order']['needAddress'] = $processor->isNeedAddress();
                    $this->pagedata['order']['needDelivery'] = $processor->isNeedDelivery();
                    break;
                }
            }
        }
        $this->pagedata['order']['protectArr'] = array('false'=>app::get('b2c')->_('否'), 'true'=>app::get('b2c')->_('是'));
        
        // 获得minfo
        $arrItems = array();
        $gift_items = array();
        $extends_items = array();
        if ($this->pagedata['order']['order_objects'])
        {    
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }
            
            foreach ($this->pagedata['order']['order_objects'] as $arrOdrObjects)
            {
                if ($arrOdrObjects['obj_type'] == 'goods')
                {
                    // 商品区块的解析。
                    $index_gift = 0;
                    foreach ($arrOdrObjects['order_items'] as $arrOdrItems)
                    {
                        if (!$arrOdrItems['products'])
                        {
                            $o = $this->app->model('order_items');
                            $tmp = $o->getList('*', array('item_id'=>$arrOdrItems['item_id']));
                            $arrOdrItems['products']['product_id'] = $tmp[0]['product_id'];
                        }
                        
                        if ($arrOdrItems['item_type'] != 'gift')
                        {
                            // 商品，配件的解析
                            if ($arrOdrItems['item_type'] == 'product')
                            {                            
                                $good_id = $arrOdrItems['products']['goods_id'];
                                $product_id = $arrOdrItems['products']['product_id'];
                                $arrAddon = unserialize($arrOdrItems['addon']);
                                
                                if ($arr_service_goods_type_obj['goods'])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj['goods'];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arrOdrItems['goods_id'],'product_id'=>$arrOdrItems['products']['product_id']), $arrGoods);
                                }
                                
                                $arrOdrItems['products']['name']  = $arrOdrItems['name'];
                                /*if ($arrOdrItems['addon'])
                                {                                        
                                    $arrOdrItems['addon'] = unserialize($arrOdrItems['addon']);
                                    if ($arrOdrItems['addon']['product_attr'])
                                    {
                                        $arrOdrItems['products']['name'] .= '(';
                                        foreach ($arrOdrItems['addon']['product_attr'] as $arr_special_info)
                                        {
                                            $arrOdrItems['products']['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                        }
                                        $arrOdrItems['products']['name'] = substr($arrOdrItems['products']['name'], 0, strpos($arrOdrItems['products']['name'], app::get('b2c')->_('、')));
                                        $arrOdrItems['products']['name'] .= ')';
                                    }
                                }*/
                                
                                $arrItems[] = array(
                                    'bn' => $arrOdrItems['bn'],
                                    'name' => $arrOdrItems['products']['name'] ? $arrOdrItems['products']['name'] : $arrOdrItems['name'],
                                    'minfo' => $arrAddon,
                                    'addon' => $arrAddon,
                                    'products' => array(
                                        'name' => $arrOdrItems['products']['name'] ? $arrOdrItems['products']['name'] : $arrOdrItems['name'],
                                        'store' => $arrOdrItems['products']['store'] ? $arrOdrItems['products']['store'] : '-',
                                    ),
                                    'quantity' => $arrOdrItems['quantity'],
                                    'sendnum' => $arrOdrItems['sendnum'],
                                    'product_id' => $product_id,
                                    'item_id' => $arrOdrItems['item_id'],
                                    'needsend' => $this->objMath->number_minus(array($arrOdrItems['quantity'], $arrOdrItems['sendnum'])),
                                );
                            }
                            elseif ($arrOdrItems['item_type'] == 'adjunct')
                            {
                                $good_id = $arrOdrItems['products']['goods_id'];
                                $product_id = $arrOdrItems['products']['product_id'];
                                $arrAddon = unserialize($arrOdrItems['addon']);
                                
                                if ($arr_service_goods_type_obj['adjunct'])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj['adjunct'];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arrOdrItems['goods_id'],'product_id'=>$arrOdrItems['products']['product_id']), $arrGoods);
                                }
                                
                                $arrOdrItems['products']['name']  = $arrOdrItems['name'];
                                /*if ($arrOdrItems['addon'])
                                {                                        
                                    $arrOdrItems['addon'] = unserialize($arrOdrItems['addon']);
                                    if ($arrOdrItems['addon']['product_attr'])
                                    {
                                        $arrOdrItems['products']['name'] .= '(';
                                        foreach ($arrOdrItems['addon']['product_attr'] as $arr_special_info)
                                        {
                                            $arrOdrItems['products']['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                        }
                                        $arrOdrItems['products']['name'] = substr($arrOdrItems['products']['name'], 0, strpos($arrOdrItems['products']['name'], app::get('b2c')->_('、')));
                                        $arrOdrItems['products']['name'] .= ')';
                                    }
                                }*/
                                
                                $arrItems[] = array(
                                    'bn' => $arrOdrItems['bn'],
                                    'name' => $arrOdrItems['products']['name'] ? $arrOdrItems['products']['name'] : $arrOdrItems['name'],
                                    'minfo' => $arrAddon,
                                    'addon' => $arrAddon,
                                    'products' => array(
                                        'name' => $arrOdrItems['products']['name'] ? $arrOdrItems['products']['name'] : $arrOdrItems['name'],
                                        'store' => $arrOdrItems['products']['store'] ? $arrOdrItems['products']['store'] : '-',
                                    ),
                                    'quantity' => $arrOdrItems['quantity'],
                                    'sendnum' => $arrOdrItems['sendnum'],
                                    'product_id' => $product_id,
                                    'item_id' => $arrOdrItems['item_id'],
                                    'needsend' => $this->objMath->number_minus(array($arrOdrItems['quantity'], $arrOdrItems['sendnum'])),
                                );
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj[$arrOdrItems['item_type']])
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrOdrItems['item_type']];                                
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arrOdrItems['goods_id'],'product_id'=>$arrOdrItems['products']['product_id']), $arrGoods);
                                
                                $arrOdrItems['products']['name']  = $arrOdrItems['name'];
                                if ($arrOdrItems['addon'])
                                {                                        
                                    $arrOdrItems['addon'] = unserialize($arrOdrItems['addon']);
                                    if ($arrOdrItems['addon']['product_attr'])
                                    {
                                        $arrOdrItems['products']['name'] .= '(';
                                        foreach ($arrOdrItems['addon']['product_attr'] as $arr_special_info)
                                        {
                                            $arrOdrItems['products']['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                        }
                                        $arrOdrItems['products']['name'] = substr($arrOdrItems['products']['name'], 0, strpos($arrOdrItems['products']['name'], app::get('b2c')->_('、')));
                                        $arrOdrItems['products']['name'] .= ')';
                                    }
                                }
                                
                                $gift_items[] = array(
                                    'goods_id' => $arrOdrItems['goods_id'],
                                    'nums' => ($gift_items[$arrOdrItems['goods_id']]) ? $this->objMath->number_plus(array($gift_items[$arrOdrItems['goods_id']]['nums'],$arrOdrItems['quantity'])) : $arrOdrItems['quantity'],
                                    'name' => $arrOdrItems['products']['name'],
                                    'point' => $arrOdrItems['score'] ? $arrOdrItems['score'] : '0',
                                    'sendnum' => $arrOdrItems['sendnum'],
                                    'store' => is_null($arrGoods['products']['store']) ? app::get('b2c')->_('无限库存') : $arrGoods['products']['store'],
                                    'needsend' => $this->objMath->number_minus(array($arrOdrItems['quantity'], $arrOdrItems['sendnum'])),
                                    'item_id' => $arrOdrItems['item_id'],
                                );
                            }
                        }
                    }
                }
                else
                {
                    if ($arrOdrObjects['obj_type'] == 'gift')
                    {
                        if ($arr_service_goods_type_obj[$arrOdrObjects['obj_type']])
                        { 
                            $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrOdrObjects['obj_type']];
                                
                            foreach ($arrOdrObjects['order_items'] as $gift_key => $gift_item)
                            {
                                if (!$gift_item['products'])
                                {
                                    $o = $this->app->model('order_items');
                                    $tmp = $o->getList('*', array('item_id'=>$gift_item['item_id']));
                                    $gift_item['products']['product_id'] = $tmp[0]['product_id'];
                                }
                                
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $gift_item['goods_id'],'product_id'=>$gift_item['products']['product_id']), $arrGoods);
                                
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
                                
                                if (isset($gift_items[$gift_item['goods_id']]) && $gift_items[$gift_item['goods_id']])
                                {
                                    $gift_items[$gift_item['goods_id']]['nums'] = $this->objMath->number_plus(array($gift_items[$gift_item['goods_id']]['nums'], $gift_item['quantity']));
                                    $gift_items[$gift_item['goods_id']]['sendnum'] = $this->objMath->number_plus(array($gift_items[$gift_item['goods_id']]['sendnum'], $gift_item['sendnum']));
                                    $gift_items[$gift_item['goods_id']]['needsend'] = $this->objMath->number_plus(array($gift_items[$gift_item['goods_id']]['needsend'], $this->objMath->number_minus(array($gift_item['quantity'],$gift_item['sendnum']))));
                                }
                                else
                                {                           
                                    $gift_items[] = array(
                                        'goods_id' => $gift_item['goods_id'],
                                        'nums' => $gift_item['quantity'],
                                        'name' => $gift_name,
                                        'point' => $gift_item['score'] ? $gift_item['score'] : '0',
                                        'sendnum' => $gift_item['sendnum'],
                                        'store' => is_null($arrGoods['products']['store']) ? app::get('b2c')->_('无限库存') : $arrGoods['products']['store'],
                                        'needsend' => $this->objMath->number_minus(array($gift_item['quantity'], $gift_item['sendnum'])),
                                        'item_id' => $gift_item['item_id'],
                                    );
                                }
                            }
                        }
                    }
                    else
                    {
                        // 赠品以外的其他区块的解析.
                        if ($arr_service_goods_type_obj[$arrOdrObjects['obj_type']])
                        {
                            $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrOdrObjects['obj_type']];
                            $str_service_goods_type_obj->get_order_object($arrOdrObjects, $arrGoods);
                            if (is_array($arrGoods) && $arrGoods)
                            {
                                foreach ($arrGoods as $arr)
                                {
                                    $extends_items[$arrOdrObjects['item_type']][] = array(
                                        'goods_id' => $arr['goods_id'],
                                        'nums' => $arr['quantity'],
                                        'name' => $arr['name'],
                                        'point' => $arr['score'] ? $arr['score'] : '0',
                                        'sendnum' => $arr['sendnum'],
                                        'store' => is_null($arr['store']) ? app::get('b2c')->_('无限库存') : $arr['store'],
                                        'needsend' => $this->objMath->number_minus(array($arr['quantity'], $arr['sendnum'])),
                                        'item_id' => $arr['item_id'],
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $this->pagedata['arrItems'] = $arrItems;
        $this->pagedata['giftItems'] = $gift_items;
        $this->pagedata['extendsItems'] = $extends_items;
        $areas = explode(':',$this->pagedata['order']['consignee']['area']);
        $this->pagedata['order']['consignee']['area_show'] = $areas[1];
        // 得到物流公司的信息
        $objDlytype = $this->app->model('dlytype');
        $arrDlytype = $objDlytype->dump($this->pagedata['order']['shipping']['shipping_id']);
        //获得发货地址信息
        $obj_ads = app::get('business')->model('dlyaddress');
        $addresses = $obj_ads->getList('*',array('store_id'=>$sto->storeinfo['store_id']));
        foreach($addresses as $k=>$v){
            $sdf = explode(':',$v['region']);
            $addresses[$k]['region'] = $sdf[1];
        }
        $this->pagedata['addresses'] = $addresses;

        $this->pagedata['corp_id'] = $arrDlytype['corp_id'];
        //echo '<pre>';print_r($addresses);exit;
        if($from == 'seller'){
            if ($this->pagedata['order']['shipping']['shipping_id'] == '0') {
                $this->page('site/order/godelivery_pickup.html',true,'business');
                return;
            }
            $this->page('site/order/godelivery.html',true,'business');
        }else{
            if ($this->pagedata['order']['shipping']['shipping_id'] == '0') {
                $this->pagedata['_PAGE_'] = 'godelivery_pickup.html';
            }
            $this->output();
        }
    }

    /**
     * 发货订单处理
     * @params null
     * @return null
     */
    public function dodelivery()
    {
        $obj_order = &$this->app->model('orders');
        $rp = app::get('aftersales')->model('return_product');
        $return_id = $rp->getRow('*',array('order_id'=>$_POST['order_id'],'refund_type'=>'1'));
        if($return_id){
            $obj_return_policy = kernel::single('aftersales_data_return_policy');

            $sdf = array(
                'return_id' => $return_id['return_id'],
                'status' => '5',
            );
            
            $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
            if ($this->pagedata['return_status'])
                $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
            
            $obj_aftersales = kernel::servicelist("api.aftersales.request");
            foreach ($obj_aftersales as $obj_request)
            {
                $obj_request->send_update_request($sdf);
            }

            //修改订单状态
            $refund_status = array('refund_status'=>'2');
            $rs = $obj_order->update($refund_status,array('order_id'=>$_POST['order_id']));
        }
        
        if(!$order_id) $order_id = $_POST['order_id'];
        else $_POST['order_id'] = $order_id;
        
        $sdf = $_POST;

        $arrMember = $this->get_current_member();
        $sdf['opid'] = $arrMember['member_id'];
        $sdf['opname'] = $arrMember['uname'];
        $this->begin($this->gen_url(array('app' =>'business','ctl'=>'site_member','act' =>'seller_order')));
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_delivery($sdf['order_id'],$sdf,$message))
        {
            $this->end(false, $message);
        }
       
        // 处理支付单据.
        $objB2c_delivery = b2c_order_delivery::getInstance($this->app, $this->app->model('delivery'));
        if ($objB2c_delivery->generate($sdf, $this, $message))
        {            
            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$sdf['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');
            $obj_corp = app::get('b2c')->model('dlycorp');
            $code = $obj_corp->dump(array('corp_id'=>$sdf['logi_id']));
            if($code['corp_code'] == 'BAM'){
                $data['confirm_time'] = time()+(app::get('b2c')->getConf('member.to_finish_BAM'))*86400;
            }else{
                $data['confirm_time'] = time()+(app::get('b2c')->getConf('member.to_finish'))*86400;
            }
            $data['del_address'] = $sdf['deladdress'];
            $arr = app::get('business')->model('orders')->update($data,array('order_id' => $sdf['order_id']));
            //$arr = $this->app->model('orders')->dump(array('order_id' => $sdf['order_id']));
            $order = $obj_order->dump($sdf['order_id']);
            if ($order['shipping']['shipping_id'] == '0' && !$sdf['logi_id']) {
                // 买家自提
                kernel::single('b2c_orderautojob')->order_do_finish($sdf['order_id']);
            }
            $this->end(true, app::get('b2c')->_('发货成功'));
        }
        else
        {
            $this->end(false, $message);
        }
    }

    

    /**
     * 延长收货时间天数
     */
    public function extend_confirm_before(){
        $this->pagedata['order_id'] = $_GET['order_id'];
        $this->page('site/order/extend_confirm.html',true,'business');
    }

    /**
     * 延长收货时间
     * @params string oder id
     * @return boolean 成功与否
     */
    public function extend_confirm(){
        $confirm_time = app::get('business')->model('orders')->dump(array('order_id' => $_POST['order_id']),'confirm_time');
        $data['confirm_time'] = $confirm_time['confirm_time']+$_POST['delayDays']*86400;
        $arr = app::get('business')->model('orders')->update($data,array('order_id' => $_POST['order_id']));

        $objorder_log = $this->app->model('order_log');

        $log_text = '延长收货时间'.$_POST['delayDays'].'天！';

        $arrMember = $this->get_current_member();
        $opid = $arrMember['member_id'];
        $opname = $arrMember['uname'];

        $sdf_order_log = array(
            'rel_id' => $_POST['order_id'],
            'op_id' => $opid,
            'op_name' => $opname,
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'extend_time',
            'result' => 'SUCCESS',
            'log_text' => $log_text,
        );
        $log_id = $objorder_log->save($sdf_order_log);

        $this->page('site/order/success.html',true,'business');
    }

    /**
     * 修改价格
     */
    public function change_price_before(){
        $this->pagedata['order_id'] = $_GET['order_id'];
        $objOrder = &$this->app->model('orders');
        $objOrder_items = &$this->app->model('order_items');
        $aORet = $objOrder->dump($_GET['order_id'],'*');
        $aORet_items = $objOrder_items->getList('*',array('order_id'=>$_GET['order_id']));
        foreach($aORet_items as $key=>$val){
            if(strlen($val['name'])>35){ 
                $aORet_items[$key]['name']=$this->utf8Substr($val['name'],0,15)."…";
            }
        }
        $this->pagedata['count'] = count($aORet_items);
        $this->pagedata['items'] = $aORet_items;
        $this->pagedata['count'] = count($aORet_items);
        $this->pagedata['orders'] = $aORet;
        
        $this->page('site/order/change_price.html',true,'business');
    }

    /**
     * 修改价格
     */
    public function change_price(){
        $cost_freight = app::get('business')->model('orders')->dump(array('order_id' => $_POST['order_id']),'cost_freight,total_amount,currency');
        if(!is_numeric($_POST['ship_cost'])){
            $this->pagedata['message'] = '请填写正整数，修改失败！';
            $this->page('site/order/success.html',true,'business');
        }elseif($_POST['ship_cost'] < 0){
            $this->pagedata['message'] = '修改运费不能为负数，修改失败！';
            $this->page('site/order/success.html',true,'business');
        }else{
            $data['total_amount'] = $cost_freight['total_amount'] + ($_POST['ship_cost'] - $cost_freight['shipping']['cost_shipping']);
            $data['cost_freight'] = $_POST['ship_cost'];

            //计算转换后的价钱
            $system_money_decimals = $this->app->getConf('system.money.decimals');
            $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
            $data['final_amount'] = app::get('ectools')->model("currency")->changer_odr($data['total_amount'], $cost_freight['currency'], true, false, $system_money_decimals, $system_money_operation_carryset);
            //end
            if($this->check_order($_POST['order_id'])){
                $arr = app::get('b2c')->model('orders')->update($data,array('order_id' => $_POST['order_id']));
                $objorder_log = $this->app->model('order_log');

                $log_text = '修改运费为'.$_POST['ship_cost'].'元！';

                $arrMember = $this->get_current_member();
                $opid = $arrMember['member_id'];
                $opname = $arrMember['uname'];

                $sdf_order_log = array(
                    'rel_id' => $_POST['order_id'],
                    'op_id' => $opid,
                    'op_name' => $opname,
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'change_price',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);
                $this->pagedata['message'] = '修改成功！';
                $this->page('site/order/success.html',true,'business');
            }else{
                $this->pagedata['message'] = '信息错误，无法修改！';
                $this->page('site/order/success.html',true,'business');
            }
        }
    }

    public function check_order($order_id){
        $order_info = app::get('b2c')->model('orders')->dump(array('order_id' => $order_id),'pay_status,ship_status,status');
        if($order_info['pay_status'] != 0 || $order_info['ship_status'] != 0 || $order_info['status'] != 'active'){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 生成退款单页面
     * @params string order id
     * @return string html
     */
    public function gorefund($order_id,$page=1)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));

        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        //$this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);
        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }
        $point_money_value = app::get('b2c')->getConf('site.point_money_value');
        $this->pagedata['gorefund_price'] = ($this->pagedata['order']['payed']) - $this->pagedata['order']['shipping']['cost_shipping'];
        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

		$objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
			$tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
						$item['item_type'] = 'goods';

					if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
						$tmp_array = (array)$tmp_array;
						if (!$tmp_array) continue;
						
						$product_id = $tmp_array['products']['product_id'];
						if (!$order_items[$product_id]){
							$order_items[$product_id] = $tmp_array;
						}else{
							$order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
							$order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
						}
						//$order_items[$item['products']['product_id']] = $tmp_array;
					}
                }
            }
			else
			{
				if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
				{
					$tmp_array = (array)$tmp_array;
					if (!$tmp_array) continue;
					foreach ($tmp_array as $tmp){
						if (!$order_items[$tmp['product_id']]){
							$order_items[$tmp['product_id']] = $tmp;
						}else{
							$order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
							$order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
							$order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
						}
					}
				}
				//$order_items = array_merge($order_items, $tmp_array);
			}
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        $count = count($order_items);
        //$arrMaxPage = $this->get_start($page, $count);
        //$this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        
        //echo '<pre>';print_r($this->pagedata);exit;
        $this->output();
    }

    	/**
	 * 截取文件名不包含扩展名
	 * @param 文件全名，包括扩展名
	 * @return string 文件不包含扩展名的名字
	 */
	private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

	public function return_save()
    {
        //echo '<pre>';print_r($_POST);exit;
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

		if (!$_POST['product_bn'])
		{
			$com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
			$this->end(false, app::get('aftersales')->_("您没有选择商品，请先选择商品！"), $com_url);
		}

        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $product_data = array();
        foreach ((array)$_POST['product_bn'] as $key => $val)
        {
			if ($_POST['product_item_nums'][$key] < intval($_POST['product_nums'][$key]))
			{
				$com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
				$this->end(false, app::get('aftersales')->_("申请售后商品的数量不能大于订购数量: "), $com_url);
			}

            $item = array();
            $item['bn'] = $val;
            $item['name'] = $_POST['product_name'][$key];
            $item['num'] = intval($_POST['product_nums'][$key]);
            $product_data[] = $item;
        }

        $aData['order_id'] = $_POST['order_id'];
        $aData['add_time'] = time();
        $aData['image_file'] = $image_id;
        //$aData['member_id'] = $this->member['member_id'];
        $aData['member_id'] = $_POST['member_id'];
        $aData['product_data'] = serialize($product_data);
        $aData['content'] = $_POST['content'];
        $aData['status'] = 1;

        $msg = "";
        $obj_aftersales = kernel::service("api.aftersales.request");
        if ($obj_aftersales->generate($aData, $msg))
        {
			$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
			if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
			{
				if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
					$obj_rpc_request_service->rpc_caller_request($aData,'aftersales');
			}
			else
			{
				$obj_aftersales->rpc_caller_request($aData);
			}

            $this->end(true, app::get('b2c')->_('提交成功！'), $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        }
        else
        {
            $this->end(false, $msg, $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        }
    }
    

    function utf8Substr($str, $from, $len) 
    { 
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'. 
        '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', 
        '$1',$str); 
    } 
}
