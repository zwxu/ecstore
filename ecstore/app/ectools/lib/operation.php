<?php

 
/**
 * 定义order操作的抽象类
 * 主要实现接口ectools_interface_order_operaction的freezeGoods和unfreezeGoods的方法
 * 
 * @version 0.1
 * @package ectools.lib
 */
abstract class ectools_operation implements ectools_interface_operation
{
    /**
     * @var object 对应的应用对象
     */ 
    protected $app;
    
    /**
     * @var object 对象实体-对应实体对象
     */ 
    protected $model;
}
