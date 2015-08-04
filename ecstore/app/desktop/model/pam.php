<?php

 

class desktop_mdl_pam extends dbeav_model{
    
      function __construct(&$app){
        $this->app = $app;
        $this->columns = array(
                        'passport_name'=>array('label'=>app::get('desktop')->_('通行证'),'width'=>200),
                        'site_passport_status'=>array('label'=>app::get('desktop')->_('启用'),'type'=>'bool','width'=>100),
                        //'shopadmin_passport_status'=>array('label'=>app::get('desktop')->_('后台启用'),'type'=>'bool','width'=>100),
                        'passport_version'=>array('label'=>app::get('desktop')->_('版本'),'width'=>200),
                   );

        $this->schema = array(
                'default_in_list'=>array_keys($this->columns),
                'in_list'=>array_keys($this->columns),
                'idColumn'=>'passport_id',
                'columns'=>&$this->columns
            );
         
    }
    
    function get_schema(){
        return $this->schema;
    }
    
    function count($filter=''){
        return 0;
        return count($this->getList());
    }
    
    public function getList($cols='*', $filter=array('status' => 'false'), $offset=0, $limit=-1, $orderby=null){
            $services = kernel::servicelist('passport');
            foreach($services as $service){
                if($service instanceof pam_interface_passport){
                    $a_temp = $service->get_config(); // print_r($a_temp);   exit; 
                    $item['passport_id'] = $a_temp['passport_id']['value'];
                    $item['passport_name'] = $a_temp['passport_name']['value'];
                    $item['shopadmin_passport_status'] = $a_temp['shopadmin_passport_status']['value'];
                    $item['site_passport_status'] = $a_temp['site_passport_status']['value'];      
                    $item['passport_version'] = $a_temp['passport_version']['value'];           
                    $ret[] = $item;      
                }
            }
            
            return $ret;

    }
    
}
