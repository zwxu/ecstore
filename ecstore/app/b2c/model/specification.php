<?php



class b2c_mdl_specification extends dbeav_model{
    var $has_many = array(
        'spec_value' => 'spec_values:contrast'
    );

    function getSpecIdByAll($spec){
        $sql = 'SELECT s.spec_id from sdb_b2c_specification s '
            .'left join sdb_b2c_spec_values v on s.spec_id = v.spec_id '
            .'where s.spec_name = '.$this->db->quote($spec['spec_name']).' and v.spec_value in ("'.implode('","',$spec['option']).'") '
            .' group by v.spec_id having count(*) = '.count($spec['option']);
        return $this->db->select($sql);
    }

    function getSpecValuesByAll($spec){
        $rs = array();
        $oSpecValue = &$this->app->model('spec_values');

        foreach( $spec['option'] as $specValue ){
            $specv = $oSpecValue->dump(array('spec_value'=>$specValue,'spec_id'=>$spec['spec_id']),'spec_value_id,spec_image');
            $rs[$specValue]['spec_value_id'] = $specv['spec_value_id'];
            $rs[$specValue]['spec_value'] = $specValue;
            $rs[$specValue]['private_spec_value_id'] = time().(++$this->countnum);
            $rs[$specValue]['spec_image'] = $specv['spec_image'];
            $rs[$specValue]['spec_goods_images'] = '';
        }
        return $rs;
    }

    function pre_recycle($rows){
        foreach($rows as $v){
            $spec_ids[] = $v['spec_id'];
        }
        $o = &$this->app->model('goods_spec_index');
        $rows = $o->getList('*',array('spec_id'=>$spec_ids));
        if( $rows[0] ){
            $this->recycle_msg = app::get('b2c')->_('规格已被商品使用');
            return false;
        }
        return true;
    }

    function save(&$data,$mustUpdate = null){
        if( $data['spec_value'] ){
            $i = 1;
            foreach( $data['spec_value'] as $k => $v ){
                $data['spec_value'][$k]['p_order'] = $i++;
            }
        }
        return parent::save($data,$mustUpdate);
    }

    function dump($filter,$field = '*',$subSdf = null){
        $rs = parent::dump($filter,$field,$subSdf);
        if( $rs['spec_value'] ){
            $tSpecValue = current( $rs['spec_value'] );
            if( $tSpecValue['p_order'] && $tSpecValue['spec_value_id'] ){
                $specValue = array();
                foreach( $rs['spec_value'] as $k => $v ){
                    $specValue[$v['p_order']] = $v;
                }
                ksort($specValue);
                $rs['spec_value'] = array();
                foreach( $specValue as $vk => $vv ){
                    $rs['spec_value'][$vv['spec_value_id']] = $vv;
                }
            }
        }
        return $rs;
    }

    function delete($filter){
        $o = &$this->app->model('goods_spec_index');
        if( $o->dump($filter) ){
            $this->recycle_msg = app::get('b2c')->_('规格已被商品使用');
            return false;
        }
        $o = &$this->app->model('spec_values');
        $o->delete($filter);
        return parent::delete($filter);;
    }
    
    function createCustomSpec($spec){
        $spec_all = array();
        foreach($spec as $sp){
            $spec_all['spec_id'][] = $sp['spec_id'];
            foreach($sp['option'] as $option){
                $spec_all['spec_value_id'][] = $option['spec_value_id'];
                $spec_all['option'][$option['spec_value_id']]['spec_value_id'] = $option['spec_value_id'];
                $spec_all['option'][$option['spec_value_id']]['spec_value'] = $option['spec_value'];
                $spec_all['option'][$option['spec_value_id']]['spec_image'] = $option['spec_image'];
                $spec_all['option'][$option['spec_value_id']]['spec_id'] = $sp['spec_id'];
                $spec_all['option'][$option['spec_value_id']]['p_order'] = 50;
            }
        }
        $sql = 'SELECT spec_value_id from sdb_b2c_spec_values where spec_id in ("'.implode('","',$spec_all['spec_id']).'") ';
        $spev_values_all = $this->db->select($sql,true);
        $exsist_spec_value = array();
        foreach($spev_values_all as $sv){
            $exsist_spec_value[] = $sv['spec_value_id'];
        }
        $create_spec_value = array_diff($spec_all['spec_value_id'],$exsist_spec_value);
        if(!empty($create_spec_value)){
            $oSpecValue = &$this->app->model('spec_values');
            foreach($create_spec_value as $spec_value_id){
                $oSpecValue->save($spec_all['option'][$spec_value_id]);
            }
        }
    }
}
