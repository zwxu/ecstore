<?php

 
/**
 * 促销规则基类(购物车项预过滤[prefilter] + 购物车过滤规则[postfilter])
 * $ 2010-05-06 21:04 $
 */
class b2c_sales_basic_abstract
{
    protected $app;

    public function __construct() {
        $this->app = app::get('b2c');
        $this->db  = kernel::database();
    }

    /**
     * 生成模板(用于后台处理显示使用)
     *
     * @param array $aTemplate
     * @param array $aData
     * @return string
     */
    public function makeTemplate($aTemplate = array(), $aData = array(),$vpath = 'conditions',$is_auto = false) {
        if(empty($this->default_aggregator)) return false;
        return kernel::single($this->default_aggregator)->view($aTemplate,$aData,$vpath,0,null,$is_auto);
    }

    // 获取所有促销模板列表信息 (由子类process 去实现)
    public function getTemplateList() {
        return array();
    }
}
?>
