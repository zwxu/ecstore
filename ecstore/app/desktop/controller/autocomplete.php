<?php

 
class desktop_ctl_autocomplete extends base_controller{

    function index(){
        $this->_request = kernel::single('base_component_request');
        $params = $this->_request->get_get('params');
        $params = explode(':',$params);
        $svckey = $params[0];
        $cols = explode(',',$params[1]);
        $key = $this->_request->get_get($cols[0]);
        $autocomplete = kernel::servicelist('autocomplete.'.$svckey);
        foreach($autocomplete as $service){
            $return = $service->get_data($key,$cols);
        }
        echo "window.autocompleter_json=".json_encode($return)."";
    }

}
