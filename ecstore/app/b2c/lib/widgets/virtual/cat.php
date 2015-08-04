<?php
/**
 * @author chris.zhang
 * 
 */
class b2c_widgets_virtual_cat extends b2c_widgets_public {
    protected $_filter = array(
        'vCatId'    => 'cat_id',    //虚拟分类ID
        'typeId'    => 'type_id',   //商品类型ID
        'parentId'  => 'parent_id', //虚拟分类父类ID
    );
    
    protected $_outData = array(
        'vCatId'    => 'virtual_cat_id',    //虚拟分类ID
        'vCatName'  => 'virtual_cat_name',  //虚拟分类名称
        'vCatLink'  => 'url',               //虚拟分类链接
    );
    
    /**
     * 获取某虚拟分类及其子分类或包含其顶端的所有分类
     * @param mix $vcatId   //虚拟分类ID
     * @param bool $master  //是否获取其顶端信息
     */
    public function getVirtualCatMap($vcatId, $master = false){
        $master = $this->get_bool($master);
        //if (!isset($vcatId) || !$vcatId) return array();
        
        $data = array();
        if ($vcatId && is_array($vcatId)){
            foreach ($vcatId as $vcId) {
                if (!$this->_existCat(array('vCatId'=>$vcId))){
                    continue;
                }
                if ($master){
                    $_tmp = $this->_getVirtualCatMap($vcId,true);
                    $data = $this->array_merge_recursive($data, $_tmp);
                }else {
                    $_tmp = $this->_getVirtualCatMap($vcId,false);
                    $data[$vcId] = $_tmp[$vcId];
                }
            }
        }else{
            if ($vcatId && !$this->_existCat(array('vCatId'=>$vcatId))) return array();
            $data = $this->_getVirtualCatMap($vcatId, $master);
        }
        
        return $data;
    }
    
    /**
     * 根据商品类型获取包含该类型的顶级虚拟分类及其子分类
     * @param int $typeId   //商品类型ID
     */
    public function getVirtualCatMapByType($typeId){
        if (!isset($typeId) || !$typeId) return array();
        $typeId = intval($typeId);
        $data = array();
//        if (is_array($typeId)){
//            foreach ($typeId as $tId){
//                $_rows = $this->_getFirstVCatByType($tId);
//                foreach ((array)$_rows as $vcat){
//                    $_tmp = $this->_getVirtualCatMap($vcat['vCatId']);
//                    $data[$tId][$vcat['vCatId']] = $_tmp[$vcat['vCatId']];
//                }
//            }
//        }else {
        $_rows = $this->_getFirstVCatByType($typeId);
        foreach ((array)$_rows as $vcat){
            $_tmp = $this->_getVirtualCatMap($vcat['vCatId']);
            $data[$typeId][$vcat['vCatId']] = $_tmp[$vcat['vCatId']];
        }
//        }
        
        return $data;
    }
    
    private function _existCat($filter){
        $catId = $this->_getFilter($filter);
        $result = $this->app->model('goods_virtual_cat')->count($filter);
        if ($result > 0) return true;
        return false;
    }
    
    protected function _getFilter($filter){
        $_filter = parent::_getFilter($filter);
        $_filter = array_merge(array('disabled'=>'false'),$_filter);
        
        return $_filter;
    }
    
    
    private function _getFirstVCatByType($typeId, $output = true){
        if (!$this->_existCat(array('typeId'=>$typeId,'parentId'=>0))) return array();
        $typeId = intval($typeId);
        $sql = "SELECT virtual_cat_id, virtual_cat_name FROM sdb_b2c_goods_virtual_cat 
                WHERE disabled = 'false' AND type_id = '$typeId' AND parent_id = 0 ORDER BY virtual_cat_id";
        $_rows = $this->db->select($sql);
        
        if (!$output) return $_rows;
        
        $data = array();
        foreach ($_rows as $cat){
            $data[] = $this->_getOutData($cat);
        }
        
        return $data;
    }

    private function _getVirtualCatMap($vcat_id=null, $master = false, $output=true){
        $master = $this->get_bool($master);
        $output = $this->get_bool($output);
        
        $sql  = $this->_getSql($vcat_id, $master);
        $data = $this->db->select($sql);
        
        $_data  = array();
        $prefix = $this->prefix;
        foreach((array)$data as $row){
            $path = $row['cat_path'];
            $vc_id = $row['virtual_cat_id'];
            $p_id = $row['parent_id'];
            
            if ($output) $row = $this->_getOutData($row);
            $parents = array_filter(explode(',', $path));
            
            if(empty($parents) || count($parents) == 0 || ($vcat_id && $vcat_id == $vc_id)){
                $_data[$vc_id] = $row;
            }elseif(count($parents) == 1){
                $_data[$p_id][$prefix][$vc_id] = $row;
            }else{
                krsort($parents);
                $out = array();
                $i = 0;
                foreach ($parents as $v){
                    $_tmp = array();
                    if ($i === 0){
                        $_tmp[$v][$prefix][$vc_id] = $row;
                    }else{
                        $_tmp[$v][$prefix] = $out;
                    }
                    $out = $_tmp;
                    $i++;
                    if ($vcat_id && $v == $vcat_id) break;
                }
                $_data = $this->array_merge_recursive($_data, $out);
            }
        }
        
        return $_data;
    }
    
    private function _getSql(&$vcat_id = null, $master){
        if ($vcat_id){
            $vcat_id = $this->addslashes($vcat_id);
            if ($master) $vcat_id = $this->_getMasterId($vcat_id);
            $sql = "SELECT virtual_cat_id, parent_id, cat_path, virtual_cat_name, url FROM sdb_b2c_goods_virtual_cat 
                    WHERE disabled='false' AND ( virtual_cat_id = '$vcat_id' OR cat_path LIKE '%,$vcat_id,%' OR cat_path LIKE '$vcat_id,%')
                        ORDER BY virtual_cat_id";
        }else {
            $sql = "SELECT virtual_cat_id, parent_id, cat_path, virtual_cat_name, url FROM sdb_b2c_goods_virtual_cat 
                WHERE disabled='false' ORDER BY virtual_cat_id";
        }
        return $sql;
    }
    
    private function _getMasterId($vcat_id){
        $sql = "SELECT parent_id FROM sdb_b2c_goods_virtual_cat WHERE disabled='false' AND virtual_cat_id = '$vcat_id'";
        $cat = $this->db->selectrow($sql);
        if ($cat['parent_id']) return $this->_getMasterId($cat['parent_id']);
        return $vcat_id;
    }
}