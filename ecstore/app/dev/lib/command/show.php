<?php

 
class dev_command_show extends base_shell_prototype{
    
    var $command_services = '显示注册的Service';
    function command_services($filter=null){
        $db = kernel::database();
        $sql = 'select content_name,content_path from sdb_base_app_content where content_type="service" and disabled!="true"';
        if($filter){
            $sql.=' and content_name like '.$db->quote($filter);
        }
        $data = array();
	$services = array();
        foreach($db->select($sql) as $row){
	    $services[$row['content_name']][] = $row['content_path'];
        }
	foreach ($services as $service_box_name => $service_box_services) {
	    foreach ($service_box_services as $service_name) {
		$data[] = array($service_box_name, $service_name);
	    }
	}
        $this->output_table($data);
    }

    var $command_viewtags = '显示所有可用模板标签';
    function command_viewtags($tagname=null){
        
        $view_helper_types = array(
				   'compile'=>array('view_compile_helper', array('compile')),
				   'runtime'=>array('view_helper', array('modifier','function','block'))
				   );
            
        foreach($view_helper_types as $part=>$define){
            foreach(kernel::servicelist($define[0]) as $class=>$obj){
                foreach(get_class_methods($obj) as $func){
                    $p = strpos($func,'_');
                    $type = substr($func,0,$p);
                    if(in_array($type,$define[1])){
                        $name = substr($func,$p+1);
                        if(!$tagname || $name==$tagname){
                            if(!isset($list[$type.'_'.$name])){
                                $list[$type.'_'.$name] = array($name,$type,$func);
                                $orderarr[] = $name;
                            }    
                        }
                    }
                }
            }
        }
        array_multisort($orderarr,$list);
        $this->output_table($list);
    }
    
    var $command_classfile = '显示类的文件地址';
    function command_classfile($class=null){
        $class = trim($class?$class:file_get_contents("php://stdin", "r"));
        $reflector = new ReflectionClass($class);
        echo $reflector->getFileName(),"\n";
    }
    
    var $command_depends = '生成已安装的app依赖关系图, Graphviz格式';
    public function command_depends(){
	$output = "//Usage: cmd dev:tools dep2dot | dot -Tjpg -odepends.jpg\n\n";
	$output .= "digraph depends{\n";
	$rows = kernel::database()->select('select app_id from sdb_base_apps where status != "uninstalled"');
	$depends_apps_map = array();
	foreach($rows as $row){
	    $depends_apps = app::get($row['app_id'])->define('depends/app');
	    if($depends_apps){
		foreach($depends_apps as $dep_app){
		    $output.= "\t".$row['app_id'].'->'.$dep_app['value'].";\n";
		}
	    }
	}
	$output.="}\n";
	echo $output;
    }

}
