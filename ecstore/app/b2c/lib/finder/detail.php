<?php

 

class b2c_finder_detail {
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function detail_columns_modifier(&$detail_pages){
        if($this->app->getConf('site.get_policy.method') ==1 && $detail_pages['detail_point']) unset($detail_pages['detail_point']);
        $objuser = kernel::single('desktop_user');
        if($objuser->is_super()) return ;
        $perss = $objuser->group();
        $get = $_GET;
        $menu_path = 'app='.$_GET['app'].'&'.'ctl='.$_GET['ctl'].'&'.'act='.$_GET['act'];
        $menus = app::get('desktop')->model('menus');
        $filter = array('menu_type' => 'menu','menu_path' => $menu_path);
        $row = $menus->getList('*',$filter);
        $detail_action  = array_keys($detail_pages);
        foreach($row as $key => $v)
        {
            if($v['addon'])
            {
                $addon = unserialize($v['addon']);
                if($addon['url_params'] && $addon['url_params']['action'] == 'detail')
                {
                    if(!in_array($v['permission'],(array)$perss))
                    {
                        if($detail_pages[$addon['url_params']['finderview']])
                            unset($detail_pages[$addon['url_params']['finderview']]);
                    }
                }
            }
            continue;
        }
        
    }
}