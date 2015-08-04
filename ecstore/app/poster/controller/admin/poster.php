<?php
/*
 * Created on 2011-12-14
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class poster_ctl_admin_poster extends desktop_controller{
    function index(){
    $this->finder('poster_mdl_poster',
        array(
            'title'=>'广告管理',
                'actions'=>array(
                    array(
                        'label'=>'添加广告',
                        'href'=>'index.php?app=poster&ctl=admin_poster&act=addPoster',
                        'target'=>'_blank',

                    ),
                    array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=poster&ctl=admin_poster&act=delePoster','target'=>'refresh'),
                ),
                'use_buildin_filter'=>true,//是否显示高级筛选
                'allow_detail_popup'=>true,//是否显示查看列中的弹出查看图标
                'use_view_tab'=>true,//是否显示lab,要看是否有_views方法
                'use_buildin_recycle'=>false,

            )
        );
    }

     function addPoster(){
         $postObj = $this->app->model('poster');
         $poster_type = $postObj->get_poster_type();
         $this->pagedata['poster_type'] = $poster_type;

         if($id = $_GET["id"]){
            $row = $postObj->getList('*',array('poster_id'=>$id));
            $this->pagedata['pics']=$row[0]['poster_imgurl'];
            $this->pagedata['poster'] = $row[0];
            $this->pagedata['edit'] = 1;

         }
         $this->pagedata['finder_id'] = $_GET['finder_id'];
         $desktop_user = new desktop_user();
         $this->pagedata['poster']['poster_author']=$desktop_user->get_login_name();
         $this->singlepage('admin/posterEdit.html');
     }
     
     function delePoster(){
        $this->begin('');
        $mposter=kernel::single('poster_mdl_poster');
        $poster_ids=implode(',',$_POST['poster_id']);
        $mposter->db->exec('delete from sdb_poster_poster where poster_id in ('.$poster_ids.')');
        $this->end(true,app::get('poster')->_('删除成功'));
       
     }

          /*
     * 删除图片
     */
    function delPic(){
        
        $file_path=ROOT_DIR.$_GET['filepath'];
        if(file_exists($file_path)){
                    if(unlink($file_path)){
                       echo "<script type='text/javascript'>alert('删除成功!')</script>";
                    }else{
                       echo "<script type='text/javascript'>alert('上传文件无法删除,对该文件没有读写权限!')</script>";
                    }
         }
    }
    

     
    function toEdit(){
        $desktop_user = new desktop_user();
        $posterObj = $this->app->model('poster');
        $data = array();
        if(isset($_POST['poster']['poster_id']))
            $data['poster_id'] = $_POST['poster']['poster_id'];
        $data['poster_switcheffect'] = isset($_POST['poster_switcheffect'])?$_POST['poster_switcheffect']:'';
        $data['poster_autoplay'] = isset($_POST['poster_autoplay'])?$_POST['poster_autoplay']:'';
        $data['poster_position'] = $_POST['poster']['poster_position'];
        $data['poster_type'] = $_POST['poster']['poster_type'];
        $data['poster_isblank'] = $_POST['poster']['poster_isblank'];
        $data['poster_starttime']=strtotime($_POST['poster_starttime'].' '.$_POST['_DTIME_']['H']['poster_starttime'].':'.$_POST['_DTIME_']['M']['poster_starttime'].':0');
        $data['poster_endtime']=strtotime($_POST['poster_endtime'].' '.$_POST['_DTIME_']['H']['poster_endtime'].':'.$_POST['_DTIME_']['M']['poster_endtime'].':0');
        $data['poster_imgurl']=$_POST['pic'];
        $data['poster_author']=$desktop_user->get_login_name();
        $data['poster_createtime']=$_POST['poster']['poster_createtime']?$_POST['poster']['poster_createtime']:time();
        $data['poster_updatetime']=time();
        $posterObj->save($data);
    }
        
        /**
     * 截取文件名不包含扩展名
     * @param 文件全名，包括扩展名
     * @return string 文件不包含扩展名的名字
     */
    private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }
    
    
          //截取文件后缀名
        function extend_1($file_name){       
            $retval=""; 
            $pt=strrpos($file_name, ".");
            if ($pt)
            $retval=substr($file_name, $pt+1, strlen($file_name) - $pt);
            return ($retval);
        }
        
        //截取文件名
        function extend_2($file_name){
            $retval="";
            $pt=strrpos($file_name,'.');
            if($pt){
                $retval=substr($file_name,0,$pt);
            }
            return $retval;
        }
        
        //循环判断文件是否存在
        function fileisexists($apply_path,$file_name){
            $i=0;
            $filecount=1;
            $fileName='';
            $filepath=$apply_path.$file_name;
            do{
                if(file_exists($filepath)){
                    $fileName=$this->extend_2($file_name).'_'.$filecount.'.'.$this->extend_1($file_name);
                    
                    $filepath=$apply_path.$fileName;
                    echo $filepath;
                }
                if(file_exists($filepath)){
                    $i=0;
                }else{
                    $i=1;
                }
                $filecount++;
            }while($i<1);
            return $fileName==''?$file_name:$fileName;
        }

 }
 

