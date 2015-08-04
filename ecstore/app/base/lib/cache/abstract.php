<?php


/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
abstract class base_cache_abstract 
{

    /*
     * @var string $_vary_list
     * @access protected
     */
    protected $_vary_list;

    /*
     * 检查是否取得影响缓存的相关数据，如果无则获取一次
     * @access public
     * @return void
     */
    protected function check_vary_list() 
    {
        if(!isset($this->_vary_list)){
            $this->_vary_list = cachemgr::fetch_vary_list();
        }
    }//End Function

    /*
     * 取得数据
     * @var string $type
     * @var string $key
     * @access public
     * @return mixed
     */
    public function get_modified($type, $key) 
    {
        return $this->_vary_list[strtoupper($type)][strtoupper($key)];
    }//End Function

    /*
     * 设置数据
     * @var string $type
     * @var string $key
     * @var int $time
     * @access public
     * @return boolean
     */
    public function set_modified($type, $key, $time=0) 
    {
        $now = ($time>0) ? $time : time();
        if(is_array($key)){
            foreach($key as $k){
                $this->_vary_list[strtoupper($type)][strtoupper($k)] = $now;
            }
        }else{
            $this->_vary_list[strtoupper($type)][strtoupper($key)] = $now;
        }
        return true;
    }//End Function
}//End Class