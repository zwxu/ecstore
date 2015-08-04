<?php

 
class desktop_finder_builder_import extends desktop_finder_builder_prototype{

    function main(){
        $render = &app::get('desktop')->render();
        /*
        $importType = array();
        foreach( kernel::servicelist('desktop_io') as $aio ){
            $importType[] = $aio->io_type_name;
        }
        $render->pagedata['importType'] = $importType;
         */
        if( !$render->pagedata['thisUrl'] )
            $render->pagedata['thisUrl'] = $this->url;
        echo $render->fetch('common/import.html');
    }
}
