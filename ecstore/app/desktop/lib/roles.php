<?php


class desktop_roles{

    function __construct($app)
    {
        $this->app=$app;
        $this->model = $app->model('roles');
    }

     //根据工作组获得所有下面的permission
    function get_permission_per($menu_id,$wg)
    {
        $menus = $this->app->model('menus');
        $sdf = $menus->dump($menu_id);
        $workground = $sdf['workground'];
        //获取权限类型为menu和permission的列表
        #$aMenus = $menus->getList('*',array('menu_type' => 'menu','workground' => $workground));
        $aMenus = $menus->getList('*',array('menu_type' => array('menu','permission'),'workground' => $workground));
        $aTmp = array();
        $menu_group = array();
        foreach($aMenus as $val )
        {
            $aTmp['menu_group'][] = $val['menu_group'];
            $aTmp['permission'][] = $val['permission'];
        }
        $aMenus = array_unique($aTmp['permission']); //所有的permissions
        $permissions = array();
        foreach($aMenus as $val)
        {
            $sdf = $menus->dump(array('menu_type' => 'permission','display'=>'true','permission' => $val)); //permission 加显示判断  by shuhanbing
            if(in_array($sdf['permission'],$wg)){
                $sdf['checked'] = 1;
            }
            else{
                $sdf['checked'] = 0;
            }
            $permissions[] = $sdf;
        }
        return $permissions;
    }

    //获取控制面板的permissions
    function get_adminpanel($role_id,$wg,&$flg=0)
    {
        $menus = $this->app->model('menus');
        $aPer = $menus->getList('*',array('menu_type' => 'permission','disabled' => 'false','display'=>'true')); //permission 加显示判断  by shuhanbing
        $adminpanel_per = array();
        foreach((array)$aPer as $val)
        {
            $aData = $menus->dump(array('menu_type' => 'menu','permission' => $val['permission']));
            $__aData = $menus->dump(array('menu_type' => 'adminpanel','permission' => $val['permission']));
            if(!$aData && $__aData){
                if(in_array($val['permission'],(array)$wg)){
                    $val['checked'] = 1;
                    $flg = 1;
                }
                else{
                    $val['checked'] = 0;
                }
                $adminpanel_per[] = $val;
            }
        }
        return $adminpanel_per;
    }

    ////获取其他的permissions
    function get_others($wg,&$othersflg=0)
    {
        $menu = app::get('desktop')->model('menus');
        $aData = array();
        $arr_per = $menu->getList('*',array('menu_type'=>'permission','disabled'=>'false','display'=>'true'));

        foreach((array)$arr_per as $key => $val)
        {
            $arr_menu = $menu->getList('menu_id',array('menu_type' => 'menu','permission' => $val['permission']));
            $__arr_menu = $menu->getList('menu_id',array('menu_type' => 'adminpanel','permission' => $val['permission']));
            if($arr_menu || $__arr_menu)
            {
                continue;
            }
            else
            {
                if(in_array($val['permission'],(array)$wg))
                {
                    $val['checked'] = 1;
                    $othersflg = 1;
                }
                else
                {
                    $val['checked'] = 0;
                }
                $aData[] = $val;
            }
        }
        return $aData;
    }
}
