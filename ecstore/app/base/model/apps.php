<?php

 
class base_mdl_apps extends base_db_model{

    function filter($filter){
        $addons = array();
        if(isset($filter['installed'])){
            $addons[] = $filter['installed']?'status!="uninstalled"':'status="uninstalled"';
            unset($filter['installed']);
        }
        
        if(isset($filter['normalview'])){ //普通用户浏览模式
            $hidden_apps = true;
            if($service = kernel::service('base_mdl_apps_hidden')){
                if(method_exists($service, 'is_hidden')){
                    $hidden_apps = $service->is_hidden($filter);
                }
            }
            if($hidden_apps === true){
                $depends_apps = array_keys($this->check_deploy_depends());
                $package = $this->fetch_deploy_package();
                $package_apps = array();
                foreach($package AS $package_app){
                    $package_apps[] = $package_app['id'];
                }
                $diff_apps = array_diff($depends_apps, $package_apps);
                if(count($diff_apps)){
                    $addons[] = "`app_id` NOT IN ('" . join("', '", $diff_apps) . "')";
                }//todo: 隐藏信赖app信息
            }//todo：判断是否需要隐藏app
        }
        unset($filter['normalview']);

        $addons = implode(' AND ',$addons);
        if($addons) $addons.=' AND ';
        return $addons.parent::filter($filter);
    }

    public function fetch_deploy_package() 
    {
        $deploy = kernel::single('base_xml')->xml2array(file_get_contents(ROOT_DIR.'/config/deploy.xml'),'base_deploy');
        return (is_array($deploy['package']['app'])) ? $deploy['package']['app'] : array();
    }//End Function

    public function check_deploy_depends() 
    {
        $depends_apps = array();
        $package = $this->fetch_deploy_package();
        foreach($package AS $package_app){
            $this->check_depends_install($package_app['id'], $depends_apps);
        }
        return $depends_apps;
    }//End Function

    public function check_depends_install($app_id, &$queue){
        $depends_app = app::get($app_id)->define('depends/app');
        foreach((array)$depends_app as $depend_app_id){
            $this->check_depends_install($depend_app_id['value'], $queue);
        }
        $queue[$app_id] = app::get($app_id)->define();
    }

}
