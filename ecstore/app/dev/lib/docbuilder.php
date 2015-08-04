<?php
class dev_docbuilder{
    
    function export($app_id,$path){
	    
	    if(!file_exists(ROOT_DIR."/app/{$app_id}/app.xml")){
	        return;
	    }
	    
	    utils::mkdir_p("$path/refs");
	    
	    $this->update_index($app_id,$path);
	    $this->update_appinfo($app_id,$path);
	    $this->update_servicelib($app_id,$path);
	    $this->update_dbschema($app_id,$path);
    }
    
    function update_servicelib($app_id,$path){
        $out = "{$app_id}提供的service\n\n\n";
        file_put_contents("$path/refs/service-export.t2t",$out);
    }
    
    function update_appinfo($app_id,$path){
        $define = kernel::single('base_xml')->xml2array(
            file_get_contents(ROOT_DIR."/app/{$app_id}/app.xml"),'base_app');
        $file = "{$app_id} app info\n\n\n";
        $file .= "{$define['description']}\n\n";
        
        if($define['depends']){
            $deps = array();
            foreach((array)$define['depends']['app'] as $app){
                $deps[] = "[{$app['value']} ecos/app/{$app['value']}/docs/index.t2t]";
            }
            $file .= "**本应用依赖:** ".implode(',',$deps)."\n";
        }else{
            $file .= "**本应用无依赖**\n";
        }
        file_put_contents("$path/appinfo.t2t",$file);
    }
    
    function update_dbschema($app_id,$path){
        if(!is_dir(ROOT_DIR."/app/{$app_id}/dbschema")){
            return;
        }
        
        $out = "{$app_id}的数据表\n\n\n";
        $db_column_title = array('colname','label','type','null','extra','pkey');
        foreach(kernel::single('base_application_dbtable')->detect($app_id) as $name=>$item){
            
            $table_name = $item->real_table_name();
            $out.= '='.$table_name."=\n";
            $define = $item->load();
            $columns = array();
            foreach($define['columns'] as $cname=>$row){
                $columns[] = array($cname
                                    ,$row['label']
                                    ,$row['realtype']
                                    ,$row['required']?'Y':'N'
                                    ,$row['extra']
                                    ,$row['pkey']?'Y':'N'
                                );
            }
            $out.= $this->gen_table($db_column_title,$columns);
        }
        file_put_contents("$path/refs/dbschema.t2t",$out);
    }
    
    function update_index($app_id,$path){
	    
	    if(file_exists("$path/index.t2t")){
                return;
	    }
	    
        $template=<<<EOF
{$app_id}


%!include appinfo.t2t

%!link  pages/*.t2t

=资料=
%!link  refs/*.t2t

EOF;
        file_put_contents("$path/index.t2t",$template);
    }
    
    function gen_table($titles,$rows){
        $out  = '';
        $out .= '|| '.implode(' | ',$titles)." ||\n";
        foreach($rows as $row){
            $out .=  '| '.implode(' | ',$row)." |\n";
        }
        return $out;
    }
   
}