<?php

 
class desktop_sidepanel_dashboard{

    function __construct($app){
        $this->app = $app;
    }
####根据工作组显示侧边栏菜单
    function get_output(){ 
        $render = $this->app->render();
        $act = app::get('desktop')->model('menus')->getList(
            'menu_id,app_id,menu_title,menu_path,workground',
            array('menu_type'=>'workground','disabled'=>'false')
        );
        $user = kernel::single('desktop_user');  
        if($user->is_super()){
            $aData = $act;
        }
        else{
            $group = $user->group();//print_r($group);
            $meuns = app::get('desktop')->model('menus');
            $data = array();
            foreach($group as $key=>$val){
            $aTmp = $meuns->workgroup($val);
               foreach($aTmp as $val ){
               $data[] =$val;
          }
      }
            $aData = $data;
        }
        $menu_id = array();
        $wrokground = array(); 
        foreach((array)$aData as $value){
            if(!in_array($value['menu_id'],(array)$menu_id)){
                $workground[] = $value;
            }
            $menu_id[] = $value['menu_id'];
        }
        $render->pagedata['actions'] = $workground;
        $render->pagedata['side'] = "sidepanel";
        return $render->fetch('sidepanel.html');
    }
}
