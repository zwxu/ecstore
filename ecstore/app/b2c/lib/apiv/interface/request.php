<?php
 

interface b2c_apiv_interface_request
{
    /**
     * get_method
     * 返回请求的方法名
     * @return string
     */
    public function get_method();

    /**
     * get_params
     * 返回请求的参数
     * @params $sdf array
     * @return string
     */
    public function get_params($sdf);

    /**
     * get_callback
     * 返回callback函数
     * @return string
     */
    public function get_callback();

    /**
     * get_title
     * 此次请求的title
     * @return string
     */
    public function get_title();

    /**
     * get_timeout
     * 返回超时时间
     * @return string
     */
    public function get_timeout();

    /**
     * is_async
     * 是否异步： true 异步， false 同步
     * @return string
     */
    public function is_async();
}