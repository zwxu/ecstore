<?php

class b2c_widgets {
    private static $_prefix = 'b2c_widgets_';
    //数据对象对外名称
    private static $_widgets_conf = array(
        'Goods'         => 'goods',
        'GoodsCat'      => 'goods_cat',
        'GoodsType'     => 'goods_type',
        'Brand'         => 'brand',
        'VirtualCat'    => 'virtual_cat',
        'Comment'       => 'comment',
        'Article'       => 'article',
    );
    /**
     * 获取某个数据对象
     * @param string $obj_name
     */
    public static function load($obj_name){
        if (!array_key_exists($obj_name, self::$_widgets_conf)) return false;
        return self::_get_obj($obj_name);
    }
    
    private static function _get_obj($obj_name){
        $_obj_name      = self::$_widgets_conf[$obj_name];
        $object_class   = self::$_prefix.$_obj_name;
        $object         = kernel::single($object_class);
        return $object;
    }
    
}