<?php

 
class b2c_mdl_member_account extends dbeav_model{

    var $name='会员';
       var $typeName = null;
      function __construct(&$app){
        $this->app = $app;
         if(!$this->typeName) $this->typeName = substr(strstr(get_class($this),'_'),1);
        #print_r($this->typeName);
    }


 /**
     * fireEvent 触发事件
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    function fireEvent($action , &$object, $member_id=0){
         $trigger = &$this->app->model('trigger');
         return $trigger->object_fire_event($action,$object, $member_id,$this);
    }
}
?>
