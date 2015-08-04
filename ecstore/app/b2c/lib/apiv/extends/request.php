<?php


class b2c_apiv_extends_request implements b2c_apiv_interface_request{
  //变量需重定义
  var $method = '';
  var $callback = array();
  var $title = '未定义标题';
  var $timeout = 1;
  var $async = true;

  public function get_method(){
    return $this->method;
  }
  
  //必须重载 
  public function get_params($sdf){

  }

  public function get_callback(){
    return $this->callback;
  }

  public function get_title(){
    return $this->title;
  }

  public function get_timeout(){
    return $this->timeout;
  }

  public function is_async(){
    return $this->async;
  }
}