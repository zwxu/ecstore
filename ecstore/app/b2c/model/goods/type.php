<?php



class b2c_mdl_goods_type extends dbeav_model{
    var $has_many = array(
        'brand' => 'type_brand:replace',
        'spec' => 'goods_type_spec:replace',
        'props' => 'goods_type_props:contrast'
    );

    var $subSdf = array(
        'default' => array(
            'brand' => array('*'),
            'spec' => array('*'),
            'props'=>array('*',array('props_value'=>array('*',null, array( 0,-1,'order_by ASC' ))),array( 0,-1,'ordernum ASC' ) )
        )
    );
    function checkDefined(){
        return $this->count(array('is_def'=>'false'));
    }

    function getDefault(){
        return $this->getList('*',array('is_def'=>'true'));
    }

    function getSpec($id,$fm=0){
		if (!$id) return array();
		
        $sql="select spec_id,spec_style from sdb_b2c_goods_type_spec where type_id=".intval($id);
        $row = $this->db->select($sql);

        if ($row){
            foreach($row as $key => $val){
                if ($fm){
                    if($val['spec_style']<>'disabled'){
                        $attachment=array(
                            "spec_style"=>$val['spec_style']
                        );
                        $tmpRow[$val['spec_id']]=$this->getSpecName($val['spec_id'],$attachment);
                    }
                }
                else{
                    $attachment=array(
                        "spec_style"=>$val['spec_style']
                    );
                    $tmpRow[$val['spec_id']]=$this->getSpecName($val['spec_id'],$attachment);
                }
            }

            return $tmpRow;
        }
        else
            return array();
    }
    function getSpecName($spec_id,$args){
        $sql="select spec_name,spec_type from sdb_b2c_specification where spec_id=".intval($spec_id);
        $snRow=$this->db->selectrow($sql);
        $tmpRow['name']=$snRow['spec_name'];
        $tmpRow['spec_type'] = $snRow['spec_type'];
        $tmpRow['spec_memo'] = $snRow['spec_memo'];
        if (is_array($args)){
            foreach($args as $k => $v){
                $tmpRow[$k] = $v;
            }
        }
        $row=$this->getSpecValue($spec_id);
        $tmpRow['spec_value']=$row;
        $tmpRow['type'] = 'spec';
        return $tmpRow;
    }

    function getSpecValue($spec_id){
        $sql="select spec_value,spec_value_id,spec_image from sdb_b2c_spec_values where spec_id=".intval($spec_id)." order by p_order,spec_value_id";
        $svRow=$this->db->select($sql);
        if ($svRow){
            foreach($svRow as $key => $val){
                $tmpRow[$val['spec_value_id']]=array(
                        "spec_value"=>$val['spec_value'],
                        "spec_image"=>$val['spec_image']
                );
            }
        }
        return $tmpRow;
    }

    function save( &$data,$mustUpdate =null ){
        if ($data['props'])
        {
            foreach( $data['props'] as $k => $v ){
                $v['goods_p'] = $k;
                if( $v['options'] ){
                    $i = 0;
                    foreach( $v['options'] as $vk => $vv ){
                        if( $v['optionIds'][$vk] )
                            $data['props'][$k]['props_value'][$vk]['props_value_id'] = $v['optionIds'][$vk];
                        $data['props'][$k]['props_value'][$vk]['name'] = $vv;
                        $data['props'][$k]['props_value'][$vk]['alias'] = $v['optionAlias'][$vk];
                        $data['props'][$k]['props_value'][$vk]['order_by'] = $i++;
                    }
                }
                unset( $data['props'][$k]['options'] );
            }
        }

        return parent::save($data,$mustUpdate);
    }

    function dump($filter,$field = '*',$subSdf = null){
        if( (strpos( $field,'*' ) !== false || strpos( $field,'props' ) !== false ) && !$subSdf['props'] ){
            $subSdf = array_merge( (array)$subSdf,array('props'=>array('*',array('props_value'=>array('*',null, array( 0,-1,'order_by ASC' ))),array( 0,-1,'ordernum ASC' ) )) );
        }
        $rs = parent::dump($filter,$field,$subSdf);
        $props = array();
        if( $rs['props'] ){
            foreach( $rs['props'] as $k => $v ){
                $props[$v['goods_p']] = $v;
                if( $v['props_value'] )
                    foreach( $v['props_value'] as $vk => $vv ){
                        $props[$v['goods_p']]['options'][$vv['props_value_id']] = $vv['name'];
                        $props[$v['goods_p']]['optionAlias'][$vv['props_value_id']] = $vv['alias'];
                        $props[$v['goods_p']]['optionIds'][$vv['props_value_id']] = $vv['props_value_id'];
                    }
                unset( $props[$v['goods_p']]['props_value'] );
            }
            unset( $rs['props'] );
            $rs['props'] = $props;
        }
        return $rs;
    }
    function pre_recycle($rows){
        foreach($rows as $v){
            $type_ids[] = $v['type_id'];
        }
		
		$o_type_brand = &$this->app->model('goods_cat');
		$rows = $o_type_brand->getList('cat_id',array('type_id'=>$type_ids));
		if( $rows ){
            $this->recycle_msg = app::get('b2c')->_('该类型已被分类关联');
            return false;
        }
		
        $o = &$this->app->model('goods');
        $rows = $o->getList('*',array('type_id'=>$type_ids),0,1);
        if( $rows ){
            $this->recycle_msg = app::get('b2c')->_('类型已被商品使用');
            return false;
        }
        return true;
    }

    function pre_restore(&$data,$restore_type='add'){
         if(!($this->is_exists($data['name']))){
             $data['need_delete'] = true;
             return true;
         }
         else{
             if($restore_type == 'add'){
                    $new_name = $data['name'].'_1';
                    while($this->is_exists($new_name)){
                        $new_name = $new_name.'_1';
                    }
                    $data['name'] = $new_name;
                    $data['need_delete'] = true;
                 return true;
             }
             if($restore_type == 'none'){
                 $data['need_delete'] = false;
                 return false;
             }
         }
    }

    function is_exists($name){
        $row = $this->getList('*',array('name' => $name));
        if(!$row) return false;
        else return true;
    }

    function fetchSave($data,&$msg=''){
        if ($data['props']){
            foreach($data['props'] as $key => $val){
                $data['props'][$key]['show']    = 'on';
                $data['props'][$key]['goods_p'] = $key + 1;
                if( $val['options'] ){
                	$i = 0;
                	foreach( $val['options'] as $valKey => $valVal ){
                		if( $val['optionIds'][$valKey] )
                		$data['props'][$key]['props_value'][$valKey]['props_value_id'] = $val['optionIds'][$valKey];
                		$data['props'][$key]['props_value'][$valKey]['name'] = $valVal;
                		$data['props'][$key]['props_value'][$valKey]['alias'] = $val['optionAlias'][$valKey];
                		$data['props'][$key]['props_value'][$valKey]['order_by'] = $i++;
                	}
                }
                unset( $data['props'][$key]['options'] );
            }
        }
        $data['params'] = $this->params_modifier($data['params'],false);
        if($this->db->selectrow('select * from sdb_b2c_goods_type where name=\''.$this->db->quote($data['name']).'\'')){
			$msg = app::get('b2c')->_('对不起，本类型名已存在，请重新输入。');
			return false;
        }

        if($data['spec'] == '') {
            $data['spec'] = array();
        }

        if(parent::save($data)){
            $type_id = $data['type_id'];			
			
            $i = 0;
			$obj_brand = $this->app->model('brand');
			$obj_type_brand = $this->app->model('type_brand');
			$type_brand = array();
			$type_brand['type_id'] = $type_id;
			if (is_array($_POST['importbrands'])){
				foreach($_POST['importbrands'] as $key=>$v){
					$brand = array();
					$brand['brand_name'] = $data['brands'][$v]['brand_name'];
					$brand['brand_keywords'] = $data['brands'][$v]['brand_keywords'];
					$tmp = $obj_brand->getList('brand_id',array('brand_name'=>$brand['brand_name']));
					if (!$tmp)
					$obj_brand->insert($brand);
					else
					$brand['brand_id'] = $tmp[0]['brand_id'];
				
					$brand_id = $brand['brand_id'];
					$type_brand['brand_order'] = $i;
					$type_brand['brand_id'] = $brand_id;
					$obj_type_brand->insert($type_brand);
				}
			}
			
            $aSpec = $this->app->model('specification');
			$obj_type_spec = $this->app->model('goods_type_spec');
            foreach($data['spec'] as $spec_id => $v){
                if($spec_id){
                    $id = $aSpec->getList('spec_id',array('spec_name'=>$v['name']));
                    $type_spec['spec_id'] = $id[0]['spec_id'];
                    $type_spec['type_id'] = $type_id;
                    $type_spec['spec_style'] = $v['spec_style'];
                    $obj_type_spec->insert($type_spec);
                }
            }

            $this->checkDefined();
            return true;
        }else{
            return false;
        }
    }

    function params_modifier($data,$forxml = true){
        $return = array();
        if(is_array($data)){
            if($forxml){
                $i = 0;
                foreach($data as $group=>$cont){
                    $return[$i] = array('groupname'=>$group);
                    if(is_array($cont)){
                        foreach($cont as $k=>$v){
                            $item['itemname'] = $k;
                            $item['itemalias'] = explode('|',$v);
                            $return[$i]['groupitems'][] = $item;
                        }
                    }
                    $i++;
                }
            }else{
                foreach($data as $k=>$group){
                    $return[$group['groupname']] = array();
                    if($group['groupitems']&&is_array($group['groupitems'])){
                        foreach($group['groupitems'] as $k1=>$v1){
                            $return[$group['groupname']][$v1['itemname']] = implode('|',$v1['itemalias']);
                        }
                    }
                }
            }
        }
        return $return;
   }

   function getPropsValue($type_id,$limit=3){
        $sql = "SELECT
                    p.props_id,
                    p.name,
                    p.goods_p,
                    v.props_value_id AS v_id,
                    v.name AS v_name
                FROM
                    sdb_b2c_goods_type_props AS p
                INNER JOIN sdb_b2c_goods_type_props_value AS v on p.props_id = v.props_id
                WHERE
                    p.type_id = ".intval($type_id)." 
                 AND p.props_id in (select a.props_id FROM (select props_id from sdb_b2c_goods_type_props WHERE type_id = ".intval($type_id)." limit 0,".intval($limit).") as a) ORDER BY p.ordernum";
         return $this->db->select($sql);
   }

}
