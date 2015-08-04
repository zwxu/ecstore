<?php
class site_devtpl_widget{
    
    function get_name(){
        return app::get('site')->_('模板挂件');
    }
    
    function get_desc(){
        return app::get('site')->_('BBC模挂件, 可以放置在模板上');
    }
    
    function init($name){
        
    }
    
    function get_templates(){
        return array(
                app::get('site')->_('商品列表')=>array(app::get('site')->_('列表挂件'),app::get('site')->_('列表挂件')),
                'flash'=>array(app::get('site')->_('flash挂件'),app::get('site')->_('列表挂件')),
            );
    }
    
}