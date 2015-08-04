<?php

 

  class b2c_queue{
      public function send_mail(&$cursor_id,$params){
          $obj_emailconf = kernel::single('desktop_email_emailconf');
        $aTmp = $obj_emailconf->get_emailConfig();
        $acceptor =  $params['acceptor'];    //收件人邮箱
        $aTmp['shopname'] = app::get('b2c')->getConf('system.shopname');
        $subject = $params['title'];
        $body = $params['body'];
        $email = kernel::single('desktop_email_email');
        $email->ready($aTmp);
        $res = $email->send($acceptor,$subject,$body,$aTmp);
        }
        
        
    public function send_msg(&$cursor_id,$params){
        $obj_memmsg = kernel::single('b2c_message_msg');
        $aData = $params['data'];
        $aData['member_id'] = 1;
        $aData['uname'] = app::get('b2c')->_('管理员');
        $aData['to_id'] = $params['member_id'];
        $aData['msg_to'] = $params['name'];
        $aData['subject'] = $aData['title']; 
        $aData['comment'] = $aData['content'];
        $aData['has_sent'] = 'true';
        $obj_memmsg->send($aData);
		
        if($params['gnotify_id']) {
		$member_goods = app::get('b2c')->model('member_goods');
        $sdf = $member_goods->dump($params['gnotify_id']);
        $sdf['status'] = "send";
        $sdf['send_time'] = time();
        $member_goods->save($sdf);
    }
    }
    
    #发手机短信
    
    public function send_sms(&$cursor_id,$params){
        $obj_memmsm = kernel::single('b2c_messenger_sms');
        $objfilter = kernel::service('filter_sms_content');
        $title = $params['data']['title'];
        $message = $params['data']['content'];
        if(is_object($objfilter)){
            if(method_exists($objfilter,'get_filter_content')){
                $data = $objfilter->get_filter_content($title,$message);
                $title = $data['title'] ? $data['title'] : '';
                $message = $data['content'];
            }
        }
        $to = $params['mobile_number'];
        $config['shopname'] = app::get('site')->getConf('site.name');
        $config['use_reply'] = ($params['data']['use_reply']=='true') ? 1 : 0;
        $config['sendType'] = ($params['data']['sendType']=='fan-out') ? 'fan-out' : 'notice';
        if($obj_memmsm->ready($config)) $obj_memmsm->send($to,$title,$message,$config);
		
        if($params['gnotify_id']) {
		$member_goods = app::get('b2c')->model('member_goods');
        $sdf = $member_goods->dump($params['gnotify_id']);
        $sdf['status'] = "send";
        $sdf['send_time'] = time();
        $member_goods->save($sdf);
    } 
    } 
    ##发到货通知邮件
    public function goods_notify(&$cursor_id,$params){
        $obj_emailconf = kernel::single('desktop_email_emailconf');
        $aTmp = $obj_emailconf->get_emailConfig();
        $acceptor = $params['acceptor'];     //收件人邮箱
        $aTmp['shopname'] = app::get('b2c')->getConf('system.shopname');
        $subject = $params['title'];
        #$subject = "biaoti";
        $body = $params['body'];
        #$body = "内容";
        $email = kernel::single('desktop_email_email');
        $email->ready($aTmp);
        $res = $email->send($acceptor,$subject,$body,$aTmp);
        $member_goods = app::get('b2c')->model('member_goods');
        $sdf = $member_goods->dump($params['gnotify_id']);
        $sdf['status'] = "send";
        $sdf['send_time'] = time();
        $member_goods->save($sdf);
    }
	
	public function send_orders(&$cursor_id,$params){
		// 与中心交互
		$is_need_rpc = false;
		$obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
		foreach ($obj_rpc_obj_rpc_request_service as $obj)
		{
			if ($obj && method_exists($obj, 'rpc_judge_send'))
			{
				if ($obj instanceof b2c_api_rpc_notify_interface)
					$is_need_rpc = $obj->rpc_judge_send($params);
			}

			if ($is_need_rpc) break;
		}

		if ($is_need_rpc)
		{
          /*$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');

			if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
			{
				if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
					$obj_rpc_request_service->rpc_caller_request($params,'create');
			}
			else
			{
				$obj_order_create = kernel::single('b2c_order_rpc_recaller');
				$obj_order_create->rpc_caller_request($params);
                }*/
          //新的版本控制api
          $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
          $obj_apiv->rpc_caller_request($params, 'ordercreate');
		}
	}
	
	public function send_payments(&$cursor_id,$params){
		// 与中心交互
		$is_need_rpc = false;
		$obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
		foreach ($obj_rpc_obj_rpc_request_service as $obj)
		{
			if ($obj && method_exists($obj, 'rpc_judge_send'))
			{
				if ($obj instanceof b2c_api_rpc_notify_interface)
					$is_need_rpc = $obj->rpc_judge_send($sdf_order);
			}
			
			if ($is_need_rpc) break;
		}
		
		if ($is_need_rpc)
		{
          /*$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');

			if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
			{
				if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
					$obj_rpc_request_service->rpc_caller_request($params,'pay');
			}
			else
			{
				$obj_order_create = kernel::single('b2c_order_rpc_pay');
				$obj_order_create->rpc_caller_request($params);
                }*/
          //新的版本控制api
          $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
          $obj_apiv->rpc_caller_request($params, 'orderpay');
		}
	}
  }
?>
