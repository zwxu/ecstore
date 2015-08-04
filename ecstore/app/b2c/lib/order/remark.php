<?php

 

class b2c_order_remark extends b2c_api_rpc_request
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->objMath = kernel::single('ectools_math');
        
        $this->arr_market_type = array(
            'b1' => '1',
            'b2' => '7',
            'b3' => '2',
            'b4' => '4',
            'b5' => '8',
            'b0' => '0',
        );
    }
    
    /**
     * 订单备注添加
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$msg='')
    {
        // 备注订单是和中心的交互
        $order = $this->app->model('orders');
        $data['order_id'] = $sdf['orderid'];
        $data['mark_text'] = $sdf['mark_text'];
        $data['mark_type'] = $sdf['mark_type'];
        
        $is_success = $order->save($data);
        
        if ($is_success)
        {
            $this->request($sdf, 'store.trade.memo.add');
            return true;
        }
        else
        {
            $msg = app::get('b2c')->_("订单备注保存失败！");
            return false;
        }
    }
    
    /**
     * 订单备注添加
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function update(&$sdf, &$msg='')
    {
        // 备注订单是和中心的交互
        $order = $this->app->model('orders');
        $arr_order = $order->getList('*', array('order_id'=>$sdf['orderid']));
        if ($arr_order[0])
        {
            if ($arr_order[0]['mark_text'])
            {
                $arr_order[0]['mark_text'] = unserialize($arr_order[0]['mark_text']);
            }
            
            $arr_order[0]['mark_text'][] = array(
                'mark_text' => str_replace("\n",' ',$sdf['mark_text']),
                'add_time' => time(),
                'op_name' => $sdf['op_name'],
            );
            $arr_order[0]['mark_text'] = serialize($arr_order[0]['mark_text']);
        }
        $data['order_id'] = $sdf['orderid'];
        $data['mark_text'] = $arr_order[0]['mark_text'] ? $arr_order[0]['mark_text'] : $sdf['mark_text'];
        $data['mark_type'] = $sdf['mark_type'];
        
        $is_success = $order->save($data);
        if ($is_success)
        {
            $this->request($sdf, 'store.trade.memo.update');
            return true;
        }
        else
        {
            $msg = app::get('b2c')->_("订单备注保存失败！");
            return false;
        }
    }
    
    /**
     * 订单取消事件埋点
     * @param array sdf
     * @param string method
     * @return boolean success or failure
     */
    protected function request(&$sdf, $method)
    {
        // 回朔待续...
        $arr_data['tid'] = $sdf['orderid'];
        $arr_data['flag'] = $this->arr_market_type[$sdf['mark_type']];
        /*$arr_data['memo'] = array(
            'op_name' => $sdf['op_name'],
            'op_time' => date('Y-m-d H:i:s'),
            'op_content' => $sdf['mark_text'],
        );*/
        $arr_data['memo'] = $sdf['mark_text'];
        $arr_data['title'] = '';
        $arr_data['sender'] = $sdf['op_name'];
        $arr_data['add_time'] = date('Y-m-d H:i:s');
        //$arr_data['memo'] = json_encode($arr_data['memo']);
        
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => $method,
                'tid' => $sdf['orderid'],
            ),
        );
        //$rst = $this->app->matrix()->set_callback('b2c_api_callback_app','callback',array('method'=>$method))->call($method, $arr_data);
        parent::request($method, $arr_data, $arr_callback, 'Order Remark', 1);
        
        return true;
    }
}