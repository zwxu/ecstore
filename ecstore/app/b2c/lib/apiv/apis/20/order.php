<?php

/**
 * b2c order interactor with center
 */
class b2c_apiv_apis_20_order
{

     /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->objMath = kernel::single('ectools_math');

         $data = $_POST ? $_POST: $_GET;
         if($data['method'] &&  trim($data['source_type']) !='system'){
            foreach(kernel::servicelist('business.api_verify_store') as $object)
            {
                 if(is_object($object))
                 {
                     if(method_exists($object,'verifyStore'))
                     {
                        $result = $object->verifyStore(trim($data['store_cert']));
                        if($result==false){
                            echo app::get('b2c')->_('店铺校验失败！');
                            exit;
                        }else {

                            //取得Store_id
                            $arycert=app::get('business')->model('storemanger')->getList('store_id',array('store_cert'=>trim($data['store_cert'])));

                            if($arycert){
                              $this->store_id=$arycert[0]['store_id'];
                            }

                        }
                     }
                 }
            }
         }

    }

    public function search( $params, &$service )
    {
        //校验参数
        if( !( $start_time = $params['start_time'] ) )
            $service->send_user_error('7001', '开始时间不能为空！');
        if( ($start_time = strtotime(trim($start_time))) === false || $start_time == -1 )
            $service->send_user_error('7002', '开始时间不合法！');

        if( !( $end_time = $params['end_time'] ) )
            $service->send_user_error('7003', '结束时间不能为空！');
        if( ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
            $service->send_user_error('7004', '结束时间不合法！');

        $page_no = 1;
        if( $params['page_no'] != '' ){
            if( !is_numeric($params['page_no']) || $params['page_no'] < 1 )
                $service->send_user_error('7005', 'page_no不合法！');
            else
                $page_no = intval($params['page_no']);
        }

        $page_size = 40;
        if( $params['page_size'] != '' ){
            if( !is_numeric($params['page_size']) || $params['page_size'] < 1 || $params['page_size'] > 100 )
                $service->send_user_error('7006', 'page_size不合法！');
            else
                $page_size = intval($params['page_size']);
        }


        /**
		 * 支付状态数组
		 */

		$arr_pay_status = array(
			'0'=>'PAY_NO',
			'1'=>'PAY_FINISH',
			'2'=>'PAY_TO_MEDIUM',
			'3'=>'PAY_PART',
			'4'=>'REFUND_PART',
			'5'=>'REFUND_ALL',
            );

        $obj_orders = &app::get('b2c')->model('orders');

		$where = '';
		if( $start_time != '' )
			$where .= "AND last_modified > '" . $start_time . "' ";
		if( $end_time != '' )
			$where .= "AND last_modified <= '" . $end_time . "' ";

        //店铺ID
        $where .="  AND  store_id='".$this->store_id."' ";

		if( $where != '' )
			$where = 'WHERE ' . substr($where, 4);

		$sql	=	"SELECT ### FROM " .
            $obj_orders->table_name(1) . ' ' .
            $where .
            "ORDER BY last_modified ASC";

		//获取总数
		$total_results = $obj_orders->db->select( str_replace('###', 'count(*) cc', $sql) );
		if( $total_results )
			$total_results = $total_results[0]['cc'];
		else
			$total_results = 0;
		if($total_results == 0) {
            return $this->search_response(array());
		}

		//计算分页
		$offset = ($page_no-1) * $page_size;
		$limit = $page_size;

		$has_next = $total_results > ($offset+$limit) ? 'true' : 'false';

		$sdf = $obj_orders->db->selectLimit( str_replace('###', 'order_id, status, pay_status, ship_status, last_modified', $sql), $limit, $offset );

		if(!$sdf){
            return $this->search_response(array());
		}

        $trades = array();
        $index = 0;
        foreach( $sdf as $row )
        {
            $trades[$index]['tid'] = $row['order_id'];
            $trades[$index]['status'] = ($row['status'] == 'active') ? 'TRADE_ACTIVE' : 'TRADE_CLOSED';
            $trades[$index]['pay_status'] =  ($row['pay_status'] == '0' || !$row['pay_status']) ? 'PAY_NO' : $arr_pay_status[$row['pay_status']];
            $trades[$index]['ship_status'] = ($row['ship_status'] == '0' || !$row['ship_status']) ? 'SHIP_NO' : 'SHIP_FINISH';
            $trades[$index]['modified'] = date('Y-m-d H:i:s', $row['last_modified']);
            $index++;
        }

        return $this->search_response($trades, $total_results, $has_next);
    }

    private function search_response($trades, $total_results=0, $has_next='false'){

        return array(
            'trades' => $trades,
            'total_results' => $total_results,
            'has_next' => $has_next,
            );

    }

    public function detail( $params, &$service )
    {
        if( !( $order_id = $params['tid'] ) ){
            return $service->send_user_error('7001', 'tid不能为空！');
        }



       //检查此订单是否是该店铺
       if($this->store_id){
          $obj_orders = $this->app->model('orders');
          $orders=$obj_orders->getList('store_id',array('order_id'=>$order_id));

          if( $orders && $this->store_id !=$orders[0]['store_id']){
            return $service->send_user_error(app::get('b2c')->_('此订单ID不属于本店铺。'), array('tid' => $order_id));
          }

          if( !$orders){
             return $service->send_user_error(app::get('b2c')->_('此订单ID不存在。'), array('tid' => $order_id));
          }
        }


        $order_detail = kernel::single('b2c_order_full')->get($order_id);
        return $order_detail;
    }

    public function iframe_url( $params, &$service )
    {
        if( !( $order_id = $params['tid'] ) ){
            return $service->send_user_error('7001', 'tid不能为空！');
        }
        if( !( $notify_url = $params['notify_url'] ) ){
            return $service->send_user_error('7002', 'notify_url不能为空!');
        }

        base_kvstore::instance('b2c.iframe')->fetch('iframe.whitelist', $whitelist);
        if( !$whitelist )
            $whitelist = array();

        $random = md5(time() . mt_rand()) . '.' . time();
        array_push($whitelist, $random);

        $url_params = array(
            'tid' => $order_id,
            'secret_key' => $random,
            'notify_url' => $notify_url,
            );

        base_kvstore::instance('b2c.iframe')->store('iframe.whitelist', $whitelist);


        $url = kernel::openapi_url('openapi.b2c.iframe.order.edit', 'edit', $url_params);

        return $url;
    }
}