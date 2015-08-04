<?php

 

class b2c_mdl_goods_type_props extends dbeav_model{
    var $has_many = array(
        'props_value' => 'goods_type_props_value:contrast'
    );

    /*
    function save( &$data,$mustUpdate=null ){
        $oPValue = &$this->app->model('goods_type_props_value');
        foreach( $data['options'] as $k => $v ){
            $data['props_value'][$k] = array(
             //   'props_id' => $data['props_id'],
                'name' => $v,
                'alias' => $data['optionAlias'][$k],
            );
            if( $data['props_id'] ){
                $pvid = $oPValue->dump( array( 'props_id'=> $data['props_id'] ,'name'=>$v),'props_value_id' );
                if( $pvid['props_value_id'] )
                    $data['props_value'][$k]['props_value_id'] = $pvid['props_value_id'];
            }
        }
        return parent::save($data,$mustUpdate);
    }
     */

}
