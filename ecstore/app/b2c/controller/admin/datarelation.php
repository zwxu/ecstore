<?php
 
 

class b2c_ctl_admin_datarelation extends desktop_controller
{
    var $workground = 'desktop_other';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    /**
     * 网站通信发送初始数据管理
     * @param null
     * @return null
     */
    public function index()
    {
        $this->finder('base_mdl_rpcpoll',array(
            'title'=>app::get('b2c')->_('数据通信管理'),
            'base_filter'=>array('type'=>'request'),
            'use_buildin_recycle'=>false,
            'use_buildin_view'=>false,
            'use_buildin_selectrow'=>false,
        ));
    }
    
    /**
     * 重新发起数据同步的请求
     * @param string id - 序号
     * @param int call-time 发起时间
     */
    public function re_request($order_no, $call_time)
    {
        $this->begin();
        if (!$order_no || !$call_time)
        {
            $this->end(false, app::get('b2c')->_('发起请求参数不全！'));
        }
        
        $obj_base_rpcpoll = app::get('base')->model('rpcpoll');
        $tmp = $obj_base_rpcpoll->getList('*', array('id'=>$order_no,'calltime'=>$call_time));
        if ($tmp)
        {
            $arr_rpcpoll = $tmp[0];
            $arr_callback = explode(':', $arr_rpcpoll['callback']);
            $callback = array(
                'class'=>$arr_callback[0],
                'method'=>$arr_callback[1],
            );
            $rpc_poll_key = $arr_rpcpoll['id'] . '-' . $arr_rpcpoll['calltime'];
            // 与中心交互
            $obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
            if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_recaller_request'))
            {
                if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
                    $obj_rpc_request_service->rpc_recaller_request($arr_rpcpoll['method'], $arr_rpcpoll['params'], $callback, $arr_rpcpoll['method'], 1, $rpc_poll_key);
            }
            else
            {
                $obj_rpc_request = kernel::single('b2c_order_data_relation');                
                $obj_rpc_request->form_request($arr_rpcpoll['method'], $arr_rpcpoll['params'], $callback, $arr_rpcpoll['method'], 1, $rpc_poll_key);
            }
            
            $this->end(true, app::get('b2c')->_('重新发送成功！'));
        }
        else
        {
            $this->end(false, app::get('b2c')->_('发起的请求不存在！'));
        }
    }
}