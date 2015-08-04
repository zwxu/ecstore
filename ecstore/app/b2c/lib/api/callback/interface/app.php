<?php


/**
 * 所有的callback类的app接口
 * 定义所有的方法
 */
interface b2c_api_callback_interface_app
{
    /**
     * 处理callback所有的方法
     * @param object base_rpc_result object
     * @return null
     */
    public function callback($result);
}