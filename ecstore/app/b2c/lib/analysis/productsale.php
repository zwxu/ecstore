<?php


class b2c_analysis_productsale extends ectools_analysis_abstract implements ectools_analysis_interface {
    public $detail_options = array(
        'hidden' => true,
    );
    public $graph_options = array(
        'hidden' => true,
    );

    public function finder(){
        return array(
            'model' => 'b2c_mdl_analysis_productsale',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href'=>'index.php?app=b2c&ctl=admin_analysis&act=productsale&action=export',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('商品销售排行'),
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }

    public function rank(){
        $filter = $this->_params;
        $filter['time_from'] = isset($filter['time_from'])?$filter['time_from']:'';
        $filter['time_to'] = isset($filter['time_to'])?$filter['time_to']:'';

        $render = kernel::single('base_render');

        $productObj = $this->app->model('analysis_productsale');
        $numProducts = $productObj->getlist('*', $filter, 0, 5, 'saleTimes desc');
        $priceProducts = $productObj->getlist('*', $filter, 0, 5, 'salePrice desc');

        $render->pagedata['numProducts'] = $numProducts;
        $render->pagedata['priceProducts'] = $priceProducts;
        $imageDefault = app::get('image')->getConf('image.set');
        $render->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $html = $render->fetch('admin/analysis/productsale.html','b2c');

        $this->_render->pagedata['rank_html'] = $html;
    }
}