<?php



class b2c_stats_listener
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->arr_listener_keys = $this->app->getConf('system.event_listener_key');
        $this->arr_listener = $this->app->getConf('system.event_listener');
    }

    /**
     * 判断存储的统计模块是否安装
     * @param object application object
     * @return boolean
     */
    private function judge_app_install(&$app)
    {
        if (isset($app) && $app && is_object($app))
        {
            if ($app->is_actived())
                return true;
            else
                return false;
        }
        else
        {
            return false;
        }
    }

    /**
     * 监督订单处理事件
     * @param string 处理的事件名称
     * @param array 被监督订单的sdf数据
     */
    public function get_orderinfo($event_type, $order_data)
    {
        if ($order_data)
        {
            $app_stats = app::get('stats');

            if ($this->judge_app_install($app_stats))
            {
                $url = kernel::base_url();

                $str = $order_data['shipping'];
                if(strstr($str,app::get('b2c')->_("货到付款")))
                {
                    $shipment = 'cash';
                }
                else
                {
                    $shipment='nomal';
                }

                $info_v = array(
                    '_member_id' => $order_data['member_id'],
                    '_member' => $order_data['uname'],
                    '_order_id' => $order_data['order_id'],
                    '_ship_status' => $order_data['ship_status'],
                    '_status' => $order_data['pay_status'],
                    '_u_make' => $order_data['is_frontend'] ? 'font' : 'back',
                    '_num' => $order_data['itemnum'],
                    '_goods_url' => $order_data['goods_url'],
                    '_goods_id' => $order_data['goods_id'],
                    '_total' => $order_data['total_amount'],
                    '_goods_name' => $order_data['goods_name'],
                    '_thumbnail_pic' => $order_data['thumbnail_pic'],
                );
                if (!$order_data['is_frontend'])
                    $info_v['from'] = 'Admin';

                $obj_service_storager = kernel::service("stats_data_storager");
                //$keys = array_search('b2c:stats_listener:get_orderinfo', $this->arr_listener);
                foreach ($this->arr_listener as $key=>$listener_info)
                {
                    if (array_key_exists('b2c:stats_listener:get_orderinfo', $listener_info))
                    {
                        $listener_key = $key;
                    }
                }
                if ($listener_key)
                    $obj_service_storager->save($this->arr_listener_keys[$listener_key], $info_v);
            }
        }
    }


    /**
     * 订单支付的状态监督
     * @param string 被监督事件的名称
     * @param array 订单的sdf数据
     */
    public function get_payinfo($event_type,$order_data)
    {
        switch ($order_data['pay_status'])
        {
            case 'PAY_NO':
                $pay_status = 'nopay';
                break;
            case 'PAY_FINISH':
                $pay_status = 'pay';
                break;
            case 'PAY_TO_MEDIUM':
                $pay_status = 'deal';
                break;
            case 'PAY_PART':
                $pay_status = 'Partial_payments';
                break;
            case 'REFUND_PART':
                $pay_status = 'Partial_refund';
                break;
            case 'REFUND_ALL':
                $pay_status = 'Full_refund';
                break;
        }

        if ($order_data)
        {
            $app_stats = app::get('stats');

            if ($this->judge_app_install($app_stats))
            {
                $info_v = array(
                    '_order_id' => $order_data['order_id'],
                    '_status' => $pay_status,
                    '_u_make' => $order_data['is_frontend'] ? 'font' : 'Back',
                );

                $obj_service_storager = kernel::service("stats_data_storager");

                foreach ($this->arr_listener as $key=>$listener_info)
                {
                    if (array_key_exists('b2c:stats_listener:get_payinfo', $listener_info))
                    {
                        $listener_key = $key;
                    }
                }
                if ($listener_key)
                    $obj_service_storager->save($this->arr_listener_keys[$listener_key], $info_v);
            }
        }
    }


    /**
     * 发货退货事件监督
     * @param string 被监督事件的名称
     * @param array 订单的sdf数据
     */
    public function get_deliveryinfo($event_type, $order_data)
    {
        switch ($order_data['ship_status'])
        {
            case '1':
                $ship_status = 'send';
                break;
            case '2':
                $ship_status = 'parship';
                break;
            case '3':
                $ship_status = 'parreship';
                break;
            case '4':
                $ship_status = 'reship';
                break;
            default:
                $ship_status = 'send';
                break;
        }

        if ($order_data)
        {
            $app_stats = app::get('stats');

            if ($this->judge_app_install($app_stats))
            {
                $info_v = array(
                    '_order_id'=>$order_data['order_id'],
                    '_ship_status'=>$ship_status,
                    '_u_make' => 'Back',
                );

                $obj_service_storager = kernel::service("stats_data_storager");

                foreach ($this->arr_listener as $key=>$listener_info)
                {
                    if (array_key_exists('b2c:stats_listener:get_deliveryinfo', $listener_info))
                    {
                        $listener_key = $key;
                    }
                }
                if ($listener_key)
                    $obj_service_storager->save($this->arr_listener_keys[$listener_key], $info_v);
            }
        }
    }

    /**
     * 会员注册监控事件
     * @param string 被监督事件的名称
     * @param array 会员的sdf数据
    */
    public function get_memberinfo($event_type, $member_data)
    {
        if ($member_data)
        {
            $app_stats = app::get('stats');

            if ($this->judge_app_install($app_stats))
            {
                if ($member_data['is_frontend'])
                {
                    $info_m = array(
                        '_member_id' => $member_data['member_id'],
                        '_uname' => $member_data['uname'],
                        '_refer_url' => $member_data['refer_url'],
                        '_u_make' => 'font',
                    );
                }
                else
                {
                    $info_m = array(
                        '_Aid' => $member_data['member_id'],
                        '_Aname' => $member_data['uname'],
                        '_u_make' => 'Back',
                        '_style' => 'Back',
                    );
                }

                $obj_service_storager = kernel::service("stats_data_storager");

                foreach ($this->arr_listener as $key=>$listener_info)
                {
                    if (array_key_exists('b2c:stats_listener:get_memberinfo', $listener_info))
                    {
                        $listener_key = $key;
                    }
                }
                if ($listener_key && $obj_service_storager)
                    $obj_service_storager->save($this->arr_listener_keys[$listener_key], $info_m);
            }
        }
    }


    /**
     * 会员登录监控事件
     * @param string 被监督事件的名称
     * @param array 登录会员的sdf数据
     */
    public function get_logmember($event_type, $log_member)
    {
        if ($log_member)
        {
            $app_stats = app::get('stats');

            if ($this->judge_app_install($app_stats))
            {
                $info_log = array(
                    '_uid' => $log_member['member_id'],
                    '_uname' => $log_member['uname'],
                );

                $obj_service_storager = kernel::service("stats_data_storager");

                foreach ($this->arr_listener as $key=>$listener_info)
                {
                    if (array_key_exists('b2c:stats_listener:get_logmember', $listener_info))
                    {
                        $listener_key = $key;
                    }
                }
                if ($listener_key && $obj_service_storager)
                    $obj_service_storager->save($this->arr_listener_keys[$listener_key], $info_log);
            }
        }
    }


    /**
     * admin预存款添加
     * @param string 被监督事件的名称
     * @param array 发布数据
     * @param array 预存款信息
     */
    public function get_money($event_type, $money_data)
    {
        if ($money_data['doadd'])
        {
            $money='doadd';
        }
        else
        {
            $money='undo';
        }

        $app_stats = app::get('stats');

        if ($this->judge_app_install($app_stats))
        {
            $info_money = array(
                '_uid' => $money_data['member_id'],
                '_money' => $money,
                '_u_make' => $money_data['is_frontend'] ? 'font' : 'Back',
            );

            $obj_service_storager = kernel::service("stats_data_storager");

            foreach ($this->arr_listener as $key=>$listener_info)
            {
                if (array_key_exists('b2c:stats_listener:get_money', $listener_info))
                {
                    $listener_key = $key;
                }
            }
            if ($listener_key)
                $obj_service_storager->save($this->arr_listener_keys[$listener_key], $info_money);
        }
    }
}

?>
