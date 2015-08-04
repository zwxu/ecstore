<?php

 
class dev_desktop_docs implements desktop_interface_widget{
    
    var $order = 1;
    function __construct($app){
        $this->app = $app; 
        $this->render =  new base_render(app::get('desktop'));  
    }
    
    function get_title(){
        return app::get('dev')->_("开发文档");    
    }
    
    function get_html(){ 
        $html = '';
        $rows = app::get('base')->model('apps')->getlist('*',array('installed'=>true));
        $doclib = array();
        $baseurl = kernel::base_url();
        foreach($rows as $k=>$v){
            $docs = app::get($v['app_id'])->docs();
            
            if($docs){
                $html.="<div class=\"clearfix dashbd-row\"><h4>{$v['app_name']}</h4>";
                $html.='<table cellpadding="0" cellspacing="0"><tbody><tr>';
                foreach($docs as $docname=>$doctitle){
                    $html .= "<td class=\" figure-zero\"><a target=\"_blank\" href=\"$baseurl/index.php/app-doc/{$v['app_id']}/{$docname}\">{$doctitle}</a></td>";
                }
                $html.='</tr></tbody></table></div>';
            }
        }
        return $html;
    }
    
    function get_className(){
          return "";
    }
    
    function get_width(){
          return "l-1";
    }
    
}

?>