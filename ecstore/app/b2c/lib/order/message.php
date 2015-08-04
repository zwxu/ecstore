<?php

 

class b2c_order_message extends b2c_api_rpc_request
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    /**
     * 订单备注添加
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$msg='')
    {
        // 订单留言是和中心的交互
        $order = &$this->app->model('orders');
        $arrOrder = $order->dump($sdf['msg']['orderid'], 'member_id');
        $objMember = $this->app->model('members');
        $arrMember = $objMember->dump($arrOrder['member_id'], 'name');
        $oMsg = kernel::single("b2c_message_order");
        $arrData = $sdf;
        $order_id = $sdf['msg']['orderid'];
        $arrData['to_type'] = $sdf['to_type'];
        $arrData['author_id'] = $sdf['author_id'];
        $arrData['author'] = $sdf['author'];
        $arrData['to_id'] = $arrOrder['member_id'];
        $arrData['to_uname'] = $arrMember['contact']['name'];
        
        if (!$oMsg->send($arrData))
        {
            $msg = app::get('b2c')->_('订单留言保存失败！');
            return false;
        }
        else
        {
            $this->request($sdf);
            return true;
        }
    }
    
    /**
     * 订单取消事件埋点
     * @param array sdf
     * @return boolean success or failure
     */
    protected function request(&$sdf)
    {
        // 回朔待续...
        $arr_data['tid'] = $sdf['msg']['orderid'];
        /*$arr_data['message'] = array(
            'op_name' => $sdf['author'],
            'op_time' => date('Y-m-d H:i:s'),
            'op_content' => $sdf['msg']['message'],
        );*/
        $arr_data['message'] = $sdf['msg']['message'];
        $arr_data['title'] = '';
        $arr_data['sender'] = $sdf['author'];
        $arr_data['add_time'] = date('Y-m-d H:i:s');
        //$arr_data['message'] = json_encode($arr_data['message']);       
       
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.buyer_message.add',
                'tid' => $arr_data['tid'],
            ),
        );
        
        // 待续...
        //$rst = $this->app->matrix()->call('store.trade.buyer_message.add', $arr_data);
        parent::request('store.trade.buyer_message.add', $arr_data, $arr_callback, 'Order Message', 1);
        
        return true;
    }
}