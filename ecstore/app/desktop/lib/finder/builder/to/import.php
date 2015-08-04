<?php

 
class desktop_finder_builder_to_import extends desktop_finder_builder_prototype{

    function main(){

        if( !$_FILES['import_file']['name'] ){
            header("content-type:text/html; charset=utf-8");
            echo '<script>top.MessageBox.error("上传失败");alert("未上传文件");</script>';
            exit;
        }
        $oQueue = app::get('base')->model('queue');
        $tmpFileHandle = fopen( $_FILES['import_file']['tmp_name'],"r" );
       
        $mdl = substr($this->object_name,strlen( $this->app->app_id.'_mdl_'));

        $oIo = kernel::servicelist('desktop_io');
        foreach( $oIo as $aIo ){
            if( $aIo->io_type_name == substr($_FILES['import_file']['name'],-3 ) ){
                $oImportType = $aIo;
                break;
            }
        }
        unset($oIo);
        if( !$oImportType ){
            header("content-type:text/html; charset=utf-8");
            echo '<script>top.MessageBox.error("上传失败");alert("导入格式不正确");</script>';
            exit;
        }
        
        $contents = array();
        $oImportType->fgethandle($tmpFileHandle,$contents);
        $newFileName = $this->app->app_id.'_'.$mdl.'_'.$_FILES['import_file']['name'].'-'.time();
 
        base_kvstore::instance($this->app->app_id.'_'.$mdl)->store($newFileName,serialize($contents));
        base_kvstore::instance($this->app->app_id.'_'.$mdl)->store($newFileName.'_sdf',serialize(array()));
        base_kvstore::instance($this->app->app_id.'_'.$mdl)->store($newFileName.'_error',serialize(array()));

 //       base_kvstore::instance($this->app->app_id.'_'.$mdl)->store($newFileName.'_msg',serialize(array()));

        fclose($tmpFileHandle);
 
        $oo = kernel::single('desktop_finder_builder_to_run_import');
        $msgList = $oo->turn_to_sdf($aaa,array(
                    'file_type' => substr( $_FILES['import_file']['name'],-3 ),
                    'app' => $this->app->app_id,
                    'mdl' => $mdl,
                    'file_name' => $newFileName
                ));
        $msg = array();
        if( $msgList['error'] )
            $rs = array('failure',$msgList['error']);
        else
            $rs = array('success',$msgList['warning']);
        /*
            $queueData = array(
                'queue_title'=>$mdl.app::get('desktop')->_('转sdf'),
                'start_time'=>time(),
                'params'=>array(
                    'file_type' => substr( $_FILES['import_file']['name'],-3 ),
                    'app' => $this->app->app_id,
                    'mdl' => $mdl,
                    'file_name' => $newFileName
                ),
                'worker'=>'desktop_finder_builder_to_run_import.turn_to_sdf',
            );
            $oQueue->save($queueData); 
         */

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog')){
            if(method_exists($obj_operatorlogs,'importlog')){
                $obj_operatorlogs->importlog($mdl,$_FILES['import_file']['name']);
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        $echoMsg = '';
        if( $rs[0] == 'success' ){
            $o = app::get( $this->app->app_id )->model($mdl);
            $oImportType->model = $o;
            $oImportType->finish_import();

            $echoMsg =app::get('desktop')->_('上传成功 已加入队列 系统会自动跑完队列');
            if($msgList['warning']){
                $echoMsg .= app::get('desktop')->_('但是存在以下问题')."\\n";
                $echoMsg .= implode("\\n",$msgList['warning']);
            }
        }else{
            $echoMsg = app::get('desktop')->_('导入失败 错误如下：'."\\n".implode("\\n",$msgList['error']));
        }

        header("content-type:text/html; charset=utf-8");        
        echo "<script>top.MessageBox.success(\"上传成功\");alert(\"".$echoMsg."\");if(parent.$('import_form').getParent('.dialog'))parent.$('import_form').getParent('.dialog').retrieve('instance').close();if(parent.window.finderGroup&&parent.window.finderGroup['".$_GET['finder_id']."'])parent.window.finderGroup['".$_GET['finder_id']."'].refresh();</script>";
    }

}
