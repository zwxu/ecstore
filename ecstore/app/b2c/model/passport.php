<?php

 

class b2c_mdl_passport extends dbeav_model{
    
      function __construct(&$app){
        $this->app = $app;
        $this->columns = array(
                        'passport_name'=>array('label'=>app::get('b2c')->_('通行证'),'width'=>200),
                        'passport_status'=>array('label'=>app::get('b2c')->_('启用'),'type'=>'bool','width'=>100),
                        'passport_version'=>array('label'=>app::get('b2c')->_('版本'),'width'=>200),
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
                    $a_temp = $service->get_config();      
                    $item['passport_id'] = $a_temp['passport_id']['value'];
                    $item['passport_name'] = $a_temp['passport_name']['value'];
                    $item['passport_status'] = $a_temp['passport_status']['value'];     
                    $item['passport_version'] = $a_temp['passport_version']['value'];           
                    $ret[] = $item;      
                }
            }
            
            return $ret;

    }
    
}
