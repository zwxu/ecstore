<?php

 
interface b2c_order_service_interface
{
    /**
     * 得到商品的类型
     * @param null
     * @return string goods type
     */
    public function get_goods_type();
    /**
     * 库存冻结
     * @params array - 参数列表数组
     * @return boolean - 冻结是否成功
     */
    public function freezeGoods($arrParams=array());
    
    /**
     * 库存解冻
     * @params array - 参数列表数组
     * @return boolean - 冻结是否成功
     */
    public function unfreezeGoods($arrParams=array());
    
    /**
     * 库存修改 - 裁剪库存
     * @params array - 参数列表数组
     * @return boolean - 修改是否成功
     */
    public function minus_store($arrParams=array());
    
    /**
     * 库存修改 - 回复库存
     * @params array - 参数列表数组
     * @return boolean - 修改是否成功
     */
    public function recover_store($arrParams=array());
    
    /**
     * 生成相应类型的订单结构
     * @param array - 生成订单必须的信息
     * @param array - 返回相应的信息 - 订单sdf数组 - 引用变量
	 * @param string - message
     * @return boolean - 是否成功的消息
     */
    public function gen_order($arrParams=array(), &$arr_data, &$msg='');
    
    /**
     * 返回goods的数据 通过传地址的方式来获取数据
     * @param mixed 得到目标的信息 - 可能是商品的id，或是对象数组
     * @param array - 目标数据数组
	 * @param array - 目标模版
     * @return string - html
     */
    public function get_order_object($arr_object=array(), &$arrGoods, $tml='member_order_detail');
    
    /**
     * 取到快递单的数据
     * @param sdf item list or object list
     * @param array 快递单打印数据
     * @return boolean -是否成功的消息
     */
    public function get_default_dly_order_info($val_list=array(),&$data);
	
	/**
     * 判断库存是否可以满足冻结值
     * @param mixed 需要冻结的订单或商品的信息
     * @return boolean -是否成功的消息
     */
    public function check_freez($arrParams);
	
	/**
	 * 取到售后数据
	 * @param mixed order item
	 * @return mixed - 是否成功的消息
	 */
	public function get_aftersales_order_info($arr_data);
	
	/**
	 * 改捆绑商品可以被拆分处理还是不能被拆分处理
	 * @param string goods_type
	 * @return boolean
	 */
	public function is_decomposition($goods_type);
	
	/**
	 * 是否可以参加编辑
	 * @param string item_type
	 * @return boolean
	 */
	public function is_item_edit($item_type);
}