<?php

 
class b2c_messenger_tmpl{
    
     public function last_modified($tplname) 
    {
        $systmpl = app::get('b2c')->model('member_systmpl');
       $aRet = $systmpl->getList('*',array('active'=>'true','tmpl_name'=>$tplname));
        if($aRet){
              return $aRet[0]['edittime'];    
        }
        return time();
    }

    public function get_file_contents($tplname) 
    { 
       $systmpl = app::get('b2c')->model('member_systmpl');
       $aRet = $systmpl->getList('*',array('active'=>'true','tmpl_name'=>$tplname));
        if($aRet){
              return $aRet[0]['content'];    
        }
        return null;
        
    }

}
?>