<?php
class dev_project{
    
    function getlist(){
        $project_dir = DATA_DIR.'/projects';
        if(is_dir($project_dir)){
            $handle = opendir($project_dir);
            if($handle){
                while(false!==($file=readdir($handle))){
                    if($file{0} != '.') {
                        include($project_dir.'/'.$file);
                        $project['commands'] = kernel::single($project['type'])->get_command($project);
                        $project['type_name'] = kernel::single($project['type'])->get_name();
                        $projects[] = $project;
                    }
                }
                closedir($handle);
            }
        }
        return $projects;
    }
    
    function get_template(){
        
    }
    
    function save($project){
        if(!is_dir(DATA_DIR.'/projects')){
            utils::mkdir_p(DATA_DIR.'/projects');
        }
        file_put_contents(DATA_DIR.'/projects/'.$project['createtime'].'.php'
            , '<?php $project = '.var_export($project,1).';');
    }
    
}