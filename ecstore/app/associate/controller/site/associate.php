<?php

class associate_ctl_site_associate extends base_controller{

    function index(){
        $param = $this->get('param');
        $params = explode(':',$param);
        $svckey = $params[0];
        $cols = explode(',',$params[1]);
        $key = $this->get($cols[0]);
        $autocomplete = kernel::servicelist('autocomplete.'.$svckey);

        foreach($autocomplete as $service){
            $return = $service->get_data($key,$cols);
        }

        echo "window.autocompleter_json=".json_encode($return)."";
        exit;
    }

    function get($key = null) 
    {
        if($key === null)
            return $_GET;
        return $_GET[$key];
    }

}
