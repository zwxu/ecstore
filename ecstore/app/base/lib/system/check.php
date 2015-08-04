<?php
class base_system_check {

    function system_check(){
        $deploy= base_setup_config::deploy_info();
        $check_list = $deploy['installer']['check']; 
        //获取app.xml扩展库
        $this->_appcheck($check_list,$deploy['package']);
        $service = kernel::single('base_system_service');
        foreach($check_list as $key=>$value){
            $show_name = $value['show_name'];
            unset($value['show_name']);
            $method = 'check_'.$key;
            if(is_object($service) && method_exists($service,$method)){
                $check_result[$show_name]=$service->$method($value,$show_name);
            }
        }
        return $check_result;
    }

    function system_check_all(){
        $result_check = $this->system_check();
        

    }

    function system_check_error(){
        $result=array();
        $result_check = $this->system_check();
        foreach($result_check as $key=>$value){
            foreach($value as $val){
                if($val['result'] == 'false'){
                    $result[]=$val['value'];
                }
            }
        }
        return $result;

    }

    function _appcheck(&$check_list,$applist){
        if($applist['app']){
            foreach($applist['app'] as $value){
                $appcheck_list = kernel::single('base_xml')->xml2array(file_get_contents(app::get($value['id'])->app_dir.'/app.xml'),'base_app');
                if($appcheck_list['check']){
                    $library[] = $appcheck_list['check'];
                }
            }

            foreach($library as $value){
                foreach($value as $k=>$val){
                    foreach($val['list'] as $v){
                        array_push($check_list[$k]['list'],$v);
                    }
                }
            }
        }
    }
}
