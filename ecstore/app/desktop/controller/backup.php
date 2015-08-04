<?php

 
class desktop_ctl_backup extends desktop_controller{

    function index(){
        $this->path[] = array('text'=>app::get('desktop')->_('数据备份'));
        if($time = app::get('shopex')->getConf("system.last_backup")){
            $this->pagedata['time'] = date('Y-m-d H:i:s',$time);
        }
        $this->pagedata['backup'] = 'current';
        kernel::single("desktop_ctl_data")->index();
        $this->page('system/backup/backup.html');
    }



    function backup_sdf(){
        ini_set("max_exection_time", 0);
        header("Content-type:text/html;charset=utf-8");

        $params['dirname'] = ($_GET["dirname"]=="") ? date("YmdHis", time()) : $_GET["dirname"];
        $params['appname'] = $_GET['appname'];
        #$params['cols'] = $_GET['cols'] ? $_GET['cols'] : 0;
        #$params['model'] = $_GET['model'] ? $_GET['model'] : '';
        #$params['startid'] = $_GET['startid'] ? $_GET['startid'] : 0;
        
        $oBackup = kernel::single('desktop_system_backup');
        
        if(!$oBackup->start_backup_sdf($params,$nexturl)){
            if (!$params['appname'])
                echo '{message:"'.app::get('desktop')->_('正在准备备份应用').'",
                   nexturl:"'.$nexturl.'"}';
            else
                echo '{message:"'.app::get('desktop')->_('正在备份应用：').($params['appname']).'",
                   nexturl:"'.$nexturl.'"}';
        }
        else{
            app::get('shopex')->setConf("system.last_backup", time(), true);
           echo '{success:"'.app::get('desktop')->_('备份完成').'",nexturl:"index.php?app=desktop&ctl=backup&act=getFile&file=multibak_'.$params['dirname'].'.zip"}';

        }

    }
    
    
     
    
    
    public function getFile() {
        $file = $_GET['file'];
        if($file && preg_match('/(\..\/){1,}/', $file)){
            header("Content-type: text/html; charset=utf-8");
            echo app::get('desktop')->_('非法操作');exit;;
        }
        kernel::single('desktop_system_backup')->download($file);
    }
    

}
