<?php

 
class desktop_widgets_appolymer implements desktop_interface_widget{
    
    
    function __construct($app){
        $this->app = $app; 
        $this->render =  new base_render(app::get('desktop'));  
    }
    
    function get_title(){
            
        return app::get('desktop')->_("应用程序");
        
    }
    function get_html(){ 
        $render = $this->render;
        $render->pagedata['data'] = '';
        return $render->fetch('widgets/appolymer.html');
    }
    function get_className(){
        
          return "";
    }
    function get_width(){
          
          return "normal";
        
    }
    
}

?>