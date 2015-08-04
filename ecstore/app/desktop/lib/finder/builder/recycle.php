<?php

 
class desktop_finder_builder_recycle extends desktop_finder_builder_prototype{

    function main(){
        $render = app::get('desktop')->render();
       $oRecycle = app::get('desktop')->model('recycle');
        $recycle_item = array();
        $recycle_item['drop_time'] = time();
        $recycle_item['item_type'] = $this->object->table_name();
        $o = $this->app->model($this->object->table_name());
        $this->dbschema = $this->object->get_schema();
        $textColumn = $this->dbschema['textColumn'];
        foreach($this->dbschema['columns'] as $k=>$col){
            if($col['is_title']&&$col['sdfpath']){
                $textColumn = $col['sdfpath'];
                break;
            }
        }



        $filter = $_POST;
        unset($filter['_finder']);
        $count_method = $this->object_method['count'];
        $render->pagedata['count'] = $this->object-> $count_method($filter);
        $render->pagedata['tags'] = (array)$tags;
        $render->pagedata['selected_item'] = implode('|',$_POST[$this->dbschema['idColumn']]);
        $render->pagedata['object_name'] = $this->object_name;
        $render->pagedata['used_tag'] = array_keys((array)$used_tag);
        $render->pagedata['intersect'] = (array)$intersect;
        $render->pagedata['url'] = $this->url;
        echo $render->fetch('common/dialog_recycle.html');

    }

}
