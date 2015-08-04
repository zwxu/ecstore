<?php

 
class b2c_application_apiv extends base_application_prototype_xml 
{
    var $xml='apiv_mapper.xml';
    var $xsd='b2c_apiv_mapper_content';
    var $path = 'api';

    public function current(){
        $this->current = $this->iterator()->current();
        return $this;
    }

    final public function install() 
    {
        kernel::log('Installing '.$this->content_typename().' '.$this->key());
        $this->insert_apiv_mapper($this->current);

    }//End Function

    private function insert_apiv_mapper($data) 
    {
        base_kvstore::instance('b2c_apiv')->fetch('apiv.mapper', $apiv_mapper);

        if( !is_array($apiv_mapper) )
            $apiv_mapper = array();

        foreach( $data['target'] as $v )
        {
            $mapper_key = $v['value'] . '_' . $v['version'];

            if( !array_key_exists( $mapper_key, $apiv_mapper ) )
                $apiv_mapper[$mapper_key] = $data['version'];
        }

        base_kvstore::instance('b2c_apiv')->store('apiv.mapper', $apiv_mapper);
    }//End Function
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        if( $app_id == 'b2c' )
            base_kvstore::instance('b2c_apiv')->store('apiv.mapper', '');
    }

}//End Class
