<?php
class dev_mdl_project{
    
    function __construct(){
        $this->app = $app;
        $this->columns = array(
            'name'=>array('label'=>app::get('dev')->_('项目名'),'width'=>200),
            'type'=>array('label'=>app::get('dev')->_('类型'),'width'=>200),
            'createtime'=>array('label'=>app::get('dev')->_('创建时间'),'type'=>'time','width'=>200),
            'path'=>array('label'=>app::get('dev')->_('路径'),'width'=>400),
            );

        $this->schema = array(
            'default_in_list'=>array_keys($this->columns),
            'in_list'=>array_keys($this->columns),
            'idColumn'=>'passport_id',
            'columns'=>&$this->columns
            );

        $project_dir = DATA_DIR.'/projects';
        if(is_dir($project_dir)){
            $handle = opendir($project_dir);
            if($handle){
                while(false!==($file=readdir($handle))){
                    if($file{0} != '.') {
                        include($project_dir.'/'.$file);
                        //$project['commands'] = kernel::single($project['type'])->get_command($project);
                        //$project['type_name'] = kernel::single($project['type'])->get_name();
                        $projects[] = $project;
                    }
                }
                closedir($handle);
            }
        }
        $this->projects = $projects;
    }
    
    function get_schema(){
        return $this->schema;
    }

    function getlist(){
        return $this->projects;
    }

    function count(){
        return count($this->projects);
    }

}