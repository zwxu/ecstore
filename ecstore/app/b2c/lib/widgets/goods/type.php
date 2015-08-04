<?php
/**
 * @author chris.zhang
 * 
 */
class b2c_widgets_goods_type extends b2c_widgets_public {
    //类型的
    protected $_outData = array(
        'typeId'        => 'type_id',   //类型ID
        'typeName'      => 'name',      //类型名称 
        'catRows'       => 'cats',      //商品分类信息（可选）
        'vCatRows'      => 'vcats',     //虚拟分类的信息（可选）
        'brandRows'     => 'brands',    //品牌信息（可选）
    );
    
    protected $_filter = array(
        'typeId'    => 'type_id',   //类型ID
    );
    
    /**
     * 获取商品类型信息的自定义格式
     * @param array $filter
     * array(
            'typeId'    => array(1,2)/1,    //类型ID
            'isCat'     => 'true'/'false',  //是否获取分类递归信息
            'isVCat'    => 'true'/'false',  //是否获取虚拟分类递归信息
            'isBrand'   => 'true'/'false',  //是否获取品牌信息
        ),
     */
    public function getGoodsTypeMap($filter) {
        $obj_type = &$this->app->model('goods_type');
        
        $isCat      = $filter['isCat'];//是否获取分类递归信息
        $isVCat     = $filter['isVCat'];//是否获取虚拟分类递归信息
        $isBrand    = $filter['isBrand'];//是否获取品牌信息
        
        $_filter = $this->_getFilter($filter);
        $_data = array();
        
        $data = $obj_type->getList('*',$_filter,0,-1);
        if (is_array($data))
        foreach ($data as $_k => $_v){
            if ($isCat=='true'){
                $cats = kernel::single('b2c_widgets_goods_cat')->getGoodsCatMapByType($_v['type_id']);
                $_v['cats'] = $cats[$_v['type_id']];
            }
            if ($isVCat=='true'){
                $vcats = kernel::single('b2c_widgets_virtual_cat')->getVirtualCatMapByType($_v['type_id']);
                $_v['vcats'] = $vcats[$_v['type_id']];
            }
            if ($isBrand=='true'){
                $brands = kernel::single('b2c_widgets_brand')->getBrandByType($_v['type_id']);
                $_v['brands'] = $brands[$_v['type_id']];
            }
            $_data[$_k] = $this->_getOutData($_v);
        }
        
        return $_data;
    }
    
    protected function _getFilter($filter){
        $_filter = parent::_getFilter($filter);
        $_filter = array_merge(array('disabled'=>'false'),$_filter);
        
        return $_filter;
    }
    
}