<?php
 
 
class b2c_ctl_admin_order extends desktop_controller{

    Var $workground = 'b2c_ctl_admin_order';
    
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



    public function index(){
        if($_GET['action'] == 'export') $this->_end_message = '导出订单';
        $this->finder('b2c_mdl_orders',array(

            'title'=>app::get('b2c')->_('订单列表'),
            'allow_detail_popup'=>true,
            'base_filter'=>array('order_refer'=>'local','disabled'=>'false'),
            'use_buildin_export'=>true,
            'actions'=>array(
                            // array('label'=>app::get('b2c')->_('添加订单'),'href'=>'index.php?app=b2c&ctl=admin_order&act=addnew','target'=>'_blank','icon'=>'sss.ccc'),
                            array('label'=>app::get('b2c')->_('打印样式'),'target'=>'_blank','href'=>'index.php?app=b2c&ctl=admin_order&act=showPrintStyle'),
                            array('label'=>app::get('b2c')->_('打印选定订单'),'submit'=>'index.php?app=b2c&ctl=admin_order&act=toprint','target'=>'_blank'),
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>false,'use_buildin_filter'=>true,'use_view_tab'=>true,
            ));
    }
    
    /**
     * 桌面订单相信汇总显示
     * @param null
     * @return null
     */
    public function _views(){
        $mdl_order = $this->app->model('orders');
        $sub_menu = array(
            0=>array('label'=>app::get('b2c')->_('全部'),'optional'=>false,'filter'=>array('disabled'=>'false')),
            1=>array('label'=>app::get('b2c')->_('未处理'),'optional'=>false,'filter'=>array('pay_status'=>array('0'),'ship_status'=>array('0'),'status'=>'active','disabled'=>'false')),
            2=>array('label'=>app::get('b2c')->_('已付款待发货'),'optional'=>false,'filter'=>array('pay_status'=>array('1','2','3'),'ship_status'=>array('0','2'),'status'=>'active','disabled'=>'false')),
            3=>array('label'=>app::get('b2c')->_('已发货'),'optional'=>false,'filter'=>array('ship_status'=>array('1'),'status'=>'active','disabled'=>'false')),
            4=>array('label'=>app::get('b2c')->_('已完成'),'optional'=>false,'filter'=>array('status'=>'finish','disabled'=>'false')),
            5=>array('label'=>app::get('b2c')->_('已退款'),'optional'=>false,'filter'=>array('pay_status'=>array('4','5'),'status'=>'active','disabled'=>'false')),
            6=>array('label'=>app::get('b2c')->_('已退货'),'optional'=>false,'filter'=>array('ship_status'=>array('3','4'),'status'=>'active','disabled'=>'false')),
            7=>array('label'=>app::get('b2c')->_('已作废'),'optional'=>false,'filter'=>array('status'=>'dead','disabled'=>'false')),
        );
        //新留言订单
        //fix filter condition by danny(数据量大后是性能瓶颈)
        //$filter = array('adm_read_status'=>'false','object_type'=>'order');
        //$orders_num = kernel::single('b2c_message_order')->count($filter);

        $order_id_arr = $this->app->model('orders')->db->select("select order_id from sdb_b2c_member_comments where adm_read_status = 'false' and object_type= 'order'");
        if(is_array($order_id_arr)){
            foreach($order_id_arr as $ok=>$ov){
                $forders['order_id'][] = $ov['order_id'];
            }
        }

        $sub_menu[8] = array('label'=>app::get('b2c')->_('新留言订单'),'optional'=>true,'filter'=>$forders,'addon'=>count($forders['order_id']),'href'=>'index.php?app=b2c&ctl=admin_order&act=index&view=8&view_from=dashboard');

        $mdl_orders = $this->app->model('orders');
        //今日订单
        $today_filter = array(
                    '_createtime_search'=>'between',
                    'createtime_from'=>date('Y-m-d',strtotime('TODAY')),
                    'createtime_to'=>date('Y-m-d'),
                    'createtime' => date('Y-m-d'),
                    '_DTIME_'=>
                        array(
                            'H'=>array('createtime_from'=>'00','createtime_to'=>date('H')),
                            'M'=>array('createtime_from'=>'00','createtime_to'=>date('i'))
                        )
                );
        $today_order = $mdl_orders->count($today_filter);
        $sub_menu[9] = array('label'=>app::get('b2c')->_('今日订单'),'optional'=>true,'filter'=>$today_filter,'addon'=>$today_order,'href'=>'index.php?app=b2c&ctl=admin_order&act=index&view=9&view_from=dashboard');

        //昨日订单
        $date = strtotime('yesterday');
        $yesterday_filter = array(
                    '_createtime_search'=>'between',
                    'createtime_from'=>date('Y-m-d',$date),
                    'createtime_to'=>date('Y-m-d',strtotime('today')),
                    'createtime' => date('Y-m-d',$date),
                    '_DTIME_'=>
                        array(
                            'H'=>array('createtime_from'=>'00','createtime_to'=>date('H',$date)),
                            'M'=>array('createtime_from'=>'00','createtime_to'=>date('i',$date))
                        )
                );
        $yesterday_order = $mdl_orders->count($yesterday_filter);
        $sub_menu[10] = array('label'=>app::get('b2c')->_('昨日订单'),'optional'=>true,'filter'=>$yesterday_filter,'addon'=>$yesterday_order,'href'=>'index.php?app=b2c&ctl=admin_order&act=index&view=10&view_from=dashboard');

        //今日已付款订单
        $today_filter = array_merge($today_filter,array('pay_status'=>'1'));
        $today_payed = $mdl_orders->count($today_filter);
        $sub_menu[11] = array('label'=>app::get('b2c')->_('今日已付款'),'optional'=>true,'filter'=>$today_filter,'addon'=>$today_payed,'href'=>'index.php?app=b2c&ctl=admin_order&act=index&view=11&view_from=dashboard');

        //昨日已付款订单
        $yesterday_filter = array_merge($yesterday_filter,array('pay_status'=>'1'));
        $yesterday_payed = $mdl_orders->count($yesterday_filter);
        $sub_menu[12] = array('label'=>app::get('b2c')->_('昨日已付款'),'optional'=>true,'filter'=>$yesterday_filter,'addon'=>$yesterday_payed,'href'=>'index.php?app=b2c&ctl=admin_order&act=index&view=11&view_from=dashboard');

        //TAB扩展
        foreach(kernel::servicelist('b2c_order_view_extend') as $service){
            if(method_exists($service,'getViews')) {
                $service->getViews($sub_menu);
            }
        }

        if(isset($_GET['optional_view'])) $sub_menu[$_GET['optional_view']]['optional'] = false;


        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array('order_refer'=>'local'),$v['filter']);
                }else{
                    $v['filter'] = array('order_refer'=>'local');
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['addon'] = $mdl_order->count($v['filter']);
                $show_menu[$k]['href'] = 'index.php?app=b2c&ctl=admin_order&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }
    
    /**
     * 添加订单
     * @param null
     * @return null
     */
    public function addnew(){
        $this->pagedata['finder_id'] = $_GET['finder_id'];
        $this->singlepage('admin/order/detail/page.html');
    }
    
    /**
     * 订单创建的第二步
     * @param null
     * @return null
     */
    public function create()
    {
        $order = &$_POST['order'];
        $member_point = 0;
        if (!empty($order['login_name']))
        {
            //$objMember = &$this->app->model('members');
            $obj_pam_account = app::get('pam')->model('account');
            $aUser = $obj_pam_account->dump(array('login_name' => $order['login_name'], 'account_type'=>'member'));
            $order['member_id'] = $aUser['account_id'];
            if (empty($aUser['account_id']))
            {
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('不存在的会员名称！').'",_:null}';
                exit;
            }
            // 得到当前会员的积分
            $member_point = $aUser['score']['total'];
        }
        else
        {
            $aUser['pam_account']['account_id'] = 0;
            $aUser['member_lv']['member_group_id'] = 0;
        }
        $_SESSION['tmp_admin_create_order'] = array();
        $_SESSION['tmp_admin_create_order']['member'] = $aUser;

        if(!$order['product_id']){//todo goods_id为product_id，遗留问题
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('没有购买商品或者购买数量为0!').'",_:null}';
            exit;
        }
        
        $data = array();
        // 生成购物车数据
        $mdl_product = $this->app->model('products');
        foreach($_POST['order']['product_id'] as $product_id)
        {
            $product = $mdl_product->dump($product_id,'*');
            $data['goods'][] = array('goods'=>array(
                'goods_id'=>$product['goods_id'],
                'product_id'=>$product['product_id'],
                'adjunct' => 'na',
                'num' =>$_POST['goodsnum'][$product_id]
            ));
        }
        
        // 购物券数据
        if (isset($_POST['coupon']) && $_POST['coupon'])
        {
            foreach ($_POST['coupon'] as $arr_coupon)
            {
                $data['coupon'][] = array(
                    'coupon'=> $arr_coupon['name'],
                );
            }
        }
        
        $obj_mCart = $this->app->model('cart');
        if ($order['member_id'])
        {
            $member_indent = md5(kernel::single('base_session')->sess_id());
            $data_org = $obj_mCart->get_cookie_cart_arr($member_indent,$order['member_id']);
            if ($data_org)
                $obj_mCart->del_cookie_cart_arr($member_indent);
            
            if ($_COOKIE['orders']['last_member_id'])
            {
                $member_indent = md5($_COOKIE['orders']['last_member_id'] . kernel::single('base_session')->sess_id());
                $data_org = $obj_mCart->get_cookie_cart_arr($member_indent,$order['member_id']);
                
                if ($data_org)
                    $obj_mCart->del_cookie_cart_arr($member_indent);        
            }
            
            setcookie('orders[last_member_id]', $order['member_id']);
            $member_indent = md5($order['member_id'] . kernel::single('base_session')->sess_id());
        }
        else
            $member_indent = md5(kernel::single('base_session')->sess_id());
               
        $obj_mCart->set_cookie_cart_arr($data, $member_indent);            
        $arr_cart_objects = $obj_mCart->get_cart_object($data);
        
        if (!isset($arr_cart_objects['cart_status']) || !$arr_cart_objects['cart_status'] || $arr_cart_objects['cart_status'] == 'true')
        {
            if($aUser['account_id'])
            {
                $member_addrs = &$this->app->model('member_addrs');
                $addrlist = $member_addrs->getList('*',array('member_id'=>$aUser['account_id']));
                
                foreach ($addrlist as $rows)
                {
                    if (empty($rows['tel']))
                    {
                        $str_tel = app::get('b2c')->_('手机：').$rows['mobile'];
                    }
                    else
                    {
                        $str_tel = app::get('b2c')->_('电话：').$rows['tel'];
                    }
                    
                    $addr[] = array(
                        'addr_id'=> $rows['addr_id'],
                        'def_addr'=>$rows['def_addr'],
                        'addr_region'=> $rows['area'],
                        'addr_label'=> $rows['addr'].app::get('b2c')->_(' (收货人：').$rows['name'].' '.$str_tel.app::get('b2c')->_(' 邮编：').$rows['zip'].')'
                    );
                }
                
                $this->pagedata['addrlist'] = $addr;
                $this->pagedata['is_allow'] = (count($addr)<5 ? 1 : 0);
                $this->pagedata['address']['member_id'] = $aUser['account_id'];
            }
            
            $currency = app::get('ectools')->model('currency');
            $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');

            $obj_payments = new ectools_payment_select();
            $sdf_payment = array();
            $this->pagedata['payment_html'] = $obj_payments->select_pay_method($this, $sdf_payment, $order['member_id'], true);
            $this->pagedata['member_id'] = $aUser['account_id'];
            
            // 得到税金的信息
            $this->pagedata['trigger_tax'] = $this->app->getConf("site.trigger_tax");
            $this->pagedata['tax_ratio'] = $this->app->getConf("site.tax_ratio");
            
            $demical = $this->app->getConf('system.money.operation.decimals');
            
            $total_item = $this->objMath->number_minus(array($arr_cart_objects["subtotal"], $arr_cart_objects['discount_amount_prefilter']));
            // 取到商店积分规则
            $policy_method = $this->app->getConf("site.get_policy.method");
            switch ($policy_method)
            {
                case '1':
                    $subtotal_consume_score = 0;
                    $subtotal_gain_score = 0;
                    $totalScore = 0;
                    break;
                case '2':
                    $subtotal_consume_score = round($arr_cart_objects['subtotal_consume_score']);
                    $policy_rate = $this->app->getConf('site.get_rate.method');
                    $subtotal_gain_score = round($this->objMath->number_plus(array(0, $arr_cart_objects['subtotal_gain_score'])));
                    $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                    break;
                case '3':
                    $subtotal_consume_score = round($arr_cart_objects['subtotal_consume_score']);
                    $subtotal_gain_score = round($arr_cart_objects['subtotal_gain_score']);
                    $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                    break;
                default:
                    $subtotal_consume_score = 0;
                    $subtotal_gain_score = 0;
                    $totalScore = 0;
                    break;
            }
            
            $total_amount = $this->objMath->number_minus(array($arr_cart_objects["subtotal"], $arr_cart_objects['discount_amount']));
            // 得到cart total支付的信息
            $this->pagedata['order_detail'] = array(
                'cost_item' => $total_item,
                'total_amount' => $total_amount,
                'currency' => $this->app->getConf('site.currency.defalt_currency'),
                'pmt_amount' => $arr_cart_objects['discount_amount'],
                'totalConsumeScore' => $subtotal_consume_score,
                'totalGainScore' => $subtotal_gain_score,
                'totalScore' => $member_point,
                'cur_code' => $strDefCurrency,
                'cur_display' => $strDefCurrency,
                'cur_rate' => $aCur['cur_rate'],
                'final_amount' => $currency->changer($total_amount, $this->app->getConf("site.currency.defalt_currency"), true),
            );
            
            $odr_decimals = $this->app->getConf('system.money.decimals');
            $total_amount = $this->objMath->get($this->pagedata['order_detail']['total_amount'], $odr_decimals);        
            $this->pagedata['order_detail']['discount'] = $this->objMath->number_minus(array($this->pagedata['order_detail']['total_amount'], $total_amount));
            $this->pagedata['order_detail']['total_amount'] = $total_amount;
            $this->pagedata['order_detail']['current_currency'] = $strDefCurrency;
        }
        else
        {
            $this->pagedata['cart_error_html'] = $arr_cart_objects['cart_error_html'];
        }
        $this->pagedata['finder_id'] = $_POST['finder_id'];
        $this->pagedata['cart_status'] = (!isset($arr_cart_objects['cart_status']) || !$arr_cart_objects['cart_status'] || $arr_cart_objects['cart_status'] == 'true') ? true : false;
        $this->display('admin/order/order_create.html');
    }
    
    public function getAddr()
    {
        $obj_addr = new b2c_member_addrs();
        $addr_id = intval($_GET['addr_id']);
        $member_id = intval($_GET['member_id']);
        echo $obj_addr->get_receive_addr($this,$addr_id,$member_id,'admin/order/rec_addr.html');exit;
    }
    
    /**
     * 打印选定订单
     * @param null
     * @return null
     */
    public function toprint()
    {
        $objOrder = $this->app->model('orders');
        
        if ($_POST['order_id'])
        {
            if (is_array($_POST['order_id']))
                $aInput = $_POST['order_id'];
            else
                $aInput = array($_POST['order_id']);
        }
        elseif ($orderid)
        {
            $aInput = array($orderid);
        }
        elseif ($_POST['isSelectedAll'] == '_ALL_')
        {
            $arr_idColumns = $objOrder->getList($objOrder->idColumn);
            if ($arr_idColumns)
            {
                foreach ($arr_idColumns as $_order_id)
                {
                    $aInput[] = $_order_id['order_id'];
                }
            }
        }
        else
        {
            $this->begin('index.php?app=b2c&ctl=admin_order&act=index');
            $this->end(false, app::get('b2c')->_('打印失败：订单参数传递出错'));
            exit();
        }

        $oCur = app::get('ectools')->model('currency');


        $dbTmpl = $this->app->model('member_systmpl');
        foreach ($aInput as $orderid)
        {
            $aData = array();
            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $orderInfo = $objOrder->dump($orderid, '*', $subsdf);#print_r($orderInfo);exit;
            // 打印订单添加html埋点
            foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            	if ( is_object($services) ) {
            		if ( method_exists($services, 'fetchHtml') ) {
            			$services->fetchHtml($this,$orderid,'admin/invoice_print.html');
            		}
            	}
            }
            $aData = $orderInfo;
            $aCur = $oCur->getcur($aData['currency']);            
            $aData['cur_name'] = $aCur['cur_name'];

            $objMember = $this->app->model('members');
            $aMember = $objMember->dump($orderInfo['member_id'], '*', array(':account@pam'=>'*'));
            $aData['member'] = $aMember;
            $payment = app::get('ectools')->model('payment_cfgs');
            $aPayment = $payment->getPaymentInfo($aData['payinfo']['pay_app_id']);#print_r($orderInfo);exit;
            $aData['payment'] = $aPayment['app_name'];

            $aData['shopname'] = app::get('site')->getConf('site.name');
            $aData['shopaddress'] = $this->app->getConf('store.address');
            $aData['shoptelphone'] = $this->app->getConf('store.telephone');
            $aData['shopzip'] = $this->app->getConf('store.zip_code');
            #$aItems = $objOrder->getItemList($orderid);
            #$aItems = $orderInfo;
            /*
            foreach($aItems as $k => $rows){
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
            $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
            $aItems[$k]['minfo'] = array();
            }
            if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= app::get('b2c')->_('<br>配件：').$aItems[$k]['addon']['adjname'];
            $aItems[$k]['catname'] = $objOrder->getCatByPid($rows['product_id']);
            }*/
            #$aData['goodsItems'] = $orderInfo['order_objects'];
            $goods = $this->app->model('goods');
            $goods_cat = $this->app->model('goods_cat');
            foreach ((array)$orderInfo['order_objects'] as $val)
            {
                foreach ( $val['order_items'] as $v)
                {
                    if ($v['item_type'] != 'gift')
                    {
                        $cat_id = $goods->dump($v['goods_id'],'cat_id');
                        $arrcat_name = $goods_cat->dump($cat_id['category']['cat_id'],'cat_name');
                        $v['catname'] = $arrcat_name['cat_name']?$arrcat_name['cat_name']:'---';
                    }
                    
                    $v['addon'] = unserialize($v['addon']);
                    if ($v['addon']['product_attr'])
                    {
                        $v['name'] .= '(';
                        foreach ($v['addon']['product_attr'] as $arr_product_attr)
                        {
                            $v['name'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                        }
                        if (strpos($v['name'], $this->app->_(" ")) !== false)
                        {
                            $v['name'] = substr($v['name'], 0, strrpos($v['name'], $this->app->_(" ")));
                        }
                        $v['name'] .= ')';
                    }
                    
                    if ($v['item_type'] === 'gift')
                    {
                        $row = $goods->getList('params',array('goods_id' => $v['goods_id'],'goods_type' => 'gift'));
                        $v['point'] = $row[0]['params']['consume_score']?$row[0]['params']['consume_score']:0;
                        $aData['giftItems'][] = $v;
                    }
                    elseif ($v['item_type'] === 'adjunct')
                    {
                        $v['name'] = '<br>'.app::get('b2c')->_('配件：').$v['name'].'('.$v['products']['spec_info'].')'; 
                        $aData['goodsItems'][] = $v;
                    }
                    else 
                        $aData['goodsItems'][] = $v;
                }

            }
            $this->pagedata['pages'][] = $dbTmpl->fetch('admin/order/orderprint',array('order'=>$aData));
        }
        $this->pagedata['shopname'] = $aData['shopname'];

        $this->display('admin/order/print_order.html');
    }
    
    /**
     * 打印订单的接口
     * @param string 打印类型
     * @param string order id
     * @return null
     */
    public function printing($type,$order_id)
    {
        $order = &$this->app->model('orders');
        $member = &$this->app->model('members');
        //$order->setPrintStatus($order_id,$type,true);
        
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $orderInfo = $order->dump($order_id, '*', $subsdf);
        $orderInfo['self'] = $this->objMath->number_minus(array(0, $orderInfo['discount'], $orderInfo['pmt_goods'], $orderInfo['pmt_order']));
        
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }
        
        $memberInfo = $member->getList('*', array('member_id'=>$orderInfo['member_id']));
        $order_items = array();
        $gift_items = array(); 
        foreach ($orderInfo['order_objects'] as $k=>$v)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            if ($v['obj_type'] == 'goods')
            {
                foreach ($v['order_items'] as $key => $item)
                {
                    if (!$item['products'])
                    {
                        $o = $this->app->model('order_items');
                        $tmp = $o->getList('*', array('item_id'=>$item['item_id']));
                        $item['products']['product_id'] = $tmp[0]['product_id'];
                    }
                        
                    if ($item['item_type'] != 'gift')
                    {
                        if ($item['item_type'] == 'product')
                            $item['item_type'] = 'goods';
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$item['item_type']];
                        $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product'=>$item['products']['product_id']), $arrGoods, 'admin_order_printing');
                    
                        $gItems[$k]['addon'] = unserialize($item['addon']);
                        
                        if ($item['addon'] && unserialize($item['addon']))
                        {
                            $gItems[$k]['minfo'] = unserialize($item['addon']);
                        }
                        else
                        {
                            $gItems[$k]['minfo'] = array();
                        }
                        
                        if ($item['item_type'] == 'goods')
                        {  
                            $order_items[$k] = $item;
                            $order_items[$k]['small_pic'] = $arrGoods['image_default_id'] ? $arrGoods['image_default_id'] : '';
                            $order_items[$k]['is_type'] = $v['obj_type'];
                            $order_items[$k]['item_type'] = ($arrGoods) ? $arrGoods['category']['cat_name'] : '';
                            $order_items[$k]['minfo'] = $gItems[$k]['minfo'];
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
                                    $order_items[$k]['name'] = substr($order_items[$k]['name'], 0, strrpos($order_items[$k]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['name'] .= ')';
                                }
                            }                        
                        }
                        else
                        {
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
                                    $order_items[$k]['adjunct'][$index_adj]['name'] = substr($$order_items[$k]['adjunct'][$index_adj]['name'], 0, strpos($order_items[$k]['adjunct'][$index_adj]['name'], app::get('b2c')->_('、')));
                                    $order_items[$k]['adjunct'][$index_adj]['name'] .= ')';
                                }
                            }
                            
                            $index_adj++;
                        }
                    }
                    else
                    {
                        $str_service_goods_type_obj = $arr_service_goods_type_obj[$item['item_type']];
                        $str_service_goods_type_obj->get_order_object(array('goods_id' => $item['goods_id'],'product'=>$item['products']['product_id']), $arrGoods, 'admin_order_printing');
                        
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
                            if (isset($gift_items[$gift_item['goods_id']]) && $gift_items[$gift_item['goods_id']])
                                $gift_items[$gift_item['goods_id']]['nums'] = $this->objMath->number_plus(array($gift_items[$gift_item['goods_id']]['nums'], $gift_item['quantity']));
                            else
                            {
                                if (!$gift_item['products'])
                                {
                                    $o = $this->app->model('order_items');
                                    $tmp = $o->getList('*', array('item_id'=>$gift_item['item_id']));
                                    $gift_item['products']['product_id'] = $tmp[0]['product_id'];
                                }
                                
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $gift_item['goods_id'],'product'=>$gift_item['products']['product_id']), $arrGoods, 'admin_order_printing');
                                
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
                                    
                                $gift_items[$gift_item['goods_id']] = array(
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
                                );
                            }
                        }
                    }
                }
                else
                {
                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$v['obj_type']];
                    $str_service_goods_type_obj->get_order_object($v, $arr_Goods, 'admin_order_printing');
                    
                    if (is_array($arr_Goods) && $arr_Goods)
                    {
                        foreach ($arr_Goods as $arr)
                            $extend_items[$v['obj_type']][$arr['goods_id']] = array(
                                    'goods_id' => $arr['goods_id'],
                                    'bn' => $arr['bn'],
                                    'nums' => $arr['quantity'],
                                    'name' => $arr['name'],
                                    'item_type' => $arr['category']['cat_name'],
                                    'price' => $arr['price'],
                                    'quantity' => $arr['quantity'],
                                    'sendnum' => $arr['sendnum'],
                                    'small_pic' => $arr['image_default_id'],
                                    'is_type' => $arr['obj_type'],
                                );
                    }
                }
            }
        }
        
        $order_sum = $this->sum_order($orderInfo['member_id']);
        $this->pagedata['goodsItem'] = $order_items;#print_r($order_items);exit;
        $this->pagedata['giftsItem'] = $gift_items;
        $this->pagedata['extend_items'] = $extend_items;
        $orderInfo['consignee']['telephone'] = $orderInfo['consignee']['telephone'] ? $orderInfo['consignee']['telephone'] :$orderInfo['consignee']['mobile'];
        $this->pagedata['orderInfo'] = $orderInfo;
        $this->pagedata['orderSum'] = $order_sum;
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['memberPoint'] = $memberInfo[0]['point'] ? $memberInfo[0]['point'] : 0;
        $this->pagedata['storeplace_display_switch'] = $this->app->getConf('storeplace.display.switch');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $logo_id = app::get('b2c')->getConf('site.logo');
        $this->pagedata['logo_image'] = base_storager::image_path($logo_id);
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['shop'] = array(
            'name'=>app::get('site')->getConf('site.name'),
            'url'=>kernel::base_url(true),
            'email'=>$this->app->getConf('store.email'),
            'tel'=>$this->app->getConf('store.telephone'),
            'logo'=>$this->app->getConf('site.logo')
        );
        $this->_systmpl = &$this->app->model('member_systmpl');
        switch($type)
        {
            case $order->arr_print_type['ORDER_PRINT_CART']:  /*购物清单*/
                $this->pagedata['printType'] = array("cart");
                $this->pagedata['printContent']['cart'] = true;
                $this->pagedata['memberPoint'] = $memberInfo[0]['point'] ? $memberInfo[0]['point'] : 0;
                $this->pagedata['content_cart'] = $this->_systmpl->fetch('admin/order/print_cart',$this->pagedata);
                $this->pagedata['page_title'] = app::get('b2c')->_('购物单打印');
                $this->display('admin/order/print.html');
                break;

            case $order->arr_print_type['ORDER_PRINT_SHEET']:    /*配货单*/
                $this->pagedata['printContent']['sheet'] = true;
                $this->pagedata['memberPoint'] = $memberInfo[0]['point'] ? $memberInfo[0]['point'] : 0;
                $this->pagedata['content_sheet'] = $this->_systmpl->fetch('admin/order/print_sheet',$this->pagedata);
                $this->pagedata['page_title'] = app::get('b2c')->_('配货单打印');
                $this->display('admin/order/print.html');
                break;

            case $order->arr_print_type['ORDER_PRINT_MERGE']:    /*联合打印*/
                $this->pagedata['printType'] = array("cart");
                $this->pagedata['printContent']['cart'] = true;
                $this->pagedata['printContent']['sheet'] = true;
                $this->pagedata['memberPoint'] = $memberInfo[0]['point'] ? $memberInfo[0]['point'] : 0;
                $this->pagedata['content_cart'] = $this->_systmpl->fetch('admin/order/print_cart',$this->pagedata);
                $this->pagedata['content_sheet'] = $this->_systmpl->fetch('admin/order/print_sheet',$this->pagedata);
                $this->pagedata['page_title'] = app::get('b2c')->_('联合打印');
                $this->display('admin/order/print.html');
                break;

            case $order->arr_print_type['ORDER_PRINT_DLY']:    /*快递单打印*/
                $printer = &app::get('express')->model('dly_center');
                $this->pagedata['dly_centers'] = $printer->getList('dly_center_id,name',array('disable'=>'false'));
                $this->pagedata['default_dc'] = $this->app->getConf('system.default_dc');
                $this->pagedata['the_dly_center'] = $printer->dump($this->pagedata['default_dc']?$this->pagedata['default_dc']:$this->pagedata['dly_centers'][0]['dly_center_id']);
                $this->pagedata['printContent']['express'] = true;
                $printer = &app::get('express')->model('print_tmpl');
                $this->pagedata['printers'] = $printer->getList('prt_tmpl_id,prt_tmpl_title',array('shortcut'=>'true'));
                $this->pagedata['type'] = 'ORDER_PRINT_DLY';
                $this->pagedata['order_status'] = $orderInfo['status']; 

                $this->singlepage('admin/order/detail/printer.html');
                break;
            default:
                echo app::get('b2c')->_('无效的打印类型');
                break;
        }
    }
    
    /**
     * 求出同一个会员对应订单的总额
     * @param string member id
     * @return array 订单数组
     */
    public function sum_order($member_id=null)
    {
        $obj_order = $this->app->model('orders');
        $aData = $obj_order->getList('total_amount',array('member_id' => $member_id));
        if($aData){
            $row['sum'] = count($aData);
            $row['sum_pay'] = 0;
            foreach($aData as $val){
                $row['sum_pay'] = $row['sum_pay']+$val['total_amount'];
            }
        }
        else{
            $row['sum'] = 0;
            $row['sum_pay'] = 0;
        }
        return $row;
    }
    
    /**
     * 保存订单的收货地址
     * @param string order id
     * @return null
     */
    public function save_addr($order_id)
    {
        $obj_order = $this->app->model('orders');
        $arr_order = $obj_order->dump($order_id);
        
        $arr_order['consignee']['name'] = $_POST['order']['ship_name'];
        $arr_order['consignee']['area'] = $_POST['order']['ship_area'];
        $arr_order['consignee']['zip'] = $_POST['order']['ship_zip'];
        $arr_order['consignee']['addr'] = $_POST['order']['ship_addr'];
        $arr_order['consignee']['mobile'] = $_POST['order']['ship_mobile'];
        $arr_order['consignee']['telephone'] = $_POST['order']['ship_tel'];
        $arr_order['consignee']['memo'] = $_POST['order']['order_memo'];
        
        if($obj_order->save($arr_order)){
            echo 'ok';
        }
    }

    /**
     * 产生支付页面
     * @params string order id
     * @return string html
     */
    public function gopay($order_id)
    {
        if (!$order_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("订单号传递出错.").'",_:null}';exit;
        }
        
        $this->pagedata['orderid'] = $order_id;
        $objOrder = &$this->app->model('orders');
        $aORet = $objOrder->dump($order_id);

        $this->pagedata['op_name'] = 'admin';
        $this->pagedata['typeList'] = array('online'=>app::get('b2c')->_("在线支付"), 'offline'=>app::get('b2c')->_("线下支付"));
        $this->pagedata['pay_type'] = ($aPayid['pay_type'] == 'ADVANCE' ? 'deposit' : 'offline');
        // 此时为支付状态
        $this->pagedata['bill_type'] = "payments";

        if ($aORet['member_id'] > 0)
        {
            $objPayments = &app::get('ectools')->model('payments');
            $aRet = $objPayments->getAccount();
            $this->pagedata['member'] = $aRet;
        }
        else 
        {
            $this->pagedata['member'] = array();
        }
        $_minus = array($aORet['cur_amount'],$aORet['payed']);
        $aORet['require_pay'] = $this->objMath->number_minus($_minus);
        $this->pagedata['order'] = $aORet;
        $aAccount = array(app::get('b2c')->_('--使用已存在帐户--'));
        if (isset($aRet) && $aRet)
        {
            foreach ($aRet as $account_info)
            {
                $str_bank = $account_info['bank'] ? $account_info['bank'] : '0';
                $str_account = $account_info['account'] ? $account_info['account'] : '0';
                $aAccount[$str_bank."-".$str_account] = $str_bank." - ".$str_account;
            }
        }
        
        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payment'] = $opayment->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));
        if (!$aORet['member_id'])
        {
            if ($this->pagedata['payment'])
            {
                foreach ($this->pagedata['payment'] as $key=>$arr_payments)
                {
                    if (trim($arr_payments['app_id']) == 'deposit')
                    {
                        unset($this->pagedata['payment'][$key]);
                    }
                }
            }
        }
        $this->pagedata['pay_account'] = $aAccount;

        $this->display('admin/order/gopay.html');
    }
    
    /**
     * 订单开始支付
     * @params null
     * @return null
     */
    public function dopay()
    {
        $sdf = $_POST;
        
        //todo 生产sdf
        $objOrders = $this->app->model('orders');
        $sdf_order = $objOrders->dump($sdf['order_id'], '*');
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_pay($sdf['order_id'],$sdf,$message))
        {   
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.$message.'",_:null}';exit;
            //$this->end(false, $message);            
        }
        
        $objPay = kernel::single("ectools_pay");
        $payment_id = $sdf['payment_id'] = $objPay->get_payment_id();        
        
        $arrOperations = array(
            'op_id' => $sdf['op_id'],
            'op_name' => $sdf['op_name'],
        );
        
        if (!isset($sdf['payment']) || !$sdf['payment'])
        {
            $sdf['pay_app_id'] = $sdf_order['payinfo']['pay_app_id'];
            
            $cost_payments_rate = $this->objMath->number_div(array($sdf['money'], $sdf_order['total_amount']));
            $cost_payment = $this->objMath->number_multiple(array($sdf_order['payinfo']['cost_payment'], $cost_payments_rate));
        }
        else
        {
            $sdf['pay_app_id'] = $sdf['payment'];
            
            $cost_payments_rate = $this->objMath->number_div(array($sdf['money'], $sdf_order['total_amount']));
            $cost_payment = $this->objMath->number_multiple(array($sdf_order['payinfo']['cost_payment'], $cost_payments_rate));
        }
        
        $sdf['currency'] = $sdf_order['currency'];
        $sdf['payinfo']['cost_payment'] = $cost_payment;
                
        $sdf['pay_object'] = 'order';
        $sdf['rel_id'] = $sdf['order_id'];
        $sdf['op_id'] = $this->user->user_id;
        $sdf['member_id'] = $sdf_order['member_id'];
        $sdf['op_name'] = $this->user->user_data['account']['login_name'];
        $sdf['status'] = 'ready';   
        $sdf['cur_money'] = $sdf['money'];
        $sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
        
        $time = time();
        
        $is_payed = $objPay->gopay($sdf, $msg);
        if (!$is_payed)
        {
            eval("\$msg = \"$msg\";");
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.$msg.'",_:null}';exit;
            //$this->end(false, $msg); 
        }
        
        // 订单的处理
        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        $obj_pay_lists = kernel::servicelist("order.pay_finish");
        $is_payed = false;
        foreach ($obj_pay_lists as $order_pay_service_object)
        {            
            $is_payed = $order_pay_service_object->order_pay_finish($sdf, 'succ', 'Back');
        }
        
        // 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
        if (!$is_payed)
        {
            $db->rollback();
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("此次订单支付失败！").'",_:null}';exit;
        }
        $db->commit($transaction_status);
        
        foreach ($obj_pay_lists as $order_pay_service_object)
        {            
            $is_payed = $order_pay_service_object->order_pay_finish_extends($sdf);
        }

        header('Content-Type:text/jcmd; charset=utf-8');
        echo '{success:"'.app::get('b2c')->_("此次订单支付成功！.").'",_:null,order_id:"'.$_POST['order_id'].'"}';exit;
    }
    
    /**
     * 生成退款单页面
     * @params string order id
     * @return string html
     */
    public function gorefund($order_id)
    {
        if (!$order_id)
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_("订单号传递出错.").'",_:null}';exit;
        }
        
        $this->pagedata['orderid'] = $order_id;
        $objOrder = &$this->app->model('orders');
        $aORet = $objOrder->dump($order_id);
        
        $this->pagedata['payment_id'] = $aORet['payment'];
        $this->pagedata['op_name'] = 'admin';
        
        if ($aORet['member_id'])
            $this->pagedata['typeList'] = array('online'=>app::get('b2c')->_("在线支付"), 'offline'=>app::get('b2c')->_("线下支付"), 'deposit'=>app::get('b2c')->_("预存款支付"));
        else
            $this->pagedata['typeList'] = array('online'=>app::get('b2c')->_("在线支付"), 'offline'=>app::get('b2c')->_("线下支付"));
            
        $this->pagedata['pay_type'] = ($aPayid['pay_type'] == 'ADVANCE' ? 'deposit' : 'offline');

        if ($aORet['member_id'] > 0)
        {
            $objPayments = &app::get('ectools')->model('refunds');
            $aRet = $objPayments->getAccount();
            $this->pagedata['member'] = $aRet;
        }
        else 
        {
            $this->pagedata['member'] = array();
        }
        $this->pagedata['order'] = $aORet;

        $aAccount = array(app::get('b2c')->_('--使用已存在帐户--'));
        if (isset($aRet) && $aRet)
        {
            foreach ($aRet as $v){
                $aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
            }
        }
        $this->pagedata['pay_account'] = $aAccount;
        
        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payment'] = $opayment->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));
        if (!$aORet['member_id'])
        {
            if ($this->pagedata['payment'])
            {
                foreach ($this->pagedata['payment'] as $key=>$arr_payments)
                {
                    if (trim($arr_payments['app_id']) == 'deposit')
                    {
                        unset($this->pagedata['payment'][$key]);
                    }
                }
            }
        }
        
        $obj_members_point = $this->app->model('member_point');
        $reasons = $obj_members_point->getHistoryReason();
        $arr_return_score = $obj_members_point->db->select("SELECT * FROM ".$obj_members_point->table_name(1)." WHERE member_id=".$aORet['member_id']." AND related_id='".$aORet['order_id']."' AND type='".$reasons['order_refund_use']['type']."' AND reason='".$reasons['order_refund_use']['describe']."'");
        $is_returned_score = 0;
        foreach ((array)$arr_return_score as $arr_is_returned){
            $is_returned_score += abs($arr_is_returned['change_point']);
        }
        
        // 退还订单消费积分
        $this->pagedata['order']['score_g'] = $aORet['score_g'] - $aORet['score_u'] - $is_returned_score;

        $this->display('admin/order/gorefund.html');
    }
    
    /**
     * 退款处理
     * @params null
     * @return null
     */
    public function dorefund()
    {
        if(!$order_id) $order_id = $_POST['order_id'];
        else $_POST['order_id'] = $order_id;

        $sdf = $_POST;        
        $this->begin();
       
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_refund($sdf['order_id'],$sdf,$message))
        {
             $this->end(false, $message);
        }

        $obj_order = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $obj_order->dump($sdf['order_id'],'*',$subsdf);

        if (!$sdf['money'])
        {
            //退款金额不是从弹出的退款单里输入而来
            $sdf['money'] = $sdf_order['payed'];
            $sdf['return_score'] = $sdf_order['score_g'];
        }

        $refunds = app::get('ectools')->model('refunds');
        $objOrder->op_id = $this->user->user_id;
        $objOrder->op_name = $this->user->user_data['account']['name'];
        $sdf['op_id'] = $this->user->user_id;
        $sdf['op_name'] = $this->user->user_data['account']['login_name'];
        $sdf['status'] = 'succ';
        unset($sdf['inContent']);
        
        $objPaymemtcfg = app::get('ectools')->model('payment_cfgs');
        $sdf['payment'] = ($sdf['payment']) ? $sdf['payment'] : $sdf_order['payinfo']['pay_app_id'];
        if ($sdf['payment'] == '-1')
        {
            $arrPaymentInfo['app_name'] = app::get('b2c')->_("货到付款");
            $arrPaymentInfo['app_version'] = "1.0";
        }
        else
            $arrPaymentInfo = $objPaymemtcfg->getPaymentInfo($sdf['payment']);
            
        $time = time();
        $sdf['refund_id'] = $refund_id = $refunds->gen_id();
        $sdf['pay_app_id'] = $sdf['payment'];
        $sdf['member_id'] = $sdf_order['member_id'] ? $sdf_order['member_id'] : 0;
        $sdf['currency'] = $sdf_order['currency'];
        $sdf['paycost'] = 0;
        $sdf['cur_money'] = $sdf['money'];
        $sdf['money'] = $this->objMath->number_div(array($sdf['cur_money'], $sdf_order['cur_rate']));
        $sdf['t_begin'] = $time;
        $sdf['t_payed'] = $time;
        $sdf['t_confirm'] = $time;
        $sdf['pay_object'] = 'order';
        $sdf['op_id'] = $this->user->user_id;
        $sdf['op_name'] = $this->user->user_data['account']['login_name'];
        $sdf['status'] = 'ready';
        $sdf['app_name'] = $arrPaymentInfo['app_name'];
        $sdf['app_version'] = $arrPaymentInfo['app_version'];
           
        $obj_refunds = kernel::single("ectools_refund");
        if ($obj_refunds->generate($sdf, $this, $msg))
        {            
            $is_refund_finished = false;
            $obj_refund_lists = kernel::servicelist("order.refund_finish");
            foreach ($obj_refund_lists as $order_refund_service_object)
            {                
                $is_refund_finished = $order_refund_service_object->order_refund_finish($sdf, 'succ', 'Back',$msg);
            }
            
            if ($is_refund_finished)
            {
                // 发送同步日志.
                $order_refund_service_object->send_request($sdf);

                //ajx crm
                $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
                $req_arr['order_id']=$sdf['order_id'];
                $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

                $this->end(true, app::get('b2c')->_('退款成功'));
            }
            else
            {
                $this->end(false, $msg);
            }
        }
        else
        {
            $this->end(false, $msg);
        }
    }
    
    /**
     * 产生订单发货页面
     * @params string order id
     * @return string html
     */
    public function godelivery($order_id)
    {
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

        $dlycorp = $this->app->model('dlycorp');
        $this->pagedata['corplist'] = $dlycorp->getList('*');
        $this->pagedata['order'] = $aORet;
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
        
        $this->pagedata['items'] = $arrItems;
        $this->pagedata['giftItems'] = $gift_items;
        $this->pagedata['extendsItems'] = $extends_items;
        // 得到物流公司的信息
        $objDlytype = $this->app->model('dlytype');
        $arrDlytype = $objDlytype->dump($this->pagedata['order']['shipping']['shipping_id']);
        $this->pagedata['corp_id'] = $arrDlytype['corp_id'];
        
        $this->display('admin/order/godelivery.html');
    }
    
    /**
     * 发货订单处理
     * @params null
     * @return null
     */
    public function dodelivery()
    {
        $obj_order = &$this->app->model('orders');
        if(!$order_id) $order_id = $_POST['order_id'];
        else $_POST['order_id'] = $order_id;
        
        $sdf = $_POST;

        $sdf['opid'] = $this->user->user_id;
        $sdf['opname'] = $this->user->user_data['account']['login_name'];
        $this->begin();
        
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

            $this->end(true, app::get('b2c')->_('发货成功'));
        }
        else
        {
            $this->end(false, $message);
        }
    }
    
    /**
     * 订单退货页面
     * @params stirng orderid
     * @return string html
     */
    public function goreship($order_id)
    {
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
        
        if (isset($order_items) && $order_items)
        {
            foreach ($order_items as &$arrOdrItems)
            {
                $good_id = $arrOdrItems['products']['goods_id'];
                $product_id = $arrOdrItems['products']['product_id'];
                $arrAddon = unserialize($arrOdrItems['addon']);
                
                if (isset($arrOdrItems['products']['spec_info']) && $arrOdrItems['products']['spec_info'])
                {
                    $arrOdrItems['products']['name'] = $arrOdrItems['products']['name'] . '(' . $arrOdrItems['products']['spec_info'] . ')';
                }
                
                if (!$arrItems[$product_id])
                    $arrItems[$product_id] = array(
                        'bn' => $arrOdrItems['bn'],
                        'name' => $arrOdrItems['name'],
                        'minfo' => $arrAddon,
                        'addon' => $arrAddon,
                        'products' => array(
                            'name' => $arrOdrItems['products']['name'] ? $arrOdrItems['products']['name'] : $arrOdrItems['name'],
                            'store' => $arrOdrItems['products']['store'] ? $arrOdrItems['products']['store'] : $arrOdrItems['store'],
                        ),
                        'quantity' => $arrOdrItems['quantity'],
                        'sendnum' => $arrOdrItems['sendnum'],
                        'product_id' => $product_id,
                        'item_id' => $arrOdrItems['item_id'],
                        'needsend' => $this->objMath->number_minus(array($arrOdrItems['quantity'], $arrOdrItems['sendnum'])),
                    );
                else{
                    $arrItems[$product_id]['sendnum'] = $this->objMath->number_plus(array($arrItems[$product_id]['sendnum'],$arrOdrItems['sendnum']));
                    $arrItems[$product_id]['quantity'] = $this->objMath->number_plus(array($arrItems[$product_id]['quantity'],$arrOdrItems['quantity']));
                    $arrItems[$product_id]['needsend'] = $this->objMath->number_plus(array($arrItems[$product_id]['needsend'],$this->objMath->number_minus(array($arrOdrItems['quantity'], $arrOdrItems['sendnum']))));
                }
            }
        }

        $this->pagedata['order'] = $aORet;
        $this->pagedata['order']['protectArr'] = array('false'=>app::get('b2c')->_('否'), 'true'=>app::get('b2c')->_('是'));
        $shippings = $this->app->model('dlytype');
        $this->pagedata['shippings'] = $shippings->getList('*');
        $dlycorp = $this->app->model('dlycorp');
        $this->pagedata['corplist'] = $dlycorp->getList('*');
        $this->pagedata['items'] = $arrItems;
        
        // 得到物流公司的信息
        $objDlytype = $this->app->model('dlytype');
        $arrDlytype = $objDlytype->dump($this->pagedata['order']['shipping']['shipping_id']);
        $this->pagedata['order']['shipping']['corp_id'] = $arrDlytype['corp_id'];
        $objDelivery = $this->app->model('delivery');
        $arrDeliverys = $objDelivery->getList('*', array('order_id' => $order_id));
        $this->pagedata['order']['shipping']['cost_shipping'] = '0';
        
        foreach ($arrDeliverys as $arrDeliveryInfo)
        {
            $this->pagedata['order']['shipping']['cost_shipping'] = $this->objMath->number_plus(array($this->pagedata['order']['shipping']['cost_shipping'], $arrDeliveryInfo['money']));
        }

        $this->display('admin/order/goreship.html');
    }
    
    /**
     * 订单退货
     * @params null
     * @return null
     */
    public function doreship()
    {
        if(!$order_id) $order_id = $_POST['order_id'];
        else $_POST['order_id'] = $order_id;

        $sdf = $_POST;
        
        $this->begin();
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        
        if (!$obj_checkorder->check_order_reship($sdf['order_id'],$sdf,$message))
        {
            $this->end(false, $message);
        }
        
        $sdf['op_id'] = $this->user->user_id;
        $sdf['opname'] = $this->user->user_data['account']['login_name'];
        $reship = &$this->app->model('reship');
        $sdf['reship_id'] = $reship->gen_id();
        $reship->op_id = $this->user->user_id;
        $reship->op_name = $this->user->user_data['account']['login_name'];
        
        
        // 处理支付单据.
        $b2c_order_reship = b2c_order_reship::getInstance($this->app, $reship);
        if ($b2c_order_reship->generate($sdf, $this, $message))
        {
            //ajx crm 
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$sdf['order_id'];
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

            $this->end(true, app::get('b2c')->_('退货成功'));
        }
        else
        {
            $this->end(false, $message);
        }
    }
    
    /**
     * 订单取消
     * @params string order id
     * @return null
     */
    public function docancel($order_id)
    {
        $this->begin();
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_cancel($order_id,'',$message))
        {
           $this->end(false, $message);
        }
        
        $sdf['order_id'] = $order_id;
        $sdf['op_id'] = $this->user->user_id;
        $sdf['opname'] = $this->user->user_data['account']['login_name'];
        
        $b2c_order_cancel = kernel::single("b2c_order_cancel");
        if ($b2c_order_cancel->generate($sdf, $this, $message))
        {
            //ajx crm
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $req_arr['order_id']=$order_id;
            $obj_apiv->rpc_caller_request($req_arr, 'orderupdatecrm');

            $this->end(true, app::get('b2c')->_('订单取消成功！'));
        }
        else
        {
            $this->end(false, app::get('b2c')->_('订单取失败！'));
        }
    }
    
    /**
     * 订单取消
     * @params string order id
     * @return null
     */
    public function dodelete($order_id)
    {
        $this->begin();
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->checkstatus($order_id,'delete','',$message))
        {
           $this->end(false, $message);
        }
        
        $obj_recycle = kernel::single('desktop_system_recycle');
        $filter = array(
            'order_id'=>$order_id,
        );
        if (!$obj_recycle->dorecycle('b2c_mdl_orders', $filter))
        {
            $this->end(false, app::get('b2c')->_('订单删除失败！'));
        }
        else
        {
            $this->end(true, app::get('b2c')->_('订单删除成功！'));
        }
    }
    
    /**
     * 订单完成
     * @params string oder id
     * @return boolean 成功与否
     */
    public function dofinish($order_id)
    {
        $this->begin();
        
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        if (!$obj_checkorder->check_order_finish($order_id,'',$message))
        {
            $this->end(false, $message);
        }
        
        $sdf['order_id'] = $order_id;
        $sdf['op_id'] = $this->user->user_id;
        $sdf['opname'] = $this->user->user_data['account']['login_name'];
        
        $objOrder = &$this->app->model('orders');
        
        $b2c_order_finish = kernel::single("b2c_order_finish");
        if ($b2c_order_finish->generate($sdf, $this, $message))
        {
            $this->end(true, app::get('b2c')->_('完成订单成功！'));
        }
        else
        {
            $this->end(false, app::get('b2c')->_('完成订单失败！'));
        }
    }
    
    /**
     * 订单备注添加，修改
     * @param null
     * @return null
     */
    public function saveMarkText()
    {
        $msg = "";
        
        $obj_order_remark = kernel::single("b2c_order_remark");
        $_POST['op_name'] = $this->user->user_data['account']['login_name'];
        $is_success = $obj_order_remark->update($_POST, $msg);
        
        if ($is_success)
        {
            $order = $this->app->model('orders');
            $arr_order = $order->getList('*', array('order_id'=>$_POST['orderid']));
            $str_html = "";
            if ($arr_order[0])
            {
                if ($arr_order[0]['mark_text'])
                {
                    $arr_order[0]['mark_text'] = unserialize($arr_order[0]['mark_text']);
                    if ($arr_order[0]['mark_text'])
                    {
                        $this->pagedata['mark_text'] = $arr_order[0]['mark_text'];
                        $str_html = $this->fetch('admin/order/od_mark_item.html');
                    }
                }
            } 
            header('Content-Type:text/jcmd; charset=utf-8');
            echo $str_html;exit;
        }
        else
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo 'error';exit;
        }
    }
    
    /**
     * 查找相对应支付方式
     * @param null
     * @return null
     */
    public function shipping()
    {
        $area_id = ($_POST['area']);
        $obj_delivery = new b2c_order_dlytype();
        $sdf = array();
        
        $member_indent = md5($_POST['member_id'] . kernel::single('base_session')->sess_id());
        $obj_mCart = $this->app->model('cart');
        if ($_POST['member_id'])
            $data = $obj_mCart->get_cookie_cart_arr($member_indent,$_POST['member_id']); 
        else
            $data = $obj_mCart->get_cookie_cart_arr($member_indent);
        $arr_cart_objects = $obj_mCart->get_cart_object($data);        
        
        echo $obj_delivery->select_delivery_method($this,$area_id,$arr_cart_objects,'','admin/order/checkout_shipping.html');
    }
    
    /**
     * 计算订单总计信息
     * @param null
     * @return null
     */
    public function total()
    {            
        $obj_mCart = $this->app->model('cart');
        if ($_POST['member_id'])
        {
            $member_indent = md5($_POST['member_id'] . kernel::single('base_session')->sess_id());
            $data = $obj_mCart->get_cookie_cart_arr($member_indent,$_POST['member_id']);
        }
        else
        {
            $member_indent = md5(kernel::single('base_session')->sess_id());
            $data = $obj_mCart->get_cookie_cart_arr($member_indent);
        }
                
        $arr_cart_objects = $obj_mCart->get_cart_object($data);
        
        $obj_total = kernel::single('b2c_order_total');
        $sdf_order = $_POST;
        echo $obj_total->order_total_method($this,$arr_cart_objects,$sdf_order, "true");exit;
    }
    
    /**
     * 得到订单相应的支付信息
     * @param null
     * @return null
     */
    public function payment()
    {
        $obj_payment_select = new ectools_payment_select();
        $sdf = $_POST;
        echo $obj_payment_select->select_pay_method($this, $sdf, $sdf['member_id'], true);exit;
    }
    
    /**
     * 添加订单的接口
     * @param null
     * @return null
     */
    public function docreate()
    {
        $this->begin("index.php?app=b2c&ctl=admin_order&act=addnew");
        
        $msg = "";
        if (!$_POST['delivery']['ship_area'] || !$_POST['delivery']['ship_addr_area'] || !$_POST['delivery']['ship_addr'] || !$_POST['delivery']['ship_name'] || (!$_POST['delivery']['ship_email'] && !$_POST['member_id']) || (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel']) || !$_POST['delivery']['shipping_id'] || !$_POST['payment']['pay_app_id'])
        {
            if (!$_POST['delivery']['ship_area'] || !$_POST['delivery']['ship_addr_area'])
            {
                $msg .= app::get('b2c')->_("收货地区不能为空！")."<br />";
            }
            
            if (!$_POST['delivery']['ship_addr'])
            {
                $msg .= app::get('b2c')->_("收货地址不能为空！")."<br />";
            }
            
            if (!$_POST['delivery']['ship_name'])
            {
                $msg .= app::get('b2c')->_("收货人姓名不能为空！")."<br />";
            }
            
            if (!$_POST['delivery']['ship_email'] && !$this->user->user_id)
            {
                $msg .= app::get('b2c')->_("Email不能为空！")."<br />";
            }
            
            if (!$_POST['delivery']['ship_mobile'] && !$_POST['delivery']['ship_tel'])
            {
                $msg .= app::get('b2c')->_("手机或电话必填其一！")."<br />";
            }
            
            if (!$_POST['delivery']['shipping_id'])
            {
                $msg .= app::get('b2c')->_("配送方式不能为空！")."<br />";
            }
            
            if (!$_POST['payment']['pay_app_id'])
            {
                $msg .= app::get('b2c')->_("支付方式不能为空！")."<br />";
            }
            
            if (strpos($msg, '<br />') !== false)
            {
                $msg = substr($msg, 0, strlen($msg) - 6);
            }
            eval("\$msg = \"$msg\";");

            $this->end(false, $msg);
        }
        
        $obj_mCart = $this->app->model('cart');
        if (!$_POST['member_id'])
        {
            $member_indent = md5(kernel::single('base_session')->sess_id());
            $data = $obj_mCart->get_cookie_cart_arr($member_indent);
        }
        else
        {
            $member_indent = md5($_POST['member_id'] . kernel::single('base_session')->sess_id());
            $data = $obj_mCart->get_cookie_cart_arr($member_indent,$_POST['member_id']);
        }
                 
        $objCarts = $obj_mCart->get_cart_object($data);
        $is_empty = $obj_mCart->is_empty($objCarts);
        if ($is_empty)
        {
            $this->end(false, app::get('b2c')->_('购物车为空，操作失败！'));
        }
        
        $order = &$this->app->model('orders');
        $_POST['order_id'] = $order_id = $order->gen_id();
        $order_data = array();
        $obj_order_create = kernel::single("b2c_order_create");
        $order_data = $obj_order_create->generate($_POST, $member_indent, $msg, $objCarts);
        if (!$order_data)
        {
            $this->end(false, $msg, "index.php?app=b2c&ctl=admin_order&act=index");
        }
        $result = $obj_order_create->save($order_data, $msg);        
        // 与中心交互
        /*$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
        
        if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
        {
            if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
                $obj_rpc_request_service->rpc_caller_request($order_data,'create');
        }
        else
        {
            $obj_order_create->rpc_caller_request($order_data);
            }*/
        //新的版本控制api
        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
        $obj_apiv->rpc_caller_request($order_data, 'ordercreate');
        
        // 取到日志模块
        $log_text = "";
        if ($result)
        {
            $log_text = app::get('b2c')->_("订单创建成功！");
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog')){
                if(method_exists($obj_operatorlogs,'inlogs')){
                    $memo = '新订单被添加，订单号为  "'.$order_data['order_id'].'"';
                    $obj_operatorlogs->inlogs($memo, $order_data['order_id'], 'orders');
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        }
        else
        {
            $log_text = app::get('b2c')->_("订单创建失败！");
        }
        $orderLog = $this->app->model("order_log");
        $sdf_order_log = array(
            'rel_id' => $order_id,
            'op_id' => $this->user->user_id,
            'op_name' => $this->user->user_data['account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'creates',
            'result' => ($result) ? 'SUCCESS' : 'FAILURE',
            'log_text' => $log_text,
        );
        
        $log_id = $orderLog->save($sdf_order_log);
        
        if ($result)
        {            
            // 订单成功后清除购物车的的信息            
            $cart_model = $this->app->model('cart');
            $cart_model->del_cookie_cart_arr($member_indent);
            
            // 得到物流公司名称
            if ($order_data['order_objects'])
            {
                $itemNum = 0;
                $good_id = "";
                $goods_name = "";
                foreach ($order_data['order_objects'] as $arr_objects)
                {
                    if ($arr_objects['order_items'])
                    {
                        if ($arr_objects['obj_type'] == 'goods')
                        {
                            $obj_goods = $this->app->model('goods');
                            $good_id = $arr_objects['order_items'][0]['goods_id'];
                            $arr_goods = $obj_goods->dump($good_id);
                        }
                            
                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            $itemNum = $this->objMath->number_plus(array($itemNum, $arr_items['quantity']));
                            if ($arr_objects['obj_type'] == 'goods')
                            {
                                if ($arr_items['item_type'] == 'product')
                                    $goods_name .= $arr_items['name'] . ($arr_items['products']['spec_info'] ? '(' . $arr_items['products']['spec_info'] . ')' : '') . '(' . $arr_items['quantity'] . ')';
                            }
                        }
                    }
                }
                $obj_dlytype = $this->app->model('dlytype');
                $arr_dlytype = $obj_dlytype->dump($order_data['shipping']['shipping_id'], 'dt_name');
                
                if ($order_data['member_id'])
                {
                    $obj_members = $this->app->model('members');
                    $arrPams = $obj_members->dump($order_data['member_id'], '*', array(':account@pam' => array('*')));
                }
                $arr_updates = array(
                    'order_id' => $order_id,
                    'total_amount' => $order_data['total_amount'],
                    'shipping_id' => $arr_dlytype['dt_name'],
                    'ship_mobile' => $order_data['consignee']['mobile'],
                    'ship_tel' => $order_data['consignee']['telephone'],
                    'ship_addr' => $order_data['consignee']['addr'],
                    'ship_email' => $order_data['consignee']['email'] ? $order_data['consignee']['email'] : '',
                    'ship_zip' => $order_data['consignee']['zip'],
                    'ship_name' => $order_data['consignee']['name'],
                    'member_id' => $order_data['member_id'] ? $order_data['member_id'] : 0,
                    'uname' => (!$order_data['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
                    'itemnum' => count($order_data['order_objects']),
                    'goods_id' => $good_id,
                    'goods_url' => kernel::base_url(1).kernel::url_prefix().app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$good_id)),
                    'thumbnail_pic' => base_storager::image_path($arr_goods['image_default_id']),
                    'goods_name' => $goods_name,
                    'ship_status' => '',
                    'pay_status' => 'Nopay',
                    'is_frontend' => false,
                );
                $order->fireEvent('create', $arr_updates, $order_data['member_id']);
            }
        }
        
        if ($result)
        {
            $order_num = $order->count(array('member_id' => $order_data['member_id']));
            $obj_mem = $this->app->model('members');
            $obj_mem->update(array('order_num'=>$order_num), array('member_id'=>$order_data['member_id']));
            $this->end(true, app::get('b2c')->_('订单创建成功'), "index.php?app=b2c&ctl=admin_order&act=index");
        }
        else
            $this->end(false, $msg, "index.php?app=b2c&ctl=admin_order&act=index");
    }
    
    /**
     * 管理员保存订单留言的回复
     * @params null
     * @return null
     */
    public function saveOrderMsgText()
    {   
        $_POST['author_id'] = $this->user->user_id;
        $_POST['author'] = app::get('b2c')->_('管理员');
        $_POST['to_type'] = 'member';
        
        $obj_order_message = kernel::single("b2c_order_message");
        $is_save = $obj_order_message->create($_POST, $msg);
        if (!$is_save)
        {
            $this->begin();
            $this->end(false,app::get('b2c')->_('保存留言失败！'));
        }
        else
        {            
            $oMsg = &kernel::single("b2c_message_order");
            $orderMsg = $oMsg->getList('*', array('order_id' => $_POST['msg']['orderid'], 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');
            $this->pagedata['ordermsg'] = $orderMsg;
            echo $this->fetch("admin/order/od_msg_item.html");
        }
    }
    
    /**
     * 显示订单详情的接口
     * @param string order id
     * @return null
     */
    public function showEdit($orderid)
    {
        $objOrder = &$this->app->model('orders');
        //已完成订单与已发货订单不可操作
        $order_status = $objOrder->getList('status,ship_status',array('order_id'=>$orderid));
        if($order_status[0]['status'] == 'finish' or $order_status[0]['ship_status'] == 1){
            header('Content-Type: text/html; charset=utf-8');
            echo "非法操作！";exit;
        }
        $this->path[] = array('text'=>app::get('b2c')->_('订单编辑'));
        
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
        $this->singlepage('admin/order/detail/page_has_btn.html');
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
     * 修改订单item项目，用于ajax请求
     * @param null
     * @return unknown_type
     */
    public function toEdit()
    {
        $_POST['user_id'] = $this->user->user_id;
        $_POST['account']['login_name'] = $this->user->user_data['account']['login_name'];
        
        /** 检查订单是否可以被操作 **/
        $obj_order_check = kernel::single('b2c_order_checkorder');
        if (!$obj_order_check->checkfor_order_update($_POST, $msg))
        {
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.$msg.'",_:null}';exit;
        }
            
        $arr_data = $this->_process_fields($_POST);
        $obj_order = $this->app->model('orders');
        $result = $obj_order->save($arr_data);
        
        if (count($_POST['aItems']))
        {
            if ($result)
            {
                $obj_order_update = kernel::single('b2c_order_update');
                if ($obj_order_update->generate($_POST, true, $msg))
                {                
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{success:"'.app::get('b2c')->_("成功.").'",_:null,order_id:"'.$_POST['order_id'].'"}';
                }
                else
                {
                    $this->begin('index.php?app=b2c&ctl=admin_order&act=showEdit&p[0]=' . $_POST['order_id']);
                    if (isset($msg) && $msg)
                        eval("\$msg = \"$msg\";");
                    $this->end(false, $msg);
                }
            }
            else
            {
                $this->begin('index.php?app=b2c&ctl=admin_order&act=showEdit&p[0]=' . $_POST['order_id']);
                if (isset($msg) && $msg)
                    eval("\$msg = \"$msg\";");
                $this->end(false, $msg);
            }
        }
        else
        {
            $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
            $arr_orders = $obj_order->dump($_POST['order_id'], '*', $subsdf);
            if (count($arr_orders['order_objects']) == 0)
            {
                $this->begin('index.php?app=b2c&ctl=admin_order&act=showEdit&p[0]=' . $_POST['order_id']);
                $this->end(false, app::get('b2c')->_('订单详细不存在，请确认！'));
            }
            else
            {
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_("成功.").'",_:null,order_id:"'.$_POST['order_id'].'"}';
            }
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
     * 设置订单样式
     * @param null
     * @return null
     */
    public function showPrintStyle()
    {
        $this->path[] = array('text'=>app::get('b2c')->_('订单打印格式设置'));
        $dbTmpl = $this->app->model('member_systmpl');
        $filetxt = $dbTmpl->get('/admin/order/orderprint');
        $cartfiletxt = $dbTmpl->get('/admin/order/print_cart');
        $sheetfiletxt = $dbTmpl->get('/admin/order/print_sheet');
        $this->pagedata['styleContent'] = $filetxt;
        $this->pagedata['styleContentCart'] = $cartfiletxt;
        $this->pagedata['styleContentSheet'] = $sheetfiletxt;
        $this->singlepage('admin/order/printstyle.html');
    }
    
    /**
     * 保存订单打印样式
     * @param null
     * @return null
     */
    public function savePrintStyle()
    {
        $this->begin('');
        $dbTmpl = $this->app->model('member_systmpl');
        $dbTmpl->set('/admin/order/print_sheet', $_POST["txtcontentsheet"]);
        $dbTmpl->set('/admin/order/print_cart', $_POST["txtcontentcart"]);
        $this->end($dbTmpl->set('/admin/order/orderprint', $_POST["txtcontent"]),app::get('b2c')->_('订单打印模板保存成功'));
    }
    
    /**
     * rebackPrintStyle
     *
     * @access public
     * @return void
     */
    public function rebackPrintStyle(){
        $this->begin('');
        $dbTmpl = $this->app->model('member_systmpl');
        $dbTmpl->clear('/admin/order/print_sheet',$msg);
        $dbTmpl->clear('/admin/order/print_cart',$msg);
        $is_clear = $dbTmpl->clear('/admin/order/orderprint',$msg);
        if ($is_clear)
            $this->end(true,app::get('b2c')->_('恢复默认值成功'));
        else
            $this->end(false,$msg);
    }
}
