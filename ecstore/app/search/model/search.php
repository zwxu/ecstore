<?php

class search_mdl_search extends base_mdl_app_content
{

	function table_name($real=false){
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_app_content';
        }else{
            return 'app_content';
        }

    }
    function _columns(){
        $schema = new base_application_dbtable;
        $dbinfo = $schema->detect($this->app,$this->table_name())->load();
        $dbinfo['columns']['content_type']['label'] = '类型';
        $dbinfo['columns']['app_id']['label'] = '搜索方式';
        return $dbinfo['columns'];
    }

    function get_schema(){
        $table = $this->table_name();
        if(!isset($this->__exists_schema[$this->app->app_id][$table])){
            $this->table_define = new base_application_dbtable;
            $this->app = app::get('base');
            $this->__exists_schema[$this->app->app_id][$table] = $this->table_define->detect($this->app,$table)->load();
        }
        $this->__exists_schema[$this->app->app_id][$table]['columns']['content_type']['label'] = '类型';
        $this->__exists_schema[$this->app->app_id][$table]['columns']['app_id']['label'] = '搜索方式';
        return $this->__exists_schema[$this->app->app_id][$table];
    }

    function filter($filter){
    	$filter['content_name'] = 'search_server.search_goods';
    	$filter['content_type'] = 'service';
        return parent::filter($filter);
    }


}//End Class
