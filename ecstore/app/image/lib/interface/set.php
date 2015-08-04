<?php
 
/** 
 * 定义图片库的设置 
 */
interface image_interface_set{
	/**
	 * 设置配置结构
	 * @param array 数据数组
	 * @return boolean true or false
	 */
    public function setconfig($data);
}

?>