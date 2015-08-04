<?php


/**
 * 这个类实现报表的数据统计和显示的接口
 * @interface
 * 
 * @version 0.1
 * @package ectools.lib.analysis
 */
interface ectools_analysis_interface 
{
	/**
     * 得到报表日志-各种报表各自实现
     * @param string time
     * @return array 日志信息
     */
    public function get_logs($time);
	
    /**
     * 设置报表统计的参数
     * @param array 需要设置的参数
     * @return object 本类对象
     */
    public function set_params($params);
	
    /**
     * 设置extra视图
     * @param array view视图数组
     * @return object 本类对象
     */
    public function set_extra_view($array);
	
    /**
     * 统计的类型-内容
     * @param null
     * @return string 类型值
     */
    public function get_type();
	
    /**
     * 设置图像方法，设置页面参数
     * @param null
     * @return boolean 成功与否
     */
    public function graph();
	
    /**
     * 统计频率
     * @param null
     * @return string 频率值
     */
    public function rank();
	
    /**
     * 生成页面详细区域信息
     * @param null
     * @return boolean 成功与否
     */
    public function detail();
	
    /**
     * 生成各自统计内容的finder
     * @param null
     * @return array - finder统一格式的数组
     */
    public function finder();
	
    /**
     * fetch 页面的html
     * @param null
     * @return string html页面nei'ron
     */
    public function fetch();
	
    /**
    * 展示页面内容的方法
    * @param boolean true - 提出内容，相当于fetch，false echo内容
    * @return string html结果内容
    */
    public function display($fetch=false);
        
}//End Class