<?php

 

/**
 * 外部支付接口统一调用的api类
 * 
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_api
{
	/**
	 * @var object 应用对象的实例。
	 */ 
	private $app;
	
	/**
	 * 构造方法
	 * @param object 当前应用的app
	 * @return null
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	/**
	 * 支付返回后的同意支付处理
	 * @params array - 页面参数
	 * @return null
	 */
	public function parse($params='')
	{		
		// 取到内部系统参数
		$arr_pathInfo = explode('?', $_SERVER['REQUEST_URI']);
		$pathInfo = substr($arr_pathInfo[0], strpos($arr_pathInfo[0], "parse/") + 6); 
		$objShopApp = $this->getAppName($pathInfo);
		$innerArgs = explode('/', $pathInfo);
		$class_name = array_shift($innerArgs);
		$class_name = array_shift($innerArgs);
		$method = array_shift($innerArgs);
		
		$arrStr = array();
		$arrSplits = array();
		$arrQueryStrs = array();
		// QUERY_STRING
		if (isset($arr_pathInfo[1]) && $arr_pathInfo[1]){
			$querystring = $arr_pathInfo[1];
		}
		if ($querystring)
		{
			$arrStr = explode("&", $querystring);
			
			foreach ($arrStr as $str)
			{
				$arrSplits = explode("=", $str);
				$arrQueryStrs[urldecode($arrSplits[0])] = urldecode($arrSplits[1]);
			}
		}
		else
		{
			if ($_POST)
			{
				$arrQueryStrs = $_POST;
			}
		}
		
		$payments_bill = new $class_name($objShopApp);
		$ret = $payments_bill->$method($arrQueryStrs);
		// 支付结束，回调服务.
		if (!isset($ret['status']) || $ret['status'] == '') $ret['status'] = 'failed';
		
		$obj_payments = app::get('ectools')->model('payments');
        //判断是否合并支付
        if(isset($ret['payment_id'])){
            $pay_data = $obj_payments->getList('*',array('merge_payment_id'=>$ret['payment_id']));
        }else{
            $pay_data = false;
        }
        if($pay_data){
            foreach($pay_data as $key=>$val){
                $sdf = $obj_payments->dump($val['payment_id'], '*', '*');
                if ($sdf){
                    $sdf['account'] = $ret['account'];
                    $sdf['bank'] = $ret['bank'];
                    $sdf['pay_account'] = $ret['pay_account'];
                    $sdf['currency'] = $ret['currency'];
                    $sdf['trade_no'] = $ret['trade_no'];
                    $sdf['t_payed'] = $ret['t_payed'];
                    $sdf['pay_app_id'] = $ret['pay_app_id'];
                    $sdf['pay_type'] = $ret['pay_type'];
                    $sdf['memo'] = $ret['memo'];
                }
                $ret['payment_id'] = $val['payment_id'];
                switch ($ret['status']){
                    case 'succ':
                    case 'progress':
                        if ($sdf && $sdf['status'] != 'succ' && $sdf['status'] != 'progress')
                        {						
                            $is_updated = false;
                            $obj_payment_update = kernel::single('ectools_payment_update');
                            $is_updated = $obj_payment_update->generate($ret, $msg);        
                            $obj_pay_lists = kernel::servicelist("order.pay_finish");
                            foreach ($obj_pay_lists as $order_pay_service_object)
                            {						
                                // 防止重复充值
                                if ($is_updated)
                                {
                                    $db = kernel::database();
                                    $transaction_status = $db->beginTransaction();
                                    $is_updated = $order_pay_service_object->order_pay_finish($sdf, $ret['status'], 'font',$msg,$refund_status);
                                    if (!$is_updated)
                                    {
                                        kernel::log(app::get('ectools')->_('支付失败') . " " . $msg ."\n");
                                        $db->rollback();
                                    }
                                    else
                                    {
                                        $db->commit($transaction_status);
                                        // 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
                                        if (method_exists($order_pay_service_object, 'order_pay_finish_extends')){
                                            $order_pay_service_object->order_pay_finish_extends($sdf);
                                            
                                            if(!$refund_status){
                                                foreach((array)$sdf['orders'] as $items){
                                                    $order_id = $items['rel_id'];
                                                    $this->updateRank($order_id);
                                                }
                                            }
                                           
                                        }
                                    }					
                                }
                            }
                        }
                        break;
                    case 'REFUND_SUCCESS':
                        // 退款成功操作					
                        if ($sdf){
                            unset($sdf['payment_id']);
                            $obj_refund = app::get('ectools')->model('refund');
                            $sdf['refund_id'] = $obj_refund->gen_id();						
                            $ret['status'] = 'succ';
                            if ($obj_refund->insert($sdf)){
                                //处理单据的支付状态
                                $obj_refund_finish = kernel::service("order.refund_finish");
                                $obj_refund_finish->order_refund_finish($sdf, $ret['status'], 'font',$msg);
                            }
                        }
                        break;
                    case 'PAY_PDT_SUCC':
                        $ret['status'] = 'succ';
                        // 无需更新状态.
                        break;
                    case 'failed':
                    case 'error':
                    case 'cancel':
                    case 'invalid':
                    case 'timeout':
                        $is_updated = false;
                        $obj_payment_update = kernel::single('ectools_payment_update');
                        $is_updated = $obj_payment_update->generate($ret, $msg);
                        break;
                }
            }
        }else{
            $sdf = $obj_payments->dump($ret['payment_id'], '*', '*');
            if ($sdf){
                $sdf['account'] = $ret['account'];
                $sdf['bank'] = $ret['bank'];
                $sdf['pay_account'] = $ret['pay_account'];
                $sdf['currency'] = $ret['currency'];
                $sdf['trade_no'] = $ret['trade_no'];
                $sdf['t_payed'] = $ret['t_payed'];
                $sdf['pay_app_id'] = $ret['pay_app_id'];
                $sdf['pay_type'] = $ret['pay_type'];
                $sdf['memo'] = $ret['memo'];
            }
            switch ($ret['status']){
                case 'succ':
                case 'progress':
                    if ($sdf && $sdf['status'] != 'succ' && $sdf['status'] != 'progress')
                    {						
                        $is_updated = false;
                        $obj_payment_update = kernel::single('ectools_payment_update');
                        $is_updated = $obj_payment_update->generate($ret, $msg);
                                        
                        $obj_pay_lists = kernel::servicelist("order.pay_finish");
                        foreach ($obj_pay_lists as $order_pay_service_object)
                        {						
                            // 防止重复充值
                            if ($is_updated)
                            {
                                $db = kernel::database();
                                $transaction_status = $db->beginTransaction();
                                $is_updated = $order_pay_service_object->order_pay_finish($sdf, $ret['status'], 'font',$msg,$refund_status);
                                if (!$is_updated)
                                {
                                    kernel::log(app::get('ectools')->_('支付失败') . " " . $msg ."\n");
                                    $db->rollback();
                                }
                                else
                                {
                                    $db->commit($transaction_status);
                                    // 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
                                    if (method_exists($order_pay_service_object, 'order_pay_finish_extends')){
                                        $order_pay_service_object->order_pay_finish_extends($sdf);
                                        if(!$refund_status){
                                          
                                            foreach((array)$sdf['orders'] as $items){
                                                $order_id = $items['rel_id'];
                                                $this->updateRank($order_id);
                                            }
                                           
                                        }
                                    }
                                }					
                            }
                        }
                    }
                    break;
                case 'REFUND_SUCCESS':
                    // 退款成功操作					
                    if ($sdf){
                        unset($sdf['payment_id']);
                        $obj_refund = app::get('ectools')->model('refund');
                        $sdf['refund_id'] = $obj_refund->gen_id();						
                        $ret['status'] = 'succ';
                        if ($obj_refund->insert($sdf)){
                            //处理单据的支付状态
                            $obj_refund_finish = kernel::service("order.refund_finish");
                            $obj_refund_finish->order_refund_finish($sdf, $ret['status'], 'font',$msg);
                        }
                    }
                    break;
                case 'PAY_PDT_SUCC':
                    $ret['status'] = 'succ';
                    // 无需更新状态.
                    break;
                case 'failed':
                case 'error':
                case 'cancel':
                case 'invalid':
                case 'timeout':
                    $is_updated = false;
                    $obj_payment_update = kernel::single('ectools_payment_update');
                    $is_updated = $obj_payment_update->generate($ret, $msg);
                    break;
            }

            // Redirect page.
            if ($sdf['return_url']){ 
                header('Location: '.strtolower(kernel::request()->get_schema().'://'.kernel::request()->get_host()).$sdf['return_url']);
            }
		}
		// Redirect page.
		if ($sdf['return_url']){ 
			header('Location: '.strtolower(kernel::request()->get_schema().'://'.kernel::request()->get_host()).$sdf['return_url']);
		}
	}
	
	/** 
	 * 得到实例应用名
	 * @params string - 请求的url
	 * @return object - 应用实例
	 */
	private function getAppName($strUrl='')
	{
		//todo.
		if (strpos($strUrl, '/') !== false)
		{
			$arrUrl = explode('/', $strUrl);
		}
		return app::get($arrUrl[0]);
	}

    
    private function updateRank($order_id=0){
        if(!$order_id) return true;
        $objOrder = app::get('b2c')->model('orders');
        $subsdf = array('order_objects'=>array('obj_id',array('order_items'=>array('goods_id,nums',array(':goods'=>array('goods_id,count_stat'))))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, 'order_id', $subsdf);
        if(!$sdf_order['order_id']) return true;
        $objGoods = app::get('b2c')->model('goods');
        $weekMark = 'buy';
        $item = 'buy_count';
        foreach((array)$sdf_order['order_objects'] as $objects){
            if(!$objects['obj_id']) continue;
            foreach((array)$objects['order_items'] as $items){
                if(!isset($items['goods']['goods_id']) || $items['goods']['goods_id'] != $items['goods_id']) continue;
                $count_stat = unserialize($items['goods']['count_stat']);
                $dayNum = $objGoods->day(time());
                $dayBegin = floor(mktime(0,0,0,date('m'),01,date('Y'))/86400);
                $dayEnd = $dayBegin + date('t', mktime(0,0,0,date('m'),date('d'),date('Y')));
                $weekNum = $num = intval($items['quantity']);
                if(isset($count_stat[$weekMark])){
                    foreach($count_stat[$weekMark] as $day => $countNum){
                        if($dayNum > $day+9) unset($count_stat[$weekMark][$day]);
                        if($dayNum < $day+8) $weekNum += $countNum;
                    }
                }
                $count_stat[$weekMark][$dayNum] += $num;
                $sqlCol = '';
                $monthMark = 'mbuy'; 
                $monthNum = $num;
                if(isset($count_stat[$monthMark])){
                    foreach($count_stat[$monthMark] as $day => $countNum){
                        //if($dayBegin>$day || $dayEnd<$day) unset($count_stat[$monthMark][$day]);
                        //else $monthNum += $countNum;
                        if($dayNum > $day+32) unset($count_stat[$monthMark][$day]);
                        if($dayNum < $day+30) $monthNum += $countNum;
                    }
                }
                $count_stat[$monthMark][$dayNum] += $num;
                $sqlCol .= ','.$weekMark.'_m_count='.intval($monthNum);
                $objStore = app::get('business')->model('storemanger');
                $sql =" update sdb_business_storemanger as s inner join ".
                  " (select sum(buy_m_count)+".intval($num)." as _count,store_id  from sdb_b2c_goods where store_id in (select store_id from sdb_b2c_goods where goods_id=".intval($items['goods_id']).") group by store_id) as c on s.store_id=c.store_id ".
                  " set s.buy_m_count=c._count ";
                $objGoods->db->exec($sql);
                $sqlCol .= ','.$weekMark.'_w_count='.intval($weekNum).', count_stat=\''.serialize($count_stat).'\'';
                $objGoods->db->exec("UPDATE sdb_b2c_goods SET ".$item." = ".$item."+".intval($num).$sqlCol." WHERE goods_id =".intval($items['goods_id']));
            }
        }
        $orderData = $objGoods->db->selectrow('SELECT o.member_id, m.login_name,o.ship_email FROM sdb_b2c_orders o LEFT JOIN sdb_pam_account m ON o.member_id = m.account_id WHERE o.order_id = '.$objGoods->db->quote($order_id));
        $orderItem = $objGoods->db->select('SELECT i.price, p.goods_id, i.product_id, p.name,p.spec_info, i.nums FROM sdb_b2c_order_items i LEFT JOIN sdb_b2c_products p ON p.product_id = i.product_id WHERE i.order_id = '.$objGoods->db->quote($order_id));
        foreach( $orderItem as $iKey => $iValue ){
            $sql = 'INSERT INTO sdb_b2c_sell_logs (member_id,name,price,goods_id,product_id,product_name,spec_info,number,createtime) VALUES ( "'.($orderData['member_id']?$orderData['member_id']:0).'", "'.($orderData['login_name']?$orderData['login_name']:$orderData['ship_email']).'", "'.$iValue['price'].'", "'.$iValue['goods_id'].'", "'.$iValue['product_id'].'", "'.htmlspecialchars($iValue['name']).'", "'.$iValue['spec_info'].'" , "'.$iValue['nums'].'", "'.time().'" )';
            $objGoods->db->exec($sql);
        }
    }
    
}
