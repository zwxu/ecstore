<?php

 

/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 * 为了数据安全，请确保persistent方法的调用正确
 */
class base_kvstore{

    /*
     * @var string $__instance
     * @access static private
     */
    static private $__instance = array();

    /*
     * @var string $__persistent
     * @access static private
     */
    static private $__persistent = true;

    /*
     * @var string $__controller
     * @access private
     */
    private $__controller = null;

    /*
     * @var string $__prefix
     * @access private
     */
    private $__prefix = null;
    
    /*
     * @var string $__fetch_count
     * @access static public
     */
    static public $__fetch_count = 0;

    /*
     * @var string $__store_count
     * @access static public
     */
    static public $__store_count = 0;

    /*
     * 构造
     * @var string $prefix
     * @access public
     * @return void
     */
    function __construct($prefix){
        if(defined('FORCE_KVSTORE_STORAGE') && constant('FORCE_KVSTORE_STORAGE')){
            $this->set_controller(kernel::single(FORCE_KVSTORE_STORAGE, $prefix));
        }else{
            if(defined('KVSTORE_STORAGE') && constant('KVSTORE_STORAGE')){
                $this->set_controller(kernel::single(KVSTORE_STORAGE, $prefix));
            }else{
                $this->set_controller(kernel::single('base_kvstore_filesystem', $prefix));
            }
        }
        $this->set_prefix($prefix);
    }//End Function

    /*
     * 设置持久化与否
     * @var boolean $flag
     * @access public
     * @return string
     */
    static function config_persistent($flag) 
    {
        self::$__persistent = ($flag) ? true : false;
    }//End Function

    /*
     * 返回KV_PREFIX
     * @access public
     * @return string
     */
    static public function kvprefix() 
    {
        #return (defined('KV_PREFIX')) ? KV_PREFIX : 'default';
        return (defined('KV_PREFIX')) ? KV_PREFIX : 'defalut'; // define里的KV_PREFIX从第一版就写错了，所以这里只好将错就错。。
    }//End Function

    /*
     * 实例一个kvstore
     * @var string $prefix
     * @access public
     * @return object
     */
    static public function instance($prefix){
        if(!isset(self::$__instance[$prefix])){
            self::$__instance[$prefix] = new base_kvstore($prefix);
        }
        return self::$__instance[$prefix];
    }//End Function

    /*
     * 设置prefix
     * @var string $prefix
     * @access public
     * @return void
     */
    public function set_prefix($prefix) 
    {
        $this->__prefix = $prefix;
    }//End Function

    /*
     * 取得prefix
     * @access public
     * @return string
     */
    public function get_prefix() 
    {
        return $this->__prefix;
    }//End Function

    /*
     * 设置kvstore控制器
     * @var object $controller
     * @access public
     * @return void
     */
    public function set_controller($controller) 
    {
        if($controller instanceof base_interface_kvstore_base){
            $this->__controller = $controller;
        }else{
            throw new Exception('this instance must implements base_interface_kvstore_base');
        }
    }//End Function

    /*
     * 得到kvstore控制器
     * @access public
     * @return object
     */
    public function get_controller() 
    {
        return $this->__controller;
    }//End Function

    /*
     * 自增
     * @var string $key
     * @var int $offset
     * @access public
     * @return int
     */
    public function increment($key, $offset=1) 
    {
        if($this->get_controller() instanceof base_interface_kvstore_extension){
            return $this->get_controller()->increment($key, $offset);
        }else{
            throw new Exception('this instance can\'t support increment');
        }
    }//End Function

    /*
     * 自减
     * @var string $key
     * @var int $offset
     * @access public
     * @return int
     */
    public function decrement($key, $offset=1) 
    {
        if($this->get_controller() instanceof base_interface_kvstore_extension){
            return $this->get_controller()->decrement($key, $offset);
        }else{
            throw new Exception('this instance can\'t support decrement');
        }
    }//End Function

    /*
     * 获取key的内容
     * @var string $key
     * @var mixed &$value
     * @var int $timeout_version
     * @access public
     * @return boolean
     */
    public function fetch($key, &$value, $timeout_version=null){
        self::$__fetch_count++;
        if($this->get_controller()->fetch($key, $value, $timeout_version)){
            return true;
        }else{
            return false;
        }
    }//End Function

    /*
     * 设置key的内容
     * @var string $key
     * @var mixed $value
     * @var int $ttl
     * @access public
     * @return boolean
     */
    public function store($key, $value, $ttl=0)
    {
        self::$__store_count++;
        if(!(defined('WITHOUT_KVSTORE_PERSISTENT') && constant('WITHOUT_KVSTORE_PERSISTENT')) && self::$__persistent && get_class($this->get_controller())!='base_kvstore_mysql' && kernel::is_online()){
            $this->persistent($key, $value, $ttl);
        }
        return $this->get_controller()->store($key, $value, $ttl);
    }//End Function

    /*
     * 删除key的内容
     * @var string $key
     * @var int $ttl
     * @access public
     * @return boolean
     */
    public function delete($key, $ttl=1) 
    {
        if($this->fetch($key, $value)){
            return $this->store($key, $value, ($ttl>0)?$ttl:1);    //todo: 不实际删除，由cron统一处理delete
        }
        return true;
    }//End Function

    /*
     * 数据持久化
     * @var string $key
     * @var mixed $value
     * @var int $ttl
     * @access public
     * @return void
     */
    public function persistent($key, $value, $ttl=0) 
    {
        kernel::single('base_kvstore_mysql', $this->get_prefix())->store($key, $value, $ttl);  //todo: 持久化
    }//End Function
    
    /*
     * 数据还原
     * @var array $record
     * @access public
     * @return boolean
     */
    public function recovery($record) 
    {
        return $this->get_controller()->recovery($record);
    }//End Function

    /*
     * 删除过期数据
     * @var array $record
     * @access public
     * @return boolean
     */
    static public function delete_expire_data() 
    {
        $time = time();
        $kv_obj = base_kvstore::instance('')->get_controller();
         if(method_exists($kv_obj, 'delete_expire_data'))
            $kv_obj->delete_expire_data();
         kernel::database()->exec('DELETE FROM sdb_base_kvstore WHERE ttl>0 AND (dateline+ttl)<'.$time, true);
    }//End Function

}//End Class
