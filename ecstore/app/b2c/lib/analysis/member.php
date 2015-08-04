<?php
class b2c_analysis_member extends ectools_analysis_abstract implements ectools_analysis_interface{
    public $detail_options = array(
        'hidden' => true,
    );
    public $graph_options = array(
        'hidden' => true,
    );

    public function finder(){
        return array(
            'model' => 'b2c_mdl_analysis_member',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href'=>'index.php?app=b2c&ctl=admin_analysis&act=member&action=export',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('会员购物排行'),
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

        $render->pagedata['timefrom'] = $filter['time_from'];
        $render->pagedata['timeto'] = $filter['time_to'];

        $html = $render->fetch('admin/analysis/member.html', 'b2c');

        $this->_render->pagedata['rank_html'] = $html;
    }
}