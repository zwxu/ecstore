<?php

 
class b2c_mdl_member_systmpl extends dbeav_model{
    
     function __construct($app){
        parent::__construct($app);
    }
    function fetch($tplname,$data=null){
        $aTmpl = explode(':',$tplname);
        $render = $this->app->render();
        foreach($data as $key=>$val){
            $render->pagedata[$key] = $val;
        }
        if(count($aTmpl) != 1){
             $aRet = $this->getList('*',array('active'=>'true','tmpl_name'=>$tplname));
            if($aRet){
                return $render->fetch("messenger:".$tplname);    
            }
            $aTp = explode('/',$aTmpl[1]);
            $aLast = explode('_',$aTp[0]);
            $app_id = $aLast[0];
			$obj_app_id = kernel::service('b2c_messenger_tpl_appid');
			if ($obj_app_id && method_exists($obj_app_id, 'get_app_id')){
				$obj_app_id->get_app_id($aTp[1], $app_id);
			}
            $aLast[0] = 'admin';
            $dir = implode('/',$aLast);
            return $render->fetch($dir.'/'.$aTp[1].'.html',$app_id);
        }
        else{
            $aRet = $this->getList('*',array('active'=>'true','tmpl_name'=>'/'.$tplname));
            if($aRet){
                return $render->fetch("messenger:".'/'.$tplname);    
            }
            return $render->fetch($tplname.'.html');
        }
        
    }

    function getTitle($ident){
        $row = $this->db->select('select title,path from sdb_sitemaps where action=\'page:'.$this->db->quote($ident).'\'');
        if($row[0]['path']){
            $row[0]['path']=substr($row[0]['path'],0,strlen($row[0]['path'])-1);
            $parentRow=$this->db->select('select title,action as link from sdb_sitemaps where node_id in ('.$row[0]['path'].')');
            $parentRow[]=array('title'=>$row[0]['title'],'link'=>$row[0]['action']);
            return $parentRow;
        }

        return $row;
    }

    function _file($name){
        if($p = strpos($name,':')){
            $type = substr($name,0,$p);
            $name=substr($name,$p+1);
            if($type=='messenger'){
                $aTmp = explode('/',$name);
                $tmpl = explode('_',$aTmp[0]);
                $app_id = $tmpl[0];
                $tmpl[0] = "view/admin";
                $html_dir = implode('/',$tmpl).'/'.$aTmp[1];
				
				$obj_app_id = kernel::service('b2c_messenger_tpl_appid');
				if ($obj_app_id && method_exists($obj_app_id, 'get_app_id')){
					$obj_app_id->get_app_id($aTmp[1], $app_id);
				}
				
                return ROOT_DIR.'/app/'.$app_id.'/'.$html_dir.'.html';
            }
        }
        else{
            return ROOT_DIR.'/app/b2c/view/'.$name.'.html';
        }
    }

    function get($name){
           $aRet = $this->getList('*',array('active'=>'true','tmpl_name'=>$name));
           if($aRet){
            return $aRet[0]['content'];
        }else{
            return file_get_contents($this->_file($name));
        }
    }

    function clear($name,&$msg=''){
        $sdf = $this->dump($name);
		if (!$sdf) {
			$msg = app::get('b2c')->_('打印样式未保存过，无需恢复！');
			return false;
		}
        $sdf['edittime'] = time();
        $sdf['active'] = 'false';
        return $this->save($sdf);
    }

    function tpl_src($matches){
        return '<{'.html_entity_decode($matches[1]).'}>';
    }

    function set($name,$body){
        //file_put_contents($this->_file($name),$body);
        $body = str_replace(array('&lt;{','}&gt;'),array('<{','}>'),$body);
        $body = preg_replace_callback('/<{(.+?)}>/',array(&$this,'tpl_src'),$body);
        $sdf['tmpl_name'] = $name;
        $sdf['edittime'] = time();
        $sdf['active'] = 'true';
        $sdf['content'] = $body;
        $rs = $this->save($sdf);
        return $rs;
    }
}
