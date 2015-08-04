<?php
/*
 * Created on 2011-12-14
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class poster_ctl_admin_position extends desktop_controller{
    function index(){
    $this->finder('poster_mdl_position',
        array(
            'title'=>'广告位置管理',
                'actions'=>array(
                    array(
                        'label'=>'添加广告位置',
                        'href'=>'index.php?app=poster&ctl=admin_position&act=showNew',
                        'target'=>'_blank',

                    ),
                    array('label'=>app::get('b2c')->_('删除'),'submit'=>'index.php?app=poster&ctl=admin_position&act=delePosition','target'=>'refresh'),
                ),
                'use_buildin_filter'=>true,//是否显示高级筛选
                //'allow_detail_popup'=>true,//是否显示查看列中的弹出查看图标
                //'use_view_tab'=>true,//是否显示lab,要看是否有_views方法
                'use_buildin_recycle'=>false,

            )
        );
    }

     function showNew(){
         if($id = $_GET["id"]){
            $postObj = $this->app->model('position');
            $row = $postObj->dump(array('position_id'=>$id),'*');
            $this->pagedata['position']=$row;
         }
         $this->pagedata['finder_id'] = $_GET['finder_id'];
         $this->singlepage('admin/positionEdit.html');
     }
     
     function delePosition(){
        $this->begin('');
        $postObj = $this->app->model('position');
        $position_ids=implode(',',$_POST['position_id']);
        $postObj->db->exec('delete from sdb_poster_position where position_id in ('.$position_ids.')');
        $this->end(true,app::get('poster')->_('操作成功'));
       
     }

    function save(){
        $posterObj = $this->app->model('position');
        $data = array();
        if(isset($_POST['position_id']))
            $data['position_id'] = intval($_POST['position_id']);
        $data['position_name'] = isset($_POST['position_name'])?$_POST['position_name']:'';
        $posterObj->save($data);
    }


}


