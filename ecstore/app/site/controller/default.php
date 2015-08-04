<?php



class site_ctl_default extends site_controller{
    function index(){
        if(defined('APP_SITE_INDEX_MAXAGE') && APP_SITE_INDEX_MAXAGE > 1){
            $this->set_max_age(APP_SITE_INDEX_MAXAGE);
        }//todo: 首页max-age设定

        if(kernel::single('site_theme_base')->theme_exists()){

            $obj = kernel::service('site_index_seo');

            if(is_object($obj) && method_exists($obj, 'title')){
                $title = $obj->title();
            }else{
                $title = (app::get('site')->getConf('site.name')) ? app::get('site')->getConf('site.name') : app::get('site')->getConf('page.default_title');
            }

            if(is_object($obj) && method_exists($obj, 'keywords')){
                $keywords = $obj->keywords();
            }else{
                $keywords = (app::get('site')->getConf('page.default_keywords')) ? app::get('site')->getConf('page.default_keywords') : $title;
            }

            if(is_object($obj) && method_exists($obj, 'description')){
                $description = $obj->description();
            }else{
                $description = (app::get('site')->getConf('page.default_description')) ? app::get('site')->getConf('page.default_description') : $title;
            }

            $this->pagedata['headers'][] = '<title>' . htmlspecialchars($title) . '</title>';
            $this->pagedata['headers'][] = '<meta name="keywords" content="' . htmlspecialchars($keywords). '" />';
            $this->pagedata['headers'][] = '<meta name="description" content="' . htmlspecialchars($description) . '" />';
            $GLOBALS['runtime']['path'][] = array('title'=>app::get('b2c')->_('首页'),'link'=>kernel::base_url(1));
            $this->pagedata['is_index'] = 1;//前台加载js表示  
            $this->set_tmpl('index');
            //本地化服务  
            //echo "<pre>";print_r($_COOKIE['CITY_ID']);exit;
            if(isset($_COOKIE['CITY_ID'])){
                $mdl_city = app::get('site')->model('city');
                $mdl_regions = app::get('ectools')->model('regions');
                $mdl_theme_tmpl = app::get('site')->model('themes_tmpl');
                $tmpl_id = $mdl_city->dump(array('city_id'=>$_COOKIE['CITY_ID']),'tmpl_id');
                $tmpl_path = $mdl_theme_tmpl->dump(array('id'=>$tmpl_id['tmpl_id']),'tmpl_path');
                $this->set_tmpl_file($tmpl_path['tmpl_path']);
            }
            //end
            $this->page('index.html');
        }else{
            
            $this->display('splash/install_template.html');
        }
    }
}
