<?php

 
/**
 * 支付方式接口类
 * 定义支付方式的基础方法
 * 适合第三方支付的借口
 * @interface
 * 
 * @version 0.1
 * @package ectools.lib.payment
 */
interface ectools_interface_payment_app
{
	/**
	 * 显示支付接口后台的信息
	 * @params null
	 * @return string - 显示的信息，html格式
	 */
	public function admin_intro();
	
	/**
	 * 设置后台的显示项目（表单项目）
	 * @params null
	 * @return array - 配置的表单项
	 */
	public function setting();
	
	/**
	 * 前台在线支付列表相应项目的说明
	 * @params null
	 * @return string - html格式的
	 */
	public function intro();
	
	/**
	 * 支付表单的提交方式
	 * @params array - 提交的表单数据
	 * @return html - 自动提交的表单
	 */
	public function dopay($payments);
	
	/**
	 * 验证提交表单数据的正确性
	 * @params null
	 * @return boolean 
	 */
	public function is_fields_valiad();
	
	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
	public function callback(&$recv);
	
	/**
	 * 生成支付表单 - 自动提交(点击链接提交的那种方式，通常用于支付方式列表)
	 * @params null
	 * @return null
	 */
	public function gen_form();
}

?>
