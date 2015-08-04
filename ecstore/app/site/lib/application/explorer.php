<?php


class site_application_explorer extends base_application_prototype_xml 
{
    var $xml='site.xml';
    var $xsd='site_content';
    var $path = 'explorer';

    public function current(){
        $this->current = $this->iterator()->current();
        return $this;
    }

    final public function install() 
    {
        $data = $this->parse_explorer_params($this->current);
        $this->insert_explorers($data);
    }//End Function

    private function parse_explorer_params($params) 
    {
        $data['app'] = $this->target_app->app_id;
        $data['title'] = $params['value'];
        $data['path'] = $params['path'];
        return $data;
    }//End Function

    private function insert_explorers($data) 
    {
        return app::get('site')->model('explorers')->insert($data);
    }//End Function
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('site')->model('explorers')->delete(array(
            'app'=>$app_id));
    }

}//End Class
