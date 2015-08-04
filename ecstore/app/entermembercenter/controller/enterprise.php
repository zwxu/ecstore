<?php

 
class entermembercenter_ctl_enterprise extends desktop_controller{

    function index(){

        $this->entid = base_enterprise::ent_id();
        $this->ent_email = base_enterprise::ent_email();
        if(empty($this->entid) ||empty($this->ent_email)){
            $this->pagedata['enterprise'] = false;
        }else{
            $this->pagedata['enterprise'] = true;
        }
        $this->pagedata['entid'] = $this->entid;
		$this->pagedata['ent_email'] = $this->ent_email;
        $this->pagedata['debug'] = false;

        $this->page('enterprise.html');
    }

    function upLicense(){
    	if ( $_FILES ){
    		if ( $_FILES['enterprise']['name'] ){
	    		$fileName = explode( '.', $_FILES['enterprise']['name'] );
	    		if ( 'CER' != $fileName['1'] ){
	    		    echo "<script>parent.MessageBox.error('".app::get('entermembercenter')->_('"企业帐号格式不对"')."');</script>";
	    		    return;
	    		}
	    		else {
			        $content = file_get_contents($_FILES['enterprise']['tmp_name']);
			        list($entid,$ent_ac,$ent_email) = explode('|||',$content);
			        $result = base_enterprise::set_enterprise_info(array('ent_id'=>$entid,'ent_ac'=>$ent_ac,'ent_email'=>$ent_email));
			        if(!$result){
			            header("Content-type:text/html; charset=utf-8");
			            echo "<script>parent.MessageBox.error('".app::get('entermembercenter')->_('"企业帐号重置失败,请先上传文件"')."');</script>";
			        }else{
						// 删除证书和node_id.
						base_certificate::del_certificate();
						$obj = kernel::single('base_shell_buildin');
						$obj->command_inactive_node_id('ceti_node_id');
			            header("Content-type:text/html; charset=utf-8");
			            echo "<script>parent.MessageBox.success('".app::get('entermembercenter')->_('"企业帐号上传成功"')."');</script>";
			        }
	    		}
    		}
    		else {
    		    echo "<script>parent.MessageBox.error('".app::get('entermembercenter')->_('"请选择要上传的文件"')."');</script>";
    		}
    	}
    }
    function download(){
        header("Content-type:application/octet-stream;charset=utf-8");
        header("Content-Type: application/force-download");
        $this->fileName = 'enterprise.CER';
        header("Content-Disposition:filename=".$this->fileName);

        $this->ent_id = base_enterprise::ent_id();
        $this->ent_ac = base_enterprise::ent_ac();
		$this->ent_email = base_enterprise::ent_email();
        echo $this->ent_id;
        echo '|||';
        echo $this->ent_ac;
		echo '|||';
        echo $this->ent_email;
    }
}

