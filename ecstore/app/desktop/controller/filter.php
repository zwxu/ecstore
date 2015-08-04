<?php

 
class desktop_ctl_filter extends desktop_controller{
    public function __construct($app)
    {
        parent::__construct($app);
    }

    function tab_save(){
        $filter = app::get('desktop')->model('filter');
        $user_id = $this->user->get_id();
        $save = array(
            'filter_query'  =>$_POST['filterquery'],
            'user_id'  =>    $user_id,
            'filter_name'   =>$_POST['filter_name'],
            'create_time'   =>time(),
            'model'         =>$_POST['model'],
            'app'           =>$_POST['app'],
            'ctl'           =>$_POST['ctl'],
            'act'           =>$_POST['act'],
            'extends'       =>unserialize(base64_decode($_POST['extends'])),
        );
        $rows = $filter->getList('*',array('filter_query'=>$save['filter_query'],'model'=>$save['model'],'app'=>$save['app'],'ctl'=>$save['ctl'],'act'=>$save['act'],'user_id'=>$user_id));
        if(!$rows[0]){
            $filter->save($save);
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('desktop')->_('筛选器保存成功').'"}';    
        }else{
            $this->begin();
            $this->end( false,'标签中存在相同的筛选：'.$rows[0]['filter_name'] );
        }
    }
    function tab_del(){
        $filter_id = $_GET['filter_id'];
        if(!$filter_id) exit;
        $filter = app::get('desktop')->model('filter');
        $filter->delete(array('filter_id'=>$filter_id)); 
        header('Content-Type:text/jcmd; charset=utf-8');
        echo '{success:"'.app::get('desktop')->_('筛选器删除成功').'"}';    
    }
}