<?php

 
class b2c_finder_orders{

    var $detail_basic = '基本信息';
    var $detail_items = '商品';
    var $detail_bills = '收退款记录';
    var $detail_delivery = '收发货记录';
    var $detail_pmt = '优惠方案';
    var $detail_mark = '订单备注';
    var $detail_logs = '订单日志';
    var $detail_msg = '顾客留言';
    //var $column_editbutton = '操作';
    
    public function __construct($app)
    {
        $this->app = $app;
        $this->app_ectools = app::get('ectools');
        $this->odr_action_buttons = array('pay','delivery','finish','refund','reship','cancel','delete');
        // 判定是否绑定ome或者其他后端店铺
        $obj_b2c_shop = $this->app->model('shop');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type'=>'ecos.ome'));
        if ($cnt > 0)
        {
            $this->odr_action_is_all_disable = true;
        }
        else
        {
            $this->odr_action_is_all_disable = false;
        }
    }
    
    public function detail_basic($order_id)
    {
        $render = $this->app->render();
        $order = $this->app->model('orders');
        $payments = app::get('ectools')->model('payments');
        
        if (substr($_POST['orderact'], 0, 3) == 'pay')
        {
            $sdf['order_id'] = $_POST['order_id'];
            $sdf['payment_id'] = $payments->gen_id();
            $sdf['bill_type'] = 'pay';
            $payments->save($sdf);
        }
        elseif (substr($_POST['orderact'], 0, 6) == 'refund')
        {
            $sdf['order_id'] = $_POST['order_id'];
            $sdf['payment_id'] = $payments->gen_id();
            $sdf['bill_type'] = 'refund';
            $payments->save($sdf);
        }
        elseif (substr($_POST['orderact'], 0, 7) == 'consign')
        {
            $delivery = $this->app->model('delivery');
            $sdf['delivery_id'] = $delivery->gen_id('delivery');
            $sdf['order_id'] = $_POST['order_id'];
            $sdf['bill_type'] = 'delivery';
            $delivery->save($sdf);
        }
        elseif (substr($_POST['orderact'], 0, 6) == 'return')
        {
            $delivery = $this->app->model('delivery');
            $sdf['delivery_id'] = $delivery->gen_id('return');
            $sdf['order_id'] = $_POST['order_id'];
            $sdf['bill_type'] = 'return';
            $delivery->save($sdf);
        }
        
        $subsdf = array('order_pmt'=>array('*'),'order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $aOrder = $order->dump($order_id, '*', $subsdf);

        $oCur = $this->app_ectools->model('currency');
        $aCur = $oCur->getSysCur();
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
    
        if (intval($aOrder['payinfo']['pay_app_id']) < 0)
            $aOrder['payinfo']['pay_app_id'] = app::get('b2c')->_('货到付款');
        else
        {  
            $payid = $aOrder['payinfo']['pay_app_id'];
            $obj_paymentsfg = app::get('ectools')->model('payment_cfgs');
            $arr_payments = $obj_paymentsfg->getPaymentInfo($payid);
            $aOrder['payinfo']['pay_app_id'] = $arr_payments['app_name'] ? $arr_payments['app_name'] : $payid;
        }
    
        if ($aOrder['member_id'])
        {
            $member = $this->app->model('members');
            $aOrder['member'] = $member->dump($aOrder['member_id'], '*', array(':account@pam'=>'*'));
            
            // 得到meta的信息
            $arrTree = array();
            $index = 0;
            if ($aOrder['member']['contact'])
            {
                if ($aOrder['member']['contact']['qq'])
                    $arrTree[$index++] = array(
                        'attr_name' => app::get('b2c')->_('腾讯QQ'),
                        'attr_tyname' => 'QQ',
                        'value' => $aOrder['member']['contact']['qq'],
                    );
                
                if ($aOrder['member']['contact']['msn'])
                    $arrTree[$index++] = array(
                        'attr_name' => 'windows live',
                        'attr_tyname' => 'MSN',
                        'value' => $aOrder['member']['contact']['msn'],
                    );
                
                if ($aOrder['member']['contact']['wangwang'])
                    $arrTree[$index++] = array(
                        'attr_name' => 'WangWang',
                        'attr_tyname' => app::get('b2c')->_('旺旺'),
                        'value' => $aOrder['member']['contact']['wangwang'],
                    );
                
                if ($aOrder['member']['contact']['skype'])
                    $arrTree[$index++] = array(
                        'attr_name' => 'Skype',
                        'attr_tyname' => 'Skype',
                        'value' => $aOrder['member']['contact']['skype'],
                    );
                
                $render->pagedata['tree'] = $arrTree;
            }
        }
    
        foreach ((array)$aItems as $k => $rows)
        {
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            if ($rows['minfo'] && unserialize($rows['minfo']))
            {
                $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }
            else 
            {
                $aItems[$k]['minfo'] = array();
            }
            if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= '<br>'.app::get('b2c')->_('配件：').$aItems[$k]['addon']['adjname'];
        }
        $render->pagedata['goodsItems'] = $aItems;
        $render->pagedata['giftItems'] = $gItems;
    
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $render->pagedata['order'] = $aOrder;
        //+todo license权限----------
    //    $_is_all_ship = 1;
    //    $_is_all_return_ship = 1;
    
        foreach ((array)$aItems as $_item)
        {
            if((!$_item['supplier_id']) && ($_item['sendnum'] < $_item['nums'] )){
                $_is_all_ship = 0;
            }
            if((!$_item['supplier_id']) && ($_item['sendnum'] > 0 )){
                $_is_all_return_ship = 0;
            }
        }
        
        foreach((array)$gItems as $g_item){
            if($g_item['sendnum'] < $g_item['nums'] ){
                $_is_all_ship = 0;
            }
            if($g_item['sendnum'] > 0 ){
                $_is_all_return_ship = 0;
            }
        }
        $render->pagedata['order']['_is_all_ship'] = $_is_all_ship;
        $render->pagedata['order']['_is_all_return_ship'] = $_is_all_return_ship;
        $render->pagedata['order']['flow']= array('refund' => $this->app->getConf('order.flow.refund'),
            'consign' => $this->app->getConf('order.flow.consign'),
            'reship' => $this->app->getConf('order.flow.reship'),
            'payed' => $this->app->getConf('order.flow.payed'),
        );
            
        if (!$render->pagedata['order']['member']['contact']['area'])
        {
            $render->pagedata['order']['member']['contact']['area'] = '';
        }
        else
        {
            if (strpos($render->pagedata['order']['member']['contact']['area'], ':') !== false)
            {
                $arr_areas = explode(':', $render->pagedata['order']['member']['contact']['area']);
                $render->pagedata['order']['member']['contact']['area'] = $arr_areas[1];
            }
        }
        
        if (strpos($render->pagedata['order']['consignee']['area'], ':') !== false)
        {
            $arr_areas = explode(':', $render->pagedata['order']['consignee']['area']);
            $render->pagedata['order']['consignee']['area'] = $arr_areas[1];
        }
        
        $objMath = kernel::single('ectools_math');
        $render->pagedata['order']['pmt_amount'] = $objMath->number_plus(array($render->pagedata['order']['pmt_goods'],$render->pagedata['order']['pmt_order']));
        if ($render->pagedata['order']['pmt_amount'] > 0)
        {
            if (isset($aOrder['order_pmt']) && $aOrder['order_pmt'])
            {
                foreach ($aOrder['order_pmt'] as $arr_pmts)
                {
                    if ($arr_pmts['pmt_type'])
                    {
                        switch ($arr_pmts['pmt_type'])
                        {
                            case 'order':
                            case 'coupon':
                                $obj_save_rules = $this->app->model('sales_rule_order');
                                break;
                            case 'goods':
                                $obj_save_rules = $this->app->model('sales_rule_goods');
                                break;
                            default:
                                break;
                        }
                    }
                    
                    $arr_save_rules = $obj_save_rules->dump($arr_pmts['pmt_id']);
                    $render->pagedata['order']['use_pmt'] .= $arr_save_rules['name'] . ', ';
                }
                
                if (strpos($render->pagedata['order']['use_pmt'], ', ') !== false)
                    $render->pagedata['order']['use_pmt'] = substr($render->pagedata['order']['use_pmt'], 0, strlen($render->pagedata['order']['use_pmt']) - 2);
            }
        }
        
        // 判断是否使用了推广服务
        $is_bklinks = 'false';
        $obj_input_helpers = kernel::servicelist("html_input");
        if (isset($obj_input_helpers) && $obj_input_helpers)
        {
            foreach ($obj_input_helpers as $obj_bdlink_input_helper)
            {
                if (get_class($obj_bdlink_input_helper) == 'bdlink_input_helper')
                {
                    $is_bklinks = 'true';
                }
            }
        }
        $render->pagedata['is_bklinks'] = $is_bklinks;
        
        /** 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        
        // 得到订单的优惠方案
        $arr_pmt_lists = array();
        $arr_order_items = array();
        $arr_gift_items = array();
        $arr_extends_items = array();
        
        $this->get_pmt_lists($aOrder, $arr_pmt_lists);
        $this->get_goods_detail($aOrder, $arr_order_items, $arr_gift_items, $arr_extends_items);
        
        $render->pagedata['goodsItems'] = $arr_order_items;
        $render->pagedata['giftItems'] = $arr_gift_items;
        $render->pagedata['arr_extends_items'] = $arr_extends_items;
        $render->pagedata['order']['pmt_list'] = $arr_pmt_lists;
        $obj_action_button = kernel::servicelist('b2c_order.b2c_finder_orders');
        $arr_obj_action_button = array();
        if ($obj_action_button)
        {
            foreach($obj_action_button as $object) 
            {
                if(!is_object($object)) continue;
                
                if( method_exists($object,'get_order') ) 
                    $index = $object->get_order();
                else $index = 10;
                
                while(true) {
                    if( !isset($arr_obj_action_button[$index]) )break;
                    $index++;
                }
                $arr_obj_action_button[$index] = $object;
            }
        }
        ksort($arr_obj_action_button);
        if ($arr_obj_action_button)
        {
            $render->pagedata['action_buttons'] = array();
            $render->pagedata['ext_action_buttons'] = array();
            foreach ($arr_obj_action_button as $obj)
            {
                $obj->is_display($this->odr_action_buttons);
                $render->pagedata['action_buttons'] = $obj->get_buttons($render->pagedata['order'], $this->odr_action_is_all_disable);
                $render->pagedata['ext_action_buttons'] = $obj->get_extension_buttons($render->pagedata['order']);
            }
        }
        // 添加 html 埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($render,$order_id,'admin/invoice_detail.html');
                }
            }
        }
        // 判断是否安装物流单跟踪服务
        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $render->pagedata['services']['logisticstrack'] = $logisticst_service;
        }
        
        $render->pagedata['services']['logisticstrack_url'] = 'index.php?'.utils::http_build_query(array(
            'app'=>'b2c','ctl'=>'admin_order','act'=>'index','action'=>'detail',
            'finderview'=>'detail_delivery','_finder'=>array('finder_id'=>$_GET['finder_id']),'finder_name'=>$_GET['finder_id'],'finder_id'=>$_GET['finder_id'],
            'id'=>$order_id,
        )); 
        
        return $render->fetch('admin/order/order_detail.html');
    }
    
    public function detail_items($order_id)
    {
        $render = app::get('base')->render();
        $order = $this->app->model('orders');
        $render->pagedata['orderid'] = $orderid;        
        
        $subSdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $aItems = $order->dump($order_id,'*',$subSdf);
        
        $order_items = array();
        $gift_items = array();
        $extend_items = array();
        $this->get_goods_detail($aItems, $order_items, $gift_items, $extend_items, 'admin_detail_items');
        
        //$order_items = array_merge($order_items,$gift_items);
        $render->pagedata['goodsItems'] = $order_items;
        $render->pagedata['giftItems'] = $gift_items;
        $render->pagedata['extends_items'] = $extend_items;
        $render->pagedata['site_base_url'] = kernel::base_url();
        
        return $render->fetch('admin/order/order_items.html',$this->app->app_id);
    }
    
    private function get_goods_detail(&$aItems, &$order_items, &$gift_items, &$extend_items, $tml='admin_order_detail')
    {
        $order_items = array();
        $objMath = kernel::single("ectools_math");
        
        if ($aItems['order_objects'])
        {
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;                
            }
                    
            foreach ($aItems['order_objects'] as $k=>$v)
            {
                $index = 0;
                $index_adj = 0;
                $index_gift = 0;
                $image_set = app::get('image')->getConf('image.set');
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
                            if($item['addon'] && unserialize($item['addon'])){
                                $gItems[$k]['minfo'] = unserialize($item['addon']);
                            }else{
                                $gItems[$k]['minfo'] = array();
                            }
                            
                            if ($item['item_type'] == 'product')
                            {  
                                if ($arr_service_goods_type_obj['goods'])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj['goods'];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods, $tml);
                                }
                                
                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                
                                $order_items[$k]['product'] = $item;
                                $order_items[$k]['product']['small_pic'] = $arrGoods['image_default_id'];
                                $order_items[$k]['product']['is_type'] = $v['obj_type'];
                                $order_items[$k]['product']['item_type'] = $arrGoods['category']['cat_name'];
                                $order_items[$k]['product']['nums'] = $item['quantity'];
                                $order_items[$k]['product']['minfo'] = $gItems[$k]['minfo'];
                                $order_items[$k]['product']['total_amount'] = $objMath->number_multiple(array($item['price'], $item['quantity']));
                                $order_items[$k]['product']['link'] = $arrGoods['link_url'];
                                
                                if ($item['addon'])
                                {                                        
                                    $item['addon'] = unserialize($item['addon']);
                                    if ($item['addon']['product_attr'])
                                    {
                                        $order_items[$k]['product']['name'] .= '(';
                                        foreach ($item['addon']['product_attr'] as $arr_special_info)
                                        {
                                            $order_items[$k]['product']['name'] .= $arr_special_info['label'] . app::get('b2c')->_('：') . $arr_special_info['value'] . app::get('b2c')->_('、'); 
                                        }
                                        $order_items[$k]['product']['name'] = substr($order_items[$k]['product']['name'], 0, strrpos($order_items[$k]['product']['name'], app::get('b2c')->_('、')));
                                        $order_items[$k]['product']['name'] .= ')';
                                    }
                                }
                            }
                            else
                            {
                                if ($arr_service_goods_type_obj['adjunct'])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj['adjunct'];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods, $tml);
                                }
                                
                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                            
                                $order_items[$k]['adjunct'][$index_adj] = $item;
                                $order_items[$k]['adjunct'][$index_adj]['small_pic'] = $arrGoods['image_default_id'];
                                $order_items[$k]['adjunct'][$index_adj]['is_type'] = $v['obj_type'];
                                $order_items[$k]['adjunct'][$index_adj]['item_type'] = $arrGoods['category']['cat_name'];
                                $order_items[$k]['adjunct'][$index_adj]['nums'] = $item['quantity'];
                                $order_items[$k]['adjunct'][$index_adj]['total_amount'] = $objMath->number_multiple(array($item['price'], $item['quantity']));
                                $order_items[$k]['adjunct'][$index_adj]['link'] = $arrGoods['link_url'];
                                
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
                                        $order_items[$k]['adjunct'][$index_adj]['name'] = substr($order_items[$k]['adjunct'][$index_adj]['name'], 0, strrpos($order_items[$k]['adjunct'][$index_adj]['name'], app::get('b2c')->_('、')));
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
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product_id'=>$item['products']['product_id']), $arrGoods, $tml);
                                
                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                
                                $order_items[$k]['gifts'][$index_gift] = $item;
                                $order_items[$k]['gifts'][$index_gift]['small_pic'] = $arrGoods['image_default_id'];
                                $order_items[$k]['gifts'][$index_gift]['is_type'] = $v['obj_type'];
                                $order_items[$k]['gifts'][$index_gift]['item_type'] = $arrGoods['category']['cat_name'];
                                $order_items[$k]['gifts'][$index_gift]['nums'] = $item['quantity'];
                                $order_items[$k]['gifts'][$index_gift]['total_amount'] = $objMath->number_multiple(array($item['price'], $item['quantity']));
                                $order_items[$k]['gifts'][$index_gift]['link'] = $arrGoods['link_url'];
                                
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
                                        $order_items[$k]['gifts'][$index_gift]['name'] = substr($order_items[$k]['gifts'][$index_gift]['name'], 0, strrpos($order_items[$k]['gifts'][$index_gift]['name'], app::get('b2c')->_('、')));
                                        $order_items[$k]['gifts'][$index_gift]['name'] .= ')';
                                    }
                                }
                                
                                $index_gift++;
                            }
                        }
                    }
                }
                else
                {
                    if ($v['obj_type'] == 'gift')
                    {
                        if ($arr_service_goods_type_obj['gift'])
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
                                    $gift_items[$gift_item['goods_id']]['nums'] = $objMath->number_plus(array($gift_items[$gift_item['goods_id']]['nums'], $item['quantity']));
                                else
                                {
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $gift_item['goods_id'],'product_id'=>$gift_item['products']['product_id']), $arrGoods, $tml);
                                    
                                    if (!isset($gift_item['products']['product_id']) || !$gift_item['products']['product_id'])
                                        $gift_item['products']['product_id'] = $gift_item['goods_id'];
                                        
                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    
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
                                
                                    if ($arrGoods)
                                        $gift_items[$gift_item['products']['product_id']] = array(
                                            'goods_id' => $gift_item['goods_id'],
                                            'bn' => $gift_item['bn'],
                                            'nums' => $gift_item['quantity'],
                                            'name' => $gift_name,
                                            'item_type' => $arrGoods['category']['cat_name'],
                                            'price' => $gift_item['price'],
                                            'quantity' => $gift_item['quantity'],
                                            'sendnum' => $gift_item['sendnum'],
                                            'small_pic' => $arrGoods['image_default_id'],
                                            'is_type' => $v['obj_type'],
                                            'total_amount' => $objMath->number_multiple(array($gift_item['price'], $gift_item['quantity'])),
                                            'link' => $arrGoods['link_url'],
                                        );
                                }
                            }
                        }
                    }
                    else
                    {
                        if ($arr_service_goods_type_obj[$v['obj_type']])
                        {
                            $str_service_goods_type_obj = $arr_service_goods_type_obj[$v['obj_type']];
                            $extend_items[] = $str_service_goods_type_obj->get_order_object($v, $arr_Goods, $tml);
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    public function detail_bills($order_id)
    {
        $render = $this->app->render();
        $payments = app::get('ectools')->model('payments');
        $refunds = app::get('ectools')->model('refunds');
        $order_bills = app::get('ectools')->model('order_bills');
        $aBill = $order_bills->getList('*',array('rel_id'=>$order_id, 'bill_type'=>'payments', 'pay_object'=>'order'));
        $aRefund = $order_bills->getList('*',array('rel_id'=>$order_id, 'bill_type'=>'refunds', 'pay_object'=>'order'));
        //判断是否为结算单

        foreach($aRefund as $key=>$val){
            $type = $refunds->dump(array('refund_id'=>$val['bill_id']),'refund_type');
            if($type['refund_type'] == '2'){
                unset($aRefund[$key]);
            }
        }

        if ($aBill)
        {
            foreach ($aBill as &$str_bill)
            {
                $arr_payments = $payments->dump($str_bill['bill_id'], 'pay_name,status,t_payed');
                $str_bill['pay_name'] = $arr_payments['pay_name'];
                $str_bill['status'] = $arr_payments['status'];
                $str_bill['t_payed'] = $arr_payments['t_payed'];
            }
        }
        
        if ($aRefund)
        {
            foreach ($aRefund as &$str_refund)
            {
                $arr_refunds = $refunds->dump($str_refund['bill_id'], 'pay_name,status,t_payed');
                $str_refund['pay_name'] = $arr_refunds['pay_name'];
                $str_refund['status'] = $arr_refunds['status'];
                $str_refund['t_payed'] = $arr_refunds['t_payed'];
            }
        }
        
        $render->pagedata['bills'] = $aBill;
        $render->pagedata['refunds'] = $aRefund;
        $render->pagedata['orderid'] = $order_id;

        return $render->fetch('admin/order/od_bill.html',$this->app->app_id);
    }
    
    public function detail_delivery($order_id){
        $render = $this->app->render();

        $objDelivery = $this->app->model('delivery');
        $objReship = $this->app->model('reship');
        $filter['order_id'] = $order_id;
        $render->pagedata['consign'] = $objDelivery->getList('*',array('order_id'=>$order_id,'status|ne'=>'cancel','disabled'=>'false'));
        $render->pagedata['reship'] =  $objReship->getList('*',$filter);
        $render->pagedata['orderid'] = $order_id;
        
        // 是否注册了订单跟踪app
        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $render->pagedata['services']['logisticstrack'] = $logisticst_service;
        }
        
        if ( $render->pagedata['services']['logisticstrack'] ) {
            foreach( $render->pagedata['consign']  as $k=>&$v) {
                $v['logistictrack_url'] = app::get('desktop')->router()->gen_url(
                    array('app'=>'logisticstrack','ctl'=>'admin_tracker','act'=>'pull','p'=>array('0'=>$v['delivery_id']))
                );
            }
            unset($v);
            foreach( $render->pagedata['reship']  as $k=>&$v) {
                $v['logistictrack_url'] = app::get('desktop')->router()->gen_url(
                    array('app'=>'logisticstrack','ctl'=>'admin_tracker','act'=>'pull','p'=>array('0'=>$v['reship_id']))
                );
            }
            unset($v);
        }
        return $render->fetch('admin/order/od_delivery.html',$this->app->app_id);
    }
    
    public function detail_pmt($order_id)
    {
        $render = $this->app->render();
        
        $order = $this->app->model('orders');
        $subsdf = array('order_pmt'=>array('*'));
        $sdf_order = $order->dump($order_id, '*', $subsdf);
        $arr_pmt_lists = array();
        
        $this->get_pmt_lists($sdf_order, $arr_pmt_lists);
        
        $render->pagedata['pmtlist'] = $arr_pmt_lists;
        
        return $render->fetch('admin/order/od_pmts.html',$this->app->app_id);
    }
    
    private function get_pmt_lists(&$sdf_order, &$arr_pmt_lists)
    {
        $arr_pmt_lists = array();
        
        if (isset($sdf_order['order_pmt']) && $sdf_order['order_pmt'])
        {
            foreach ($sdf_order['order_pmt'] as $arr_pmt_items)
            {
                $arr_pmt_lists[] = array(
                    'pmt_describe' => $arr_pmt_items['pmt_describe'],
                    'pmt_amount' => $arr_pmt_items['pmt_amount'],
                );
            }
        }
        
        return true;
    }
    
    public function detail_mark($order_id)
    {
        $render = $this->app->render();
        $order = $this->app->model('orders');

        $aOrder = $order->dump($order_id,'mark_text,mark_type');
        if ($aOrder['mark_text'])
        {
            $aOrder['mark_text'] = unserialize($aOrder['mark_text']);
        }
        $render->pagedata['mark_text'] = $aOrder['mark_text'];
        $render->pagedata['mark_type'] = $aOrder['mark_type'];
        $render->pagedata['orderid'] = $order_id;
        $render->pagedata['res_url'] = $this->app->res_url;

        return $render->fetch('admin/order/od_mark.html',$this->app->app_id);
    }
    
    public function detail_logs($order_id){
        $render = app::get('base')->render();
        $order = $this->app->model('orders');
        $aOrder = $order->dump($order_id);
    
        $order = &$this->app->model('orders');
        
        $page = ($_POST['page']) ? $_POST['page'] : 1;
        $pageLimit = 10;
        $aLog = $order->getOrderLogList($order_id, $page-1, $pageLimit);
        $ui = new base_component_ui($this->app);
        $render->pagedata['logs'] = $aLog;
        $render->pagedata['result'] = array('SUCCESS'=>app::get('b2c')->_('成功'),'FAILURE'=>app::get('b2c')->_('失败'));
        $pager = array(
            'current'=> $page,
            'total'=> ceil($aLog['page']/$pageLimit),
            'link'=> 'javascript:W.page(\'index.php?app=b2c&ctl=admin_order&act=index&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&finder_id='.$_GET['_finder']['finder_id'].'&id='.$order_id.'&finderview=detail_logs&finder_name='.$_GET['_finder']['finder_id'].'&action=detail&p[0]=_PPP_\', {update:$E(\'.tableform\').parentNode, method:\'post\', data:\'&page=%d\'});',
            'token'=> '_PPP_'
        );
        $render->pagedata['pager'] = $ui->pager($pager);
        $render->pagedata['pagestart'] = ($page-1)*$pageLimit;

        return $render->fetch('admin/order/order_logs.html',$this->app->app_id);
    }
    
    /**
     * 顾客留言
     * @params string order id
     * @return null
     */
    public function detail_msg($order_id)
    {
        $render = $this->app->render();
        $order = $this->app->model('orders');
        $objMath = kernel::single("ectools_math");

        $oMsg = &kernel::single("b2c_message_order");
        $orderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');
        
        //bugfix 0029929 change adm_read_status 
        $obj_member_comments = $this->app->model('member_comments');
        $obj_member_comments->update(array('adm_read_status'=>'true'),array('object_type'=>'order','order_id'=>$order_id,'adm_read_status'=>'false'));
        //$oMsg->sethasreaded($orderid);
        //$aItems = $order->getItemList($orderid);
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $order->dump($order_id,'*',$subsdf);
        
        $order_items = array();
        $gift_items = array();
        $extend_items = array();
        $this->get_goods_detail($sdf_order, $order_items, $gift_items, $extend_items, 'admin_detail_msg');
        
        $render->pagedata['ordermsg'] = $orderMsg;
        $render->pagedata['goodsItems'] = $order_items;
        $render->pagedata['giftsItems'] = $gift_items;
        $render->pagedata['extends_items'] = $extend_items;
        $render->pagedata['orderid'] = $order_id;
        $render->pagedata['site_base_url'] = kernel::base_url();

       return $render->fetch('admin/order/od_msg.html',$this->app->app_id);
    }
    
    public function column_editbutton($row)
    {
        $render = $this->app->render();
        $arr = array(
            'app'=>$_GET['app'],
            'act'=>$_GET['act'],
            'action'=>'detail',
            'id'=>$_GET['id'],
            'finder_name'=>$_GET['_finder']['finder_id'],
        );
        /** 得到订单操作按钮的详细情形 **/
        $obj_order = $this->app->model('orders');
        $arr_order = $obj_order->dump($row['order_id']);
        $arr_order['flow']= array('refund' => $this->app->getConf('order.flow.refund'),
            'consign' => $this->app->getConf('order.flow.consign'),
            'reship' => $this->app->getConf('order.flow.reship'),
            'payed' => $this->app->getConf('order.flow.payed'),
        );
        $actionbutton = kernel::single('b2c_order_actionbutton');
        $actions = $actionbutton->get_buttons($arr_order,$this->odr_action_is_all_disable);
        $extends_actions = $actionbutton->get_extension_buttons($arr_order);
        //$all_actions = array_merge($actions['sequence'],$actions['re_sequence'],$extends_actions);
        /** 结束 **/
        
        /** 根据状态，判定显示与否 **/
        if ($actions['sequence']){
            foreach ($actions['sequence'] as $key=>$buttons){
                if ($buttons['disable'])
                    unset($actions['sequence'][$key]);
            }
        }
        if ($actions['re_sequence']){
            foreach ($actions['re_sequence'] as $key=>$buttons){
                if ($buttons['disable'])
                    unset($actions['re_sequence'][$key]);
            }
        }
        
        if ($extends_actions){
            foreach ($extends_actions as $key=>$buttons){
                if ($buttons['disable'])
                    unset($extends_actions[$key]);
            }
        }
        /** 结束 **/
        /** 订单编辑按钮 **/
        if ($arr_order['pay_status'] == '0' && !$arr_order['ship_status'] && $arr_order['status'] == 'active'){
            $order_edit_disable = false;
        }else{
            $order_edit_disable = true;
        }
        /** 结束 **/
        
        // 判定是否绑定ome或者其他后端店铺
        $obj_b2c_shop = $this->app->model('shop');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type'=>'ecos.ome'));
        if ($cnt > 0)
        {
            $order_edit_disable = true;
        }
        
        /** 订单分组操作链接 **/
        if (!$order_edit_disable)
            $arr_link['info']['edit'] = array(
                //'href'=>'javascript:void(0);',
                'href'=>'index.php?app='.$_GET['app'].'&ctl=admin_order&act=showEdit&p[0]='.$row['order_id'].'&finder_id='.$_GET['_finder']['finder_id'],
                'label'=>'订单编辑',
                'target'=>'_blank',
                'disable'=>$order_edit_disable
            );
        
        if ($actions['sequence'])
            foreach($actions['sequence'] as $key=>$link){
                $pre = $link['flow']?'go':'do';
                $arr_link['sequence'][$key] = array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'index.php?app='.$link['app'].'&ctl=admin_order&act='.$pre.$link['act'].'&p[0]='.$row['order_id'],
                    'label'=>$link['label'],
                    'target'=>$link['confirm']?'confirm':'dialog::{title:\''.$link['label'].':'.$row['order_id'].'\',width:800,height:420}',
                    'disable'=>$link['disable'],
                    'confirm'=>$link['confirm']
                );
            }
        
        if ($actions['re_sequence'])
            foreach($actions['re_sequence'] as $key=>$link){
                $pre = $link['flow']?'go':'do';
                $arr_link['re_sequence'][$key] = array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'index.php?app='.$link['app'].'&ctl=admin_order&act='.$pre.$link['act'].'&p[0]='.$row['order_id'],
                    'label'=>$link['label'],
                    'target'=>$link['confirm']?'confirm':'dialog::{title:\''.$link['label'].':'.$row['order_id'].'\',width:800,height:420}',
                    'disable'=>$link['disable'],
                    'confirm'=>$link['confirm']
                );
            }
        
        if ($extends_actions)
            foreach($extends_actions as $key=>$link){
                $pre = $link['flow']?'go':'do';
                $arr_link['extends'][$key] = array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'index.php?app='.$link['app'].'&ctl=admin_order&act='.$pre.$link['act'].'&p[0]='.$row['order_id'],
                    'label'=>$link['label'],
                    'target'=>$link['confirm']?'confirm':'dialog::{title:\''.$link['label'].':'.$row['order_id'].'\',width:800,height:420}',
                    'disable'=>$link['disable'],
                    'confirm'=>$link['confirm']
                );
            }
            
        $arr_link['finder']['remark'] = array(
            'href'=>'javascript:void(0);',
            'submit'=>'index.php?'.utils::http_build_query($arr).'&ctl=admin_order&finderview=detail_mark&id='.$row['order_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],
            'label'=>'订单备注',
            'target'=>'tab',
            'disable'=>true
        );
        /** 结束 **/
        /** 对所有链接的修改 **/
        $obj_editbuttons = kernel::servicelist('b2c_order.b2c_order_editbutton_modify');
        $arr_obj_editbutton = array();
        if ($obj_editbuttons)
        {
            foreach($obj_editbuttons as $object) 
            {
                if(!is_object($object)) continue;
                
                if( method_exists($object,'get_order') ) 
                    $index = $object->get_order();
                else $index = 10;
                
                while(true) {
                    if( !isset($arr_obj_editbutton[$index]) )break;
                    $index++;
                }
                $arr_obj_editbutton[$index] = $object;
            }
        }
        ksort($arr_obj_editbutton);
        if ($arr_obj_editbutton)
        {
            foreach ($arr_obj_editbutton as $obj)
            {
                if ($obj instanceof b2c_order_service_editbutton_interface){
                    if( method_exists($obj,'get_action_links') ) $obj->get_action_links($arr_link,$arr_order);
                }
            }
        }
        /** end **/
        
        if (!isset($row['status']) || $row['status'] == '')
        {
            $row['status'] = $arr_order['status'];
        }
        $render->pagedata['arr_link'] = $arr_link;
        $render->pagedata['is_active'] = ($row['status'] == 'active') ? 'true' : 'false';
        $render->pagedata['finder_id'] = $_GET['_finder']['finder_id'];
        $render->pagedata['handle_title'] = app::get('b2c')->_('处理订单');
        return $render->fetch('admin/actions.html');
    }
    
    public $column_printer = '打印';
    public $column_printer_order = COLUMN_IN_HEAD;
    public function column_printer($row)
    {
        $res_url = $this->app->res_url;
        $html = '<div class="clearfix print-col">';
        $html.='<div class="span-auto"><a href="index.php?app=b2c&ctl=admin_order&act=printing&p[0]=1&p[1]=' . $row['order_id'] . '" title='.app::get('b2c')->_("购物清单").' target="_blank">'.app::get('b2c')->_('购').'</a></div><div class="span-auto">
                            <a href="index.php?app=b2c&ctl=admin_order&act=printing&p[0]=2&p[1]=' . $row['order_id'] . '" title='.app::get('b2c')->_("配货单").' target="_blank">'.app::get('b2c')->_('配').'</a></div><div class="span-auto">
                              <a href="index.php?app=b2c&ctl=admin_order&act=printing&p[0]=4&p[1]=' . $row['order_id'] . '" title='.app::get('b2c')->_("联合打印").' target="_blank">合</a></div><div class="span-auto last">
                            ';
         $app_express = app::get('express');
        if (isset($app_express) && $app_express && is_object($app_express))
        {
            if ($app_express->is_actived())
            {                        
                $html.='<a href="index.php?app=b2c&ctl=admin_order&act=printing&p[0]=8&p[1]=' . $row['order_id'] . '" title='.app::get('b2c')->_("快递单打印").' target="_blank">'.app::get('b2c')->_('递').'</a></div></div>';
                                   
            }
            else
            {
                ;
            }
        } 
            return $html;
    }
    
    public function row_style($row)
    {
        if (!isset($row['status']) || !isset($row['pay_status']) || !isset($row['ship_status']))
        {
            $obj_order = $this->app->model('orders');
            $tmp = $obj_order->getList('status,pay_status,ship_status',array('order_id'=>$row['order_id']));
            if ($tmp)
            {
                $row['status'] = $tmp[0]['status'];
                $row['pay_status'] = $tmp[0]['pay_status'];
                $row['ship_status'] = $tmp[0]['ship_status'];
            }
        }
        if ($row['status'] == 'finish' || $row['status'] == 'dead' || $row['pay_status'] > 0 || $row['ship_status'] > 0)
            return '';
        
        return 'unoperated';
    }
}
