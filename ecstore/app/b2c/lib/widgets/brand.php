<?php
/**
 * @author chris.zhang
 * 
 */
class b2c_widgets_brand extends b2c_widgets_public {
    protected $_filter = array(
        'brandId' => 'brand_id',
    );
    
    //品牌返回信息格式 
    protected $_outData = array(
        'brandId'       => 'brand_id',          //品牌ID
        'brandName'     => 'brand_name',        //品牌名称
        'brandUrl'      => 'brand_url',         //品牌链接
        'brandLogo'     => 'brand_logo_url',    //品牌Logo
        'brandKeywords' => 'brand_keywords',    //品牌关键字
        'brandLink'     => '_brand_link_',         //品牌链接
        //'brandOrderNum' => 'ordernum',        //品牌排序
    );
    
    /**
     * 根据ID获取品牌信息
     * @param mix $brandId  //品牌ID
     */
    public function getBrandList($brandId){
        $filter = array();
        if (isset($brandId) && $brandId){
            $filter = array('brandId'=>$brandId);
            if (!$this->_existBrand($filter)) return array();
        }
        
        $filter = $this->_getFilter($filter);
        $orderBy = array('ordernum DESC');
        $columns = ' brand_id,brand_name,brand_url,brand_logo,brand_keywords,ordernum ';
        $_rows   = $this->app->model('brand')->getList($columns,$filter,0,-1,$orderBy);
        
        $data = array();
        foreach ((array)$_rows as $row){
            $imageUrl = $this->get_image_url($row['brand_logo']);
            $row['brand_logo_url'] = $imageUrl['url_original'];
            $row['_brand_link_']   = $this->getBrandLink($row['brand_id']);
            $data[] = $this->_getOutData($row);
        }
        return $data;
    }
    
    /**
     * 通过商品类型获取其关联的品牌
     * @param mix $typeId   //商品类型ID
     * array(1,2,3)/1
     */
    public function getBrandByType($typeId){
        if (empty($typeId)) return array();
        $types = kernel::single('b2c_widgets_goods_type')->getGoodsTypeMap(array('typeId'=>$typeId));
        
        $data = array();
        foreach ((array)$types as $type){
            $_rows = $this->_getBrandIdByTypeId($type['typeId']);
            foreach ((array)$_rows as $_row){
                $data[$type['typeId']][$_row['brand_id']] = array_pop($this->getBrandList($_row['brand_id']));
            }
        }
        return $data;
    }
    
    public function getBrandLink($brandId){
        $params = array('app'=>'b2c','ctl'=>'site_brand','act'=>'index','arg'=>$brandId);
        return $this->get_link($params);
    }
    
    private function _getBrandIdByTypeId($typeId){
        if (empty($typeId)) return array();
		$typeId = intval($typeId);
        $data = array();
        $sql = "SELECT type_id, brand_id FROM sdb_b2c_type_brand WHERE type_id = '$typeId'";
        $data = $this->db->select($sql);//TODO 可直接调用model的brand->getBidByType($typeId)
        return $data;
    }
    
    private function _existBrand($filter){
        $filter = $this->_getFilter($filter);
        $result = app::get('b2c')->model('brand')->count($filter);
        if ($result > 0) return true;
        return false;
    }
    
    protected function _getFilter($filter){
        $filter = parent::_getFilter($filter);
        $_filter = array_merge(array('disabled'=>'false'),$filter);
        
        return $_filter;
    }
    
}