<?php

 

class logisticstrack_orderlog {
    /**
     * 得到特定订单的所有日志，接管B2C订单日志显示流程
     * @params string order id
     * @params int page num
     * @params int page limit
     * @return array log list
     */
    public function getOrderLogList($order_id, $page=0, $limit=-1) {
        $objlog = app::get('b2c')->model('order_log');
        $arrlogs = array();
        $arr_returns = array();
        
        if ($limit < 0) {
            $arrlogs = $objlog->getList('*', array('rel_id' => $order_id));
        }
        
        $limitStart = $page * $limit;
        
        $arrlogs_all = $objlog->getList('*', array('rel_id' => $order_id));
        $arrlogs = $objlog->getList('*', array('rel_id' => $order_id), $limitStart, $limit);
        if ($arrlogs) {
            foreach ($arrlogs as &$logitems) {
                $logitems['addon'] = unserialize($logitems['addon']);
                switch ($logitems['behavior']) {
                    case 'creates':
                        $logitems['behavior'] = app::get('b2c')->_("创建");
                        if ($arr_log_text = unserialize($logitems['log_text'])) {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log) {
                                $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                            }
                        }
                        break;
                    case 'updates':
                        $logitems['behavior'] = app::get('b2c')->_("修改");
                        if ($arr_log_text = unserialize($logitems['log_text'])) {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log) {
                                $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                            }
                        }
                        break;
                    case 'payments':
                        $logitems['behavior'] = app::get('b2c')->_("支付");
                        if ($arr_log_text = unserialize($logitems['log_text'])) {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log) {
                                $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2]);
                            }
                        }
                        break;
                    case 'refunds':
                        $logitems['behavior'] = app::get('b2c')->_("退款");
                        if ($arr_log_text = unserialize($logitems['log_text'])) {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log) {
                                $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                            }
                        }
                        break;
                    case 'delivery':
                        $logitems['behavior'] = app::get('b2c')->_("发货");
                        if ($arr_log_text = unserialize($logitems['log_text'])) {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log) {
	                             if ( preg_match('/show_delivery_item/', $arr_log['txt_key']) ) {
	                             	$delivery_id = $arr_log['data'][0];
	                             	$logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
	                             } elseif ( preg_match('/物流单号/', $arr_log['txt_key']) ) {
	                             	$dly_number = $arr_log['data'][0];
	                             	$logitems['log_logi_no']['dly_number'] = $dly_number;
	                             	$logitems['log_logi_no']['delivery_id'] = $delivery_id;
	                             	$logitems['log_logi_no']['desktop_url'] = app::get('desktop')->router()->gen_url(
	                             		array('app'=>'logisticstrack','ctl'=>'admin_tracker','act'=>"pull",'p'=>array('0'=>$delivery_id)));
	                             } else {
	                             	$logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
	                             }
                            }
                        }
                        break;
                    case 'reship':
                        $logitems['behavior'] = app::get('b2c')->_("退货");
                        if ($arr_log_text = unserialize($logitems['log_text'])) {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log) {
	                             if ( preg_match('/show_delivery_item/', $arr_log['txt_key']) ) {
	                             	$reship_id = $arr_log['data'][0];
	                             	$logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
	                             } elseif ( preg_match('/物流单号/', $arr_log['txt_key']) ) {
	                             	$dly_number = $arr_log['data'][0];
	                             	$logitems['log_logi_no']['dly_number'] = $dly_number;
	                             	$logitems['log_logi_no']['delivery_id'] = $reship_id;
	                             	$logitems['log_logi_no']['desktop_url'] = app::get('desktop')->router()->gen_url(
	                             		array('app'=>'logisticstrack','ctl'=>'admin_tracker','act'=>"pull",'p'=>array('0'=>$reship_id)));
	                             } else {
	                             	$logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
	                             }
                            }
                        }
                        break;
                    case 'finish':
                        $logitems['behavior'] =  app::get('b2c')->_("完成");
                        if ($arr_log_text = unserialize($logitems['log_text']))
                        {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log)
                            {
                                $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                            }
                        }
                        break;
                    case 'cancel':
                        $logitems['behavior'] = app::get('b2c')->_("作废");
                        if ($arr_log_text = unserialize($logitems['log_text']))
                        {
                            $logitems['log_text'] = '';
                            foreach ($arr_log_text as $arr_log)
                            {
                                $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                            }                            
                        }
                        break;
                    default:
                        break;
                }
            }
            unset($logitems);
        }
        
        $arr_returns['page'] = count($arrlogs_all);
        $arr_returns['data'] = $arrlogs;
        return $arr_returns;
        
    }
}
