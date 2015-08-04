<?php


class b2c_apiv_apis_20_ome_iframeedit extends b2c_apiv_extends_request
{
  var $method = 'store.trade.update';
  var $callback = array();
  var $title = '订单iframe编辑';
  var $timeout = 3;
  var $async = false;

  public function get_params($sdf)
  {
    $order_id = $sdf['order_id'];
    $order_detail = kernel::single('b2c_order_full')->get($order_id);
    $order_detail['real_time'] = 'true';
    return $order_detail;
  }
}