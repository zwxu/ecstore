<?php
/**
 * @author chris.zhang
 *
 */
class b2c_widgets_goods_cat extends b2c_widgets_public {
    protected $_filter = array(
        'catId'     => 'cat_id',    //商品分类ID
        'typeId'    => 'type_id',   //商品类型ID
        'parentId'  => 'parent_id', //商品分类父类ID
    );

    protected $_outData = array(
        'catId'     => 'cat_id',    //商品分类ID
        'catName'   => 'cat_name',  //商品分类名称
        'catLink'   => '_link_',    //商品链接
    );

    /**
     * 获取商品分类并递归获取其子类
     * @param mix $catId    //分类ID（可选）
     * array(1,4)/1;
     * @param bool $master //是否获取父分类（只针对$catId为子类时）
     *
     */
    public function getGoodsCatMap($catId=null, $master = false){
        $master = $this->get_bool($master);
        //if (!isset($catId) || !$catId) return array();
        //$catId = $filter['catId'];
        $data = array();
        if ($catId && is_array($catId)){
            foreach ($catId as $cId) {
                if (!$this->_existCat(array('catId'=>$cId))){
                    //$data[$cId] = array();
                    continue;
                }
                if ($master){
                    $_tmp = $this->_getCatMap($cId,true);
                    $data = $this->array_merge_recursive($data, $_tmp);
                }else {
                    $_tmp = $this->_getCatMap($cId,false);
                    $data[$cId] = $_tmp[$cId];
                }
            }
        }else{
            if ($catId && !$this->_existCat(array('catId'=>$catId))) return array();
            $data = $this->_getCatMap($catId, $master);
        }

        return $data;
    }

    /**
     * 通过类型ID获取商品分类并递归获取其子类
     * @param int $typeId   //商品类型ID
     *
     */
    public function getGoodsCatMapByType($typeId){
        if (!isset($typeId) || !$typeId) return array();

        $data = array();
//        if (is_array($typeId)){
//            foreach ($typeId as $tId){
//                $_rows = $this->_getFirstCatByType($tId);
//                foreach ((array)$_rows as $cat){
//                    $_tmp = $this->_getCatMap($cat['catId']);
//                    $data[$tId][$cat['catId']] = $_tmp[$cat['catId']];
//                }
//            }
//        }else {
        $_rows = $this->_getFirstCatByType($typeId);
        foreach ((array)$_rows as $cat){
            $_tmp = $this->_getCatMap($cat['catId']);
            $data[$typeId][$cat['catId']] = $_tmp[$cat['catId']];
        }
//        }

        return $data;
    }

    private function _existCat($filter){
        $catId = $this->_getFilter($filter);
        $result = $this->app->model('goods_cat')->count($filter);
        if ($result > 0) return true;
        return false;
    }

    protected function _getFilter($filter){
        $_filter = parent::_getFilter($filter);
        $_filter = array_merge(array('disabled'=>'false'),$_filter);

        return $_filter;
    }


    private function _getFirstCatByType($typeId, $output=true){
        if (!$this->_existCat(array('typeId'=>$typeId,'parentId'=>0))) return array();
        $typeId = intval($typeId);
        $sql = "SELECT cat_id, cat_name FROM sdb_b2c_goods_cat
                WHERE disabled = 'false' AND type_id = '$typeId' AND parent_id = 0 ORDER BY cat_id";
        $_rows = $this->db->select($sql);
        if (!$output) return $_rows;

        $data = array();
        foreach ($_rows as $cat){
            $data[] = $this->_getOutData($cat);
        }
        return $data;
    }

    private function _getLink($catId){
        return $this->get_link(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array($catId)));
    }

    private function _getCatMap($cat_id=null, $master = false, $output=true){
        $master = $this->get_bool($master);
        $output = $this->get_bool($output);

        $sql    = $this->_getSql($cat_id, $master);
        $data   = $this->db->select($sql);

        $prefix = $this->prefix;
        $_data  = array();
        foreach((array)$data as $row){
            $path = $row['cat_path'];
            $c_id = $row['cat_id'];
            $p_id = $row['parent_id'];
            $row['_link_'] = $this->_getLink($c_id);
            if ($output) $row = $this->_getOutData($row);

            $parents = array_filter(explode(',', $path));

            if(empty($parents) || count($parents) == 0 || ($cat_id && $cat_id == $c_id)){
                $_data[$c_id] = $row;
            }elseif(count($parents) == 1){
                $_data[$p_id][$prefix][$c_id] = $row;
            }else{
                krsort($parents);
                $out = array();
                $i = 0;
                foreach ($parents as $v){
                    $_tmp = array();
                    if ($i === 0){
                        $_tmp[$v][$prefix][$c_id] = $row;
                    }else{
                        $_tmp[$v][$prefix] = $out;
                    }
                    $out = $_tmp;
                    $i++;
                    if ($cat_id && $v == $cat_id) break;
                }
                $_data = $this->array_merge_recursive($_data, $out);
            }
        }

        return $_data;
    }

    private function _getSql(&$cat_id = null, $master){
        if ($cat_id){
            $cat_id = $this->addslashes($cat_id);
            if ($master) $cat_id = $this->_getMasterId($cat_id);
            $sql = "SELECT cat_id, parent_id, cat_path, cat_name FROM sdb_b2c_goods_cat
                    WHERE disabled='false' AND ( cat_id = '$cat_id' OR cat_path LIKE '%,$cat_id,%')
                        ORDER BY cat_path,p_order,cat_id ";
        }else {
            $sql = "SELECT cat_id, parent_id, cat_path, cat_name FROM sdb_b2c_goods_cat
                WHERE disabled='false' ORDER BY cat_path,p_order,cat_id ";
        }
        return $sql;
    }

    private function _getMasterId($cat_id){
        $sql = "SELECT parent_id FROM sdb_b2c_goods_cat WHERE disabled='false' AND cat_id = '$cat_id'";
        $cat = $this->db->selectrow($sql);
        if ($cat['parent_id']) return $this->_getMasterId($cat['parent_id']);
        return $cat_id;
    }
}
