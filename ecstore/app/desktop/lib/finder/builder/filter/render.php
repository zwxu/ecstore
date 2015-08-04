<?php

 
class desktop_finder_builder_filter_render
{
    function __contruct($finder_aliasname){
        $this->finder_aliasname = $finder_aliasname;
    }
    function main($object_name,$app,$filter=null,$controller=null,$cusrender=null){
        if(strpos($_GET['object'],'@')!==false){
            $tmp = explode('@',$object_name);
            $app = app::get($tmp[1]);
            $object_name = $tmp[0];
        }
        $object = $app->model($object_name);
        $ui = new base_component_ui($this->controller,$app);
        require(APP_DIR.'/base/datatypes.php');
        $this->dbschema = $object->get_schema();
        $finder_id = $_GET['_finder']['finder_id'];
        foreach(kernel::servicelist('extend_filter_'.get_class($object)) as $extend_filter){
            $colums = $extend_filter->get_extend_colums($this->finder_aliasname);
            if($colums[$object_name]){
                $this->dbschema['columns'] = array_merge((array)$this->dbschema['columns'],(array)$colums[$object_name]['columns']);
            }
        }

        foreach($this->dbschema['columns'] as $c=>$v){
            if(!$v['filtertype']) continue;
            
            if( isset($filter[$c]) ) {
                continue;
            }
            
            if(!is_array($v['type']))
                if(strpos($v['type'],'decimal')!==false&&$v['filtertype']=='number'){
                    $v['type'] = 'number';
                }
            $columns[$c] = $v;
            if(!is_array($v['type']) && $v['type']!='bool' && isset($datatypes[$v['type']]) && isset($datatypes[$v['type']]['searchparams'])){
                $addon='<select search="1" name="_'.$c.'_search" class="x-input-select  inputstyle">';
                foreach($datatypes[$v['type']]['searchparams'] as $n=>$t){
                    $addon.="<option value='{$n}'>{$t}</option>";
                }
                $addon.='</select>';
            }else{
                if($v['type']!='bool')
                    $addon = app::get('desktop')->_('是');
                else $addon = '';
            }
            $columns[$c]['addon'] = $addon;
            if($v['type']=='last_modify'){
                $v['type'] = 'time';
            }
             $params = array(
                    'type'=>$v['type'],
                    'name'=>$c,
                );
            if($v['type']=='bool'&&$v['default']){
                $params = array_merge(array('value'=>$v['default']),$params);
            }
            if($this->name_prefix){
                $params['name'] = $this->name_prefix.'['.$params['name'].']';  
            }
            if($v['type']=='region'){
                $params['app'] = 'ectools';
            }
            if($v['default_value']) $params['value'] = $v['default_value'];
            
            
            $inputer = $ui->input($params);
            $columns[$c]['inputer'] = $inputer;
        }
        
        if($cusrender){
          return array('filter_cols'=>$columns,'filter_datatypes'=>$datatypes);
        }
        $render = new base_render(app::get('desktop'));
        
        if($object->has_tag){
            $render->pagedata['app_id'] = $app->app_id;
            $render->pagedata['tag_type'] = $object_name;
            $tag_inputer = $render->fetch('finder/tag_inputer.html');
            $columns['tag'] = array('filtertype'=>true,'filterdefault'=>true,'label'=>app::get('desktop')->_('标签'),'inputer'=>$tag_inputer);
        }
        $render->pagedata['columns'] = $columns;
        $render->pagedata['datatypes'] = $datatypes;
        $render->pagedata['finder_id'] = $finder_id;
        $render->display('finder/finder_filter.html');
    }
}

