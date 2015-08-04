<?php

 
#require_once('shopObject.php');
define('TEST_INCLUDE',1);
define('TEST_EXCLUDE',2);
define('TEST_BEGIN',3);
define('TEST_END',4);

define('TEST_EQUAL',7);
define('TEST_NOT_EQUAL',8);
define('TEST_GREAT_THAN',9);
define('TEST_LESS_THAN',10);

define('TEST_EARLY_THAN',11);
define('TEST_LATE_THAN',12);
define('TEST_IS_WORKDAY',13);
define('TEST_WEEKEND',14);

class b2c_mdl_trigger {

    var $defaultCols = 'trigger_event,trigger_memo,filter_str,action_str,active';
    var $idColumn = 'trigger_id'; //表示id的列
    var $textColumn = 'filter_str';
    var $tableName = 'sdb_triggers';
    function __construct(&$app){
        $this->app = $app;
         if(!$this->typeName) $this->typeName = substr(strstr(get_class($this),'_'),1);
        $this->trigger_points = array(
//            'goods/products'=>__('商品'),
            'trading/order'=>app::get('b2c')->_('订单'),
            'member/account'=>app::get('b2c')->_('会员'),
//            'system'=>__('系统'),
        );
        $this->listeners = $this->app->getConf('system.event_listener');
        
    }
 

    /**
     * object_fire_event
     * 执行对象事件
     *
     * @param mixed $action
     * @param mixed $object
     * @param mixed $member_id
     * @param mixed $target
     * @access public
     * @return void
     */
    function object_fire_event($action , &$object, $member_id,&$target){
        if(false===strpos($action,':')){
            $trigger_event = $target->table_name().':'.$action;
            $modelName = $target->table_name();
        }else{
            $trigger_event = $action;
            list($modelName,$action) = explode(':',$action);
        }
        $typeName = substr(strstr(get_class($target),'_'),1);
        $aType = explode('_',$typeName);
        foreach($aType as $val){
            $type = $val;
        }
        #$type = "order";
        
        $app_id = $target->app->app_id;
        $this->app->messenger = &$this->app->model('member_messenger');
        $this->app->_msgList = $this->app->messenger->actions();
        $result = true;
        if($this->app->_msgList[$type.'-'.$action]){
            $result=$this->app->messenger->actionSend($type.'-'.$action,$object,$member_id);
        }
        
        if(defined('DISABLE_TRIGGER') && DISABLE_TRIGGER){
            return $result;
        }else{
            // triggers 待续...
            if (isset($this->listeners) && $this->listeners)
                foreach(array_merge((array)$this->listeners['*'],
                    (array)$this->listeners[$app_id.":".$target->table_name().':*'],
                    (array)$this->listeners[$app_id.":".$target->table_name().':'.$action])
                    as $func){
                    list($mod,$func) = $this->get_func($func);
                    $mod->$func($action,$object);
                }

            return $result;
        }
    }

    private function get_func($position)
    {
        list($package,$class,$method) = explode(':',$position);
        $class = $package.'_'.$class;
        
        $obj = kernel::single($class);        
        return array(&$obj,$method);
    }

}
