<?php
class site_devtpl_theme{
    
    function get_name(){
        return app::get('site')->_('模板');
    }
    
    function get_desc(){
        return app::get('site')->_('BBC模板, 上面可以放置挂件');
    }
    
    function init($name){
        
    }
    
    function get_templates(){
        return array(
                app::get('site')->_('商品列表')=>array(app::get('site')->_('列表挂件'),app::get('site')->_('列表挂件')),
                app::get('site')->_('flash')=>array(app::get('site')->_('flash挂件'),''),
            );
    }
    
}