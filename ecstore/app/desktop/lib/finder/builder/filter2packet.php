<?php

 
class desktop_finder_builder_filter2packet extends desktop_finder_builder_prototype{
    function main(){
        $render = app::get('desktop')->render();
        $render->pagedata['app'] = $_GET['app'];
        $render->pagedata['act'] = $_GET['act'];
        $render->pagedata['ctl'] = $_GET['ctl'];
        $render->pagedata['model'] = $this->object_name;
        
        
        $filterquery = $_POST['filterquery'];
        $tabs = $this->get_views();
        if($tabs&&$_GET['view']){
            $filterquery = $filterquery.'&'.http_build_query($tabs[$_GET['view']]['filter']);
        }
        $render->pagedata['filterquery'] = $filterquery;
        echo $render->fetch('finder/view/filter2packet.html');
    }
}