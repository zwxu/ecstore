<?php


class b2c_analysis_advance extends ectools_analysis_abstract implements ectools_analysis_interface{
    public $logs_options = array();
	
	public function __construct(&$app){
		parent::__construct($app);
		$this->logs_options = array(
			'1' => array(   
				'name' => app::get('b2c')->_('存入金额'),
				'flag' => array(),
				'memo' => app::get('b2c')->_('预存款收款额'),
				'icon' => 'money_add.gif',
			),
			'2' => array(
				'name' => app::get('b2c')->_('消费金额'),
				'flag' => array(),
				'memo' => app::get('b2c')->_('预存款支出额'),
				'icon' => 'money_delete.gif',
			),
			'3' => array(
				'name' => app::get('b2c')->_('余额'),
				'flag' => array(),
				'memo' => app::get('b2c')->_('预存款余额总计'),
				'icon' => 'coins.gif',
			),
			'4' => array(
				'name' => app::get('b2c')->_('使用人数'),
				'flag' => array(),
				'memo' => app::get('b2c')->_('使用过预存款的人数'),
				'icon' => 'user.gif',
			),
		);
	}

    public $graph_options = array(
        'hidden' => true,
    );

    public function get_logs($time){
        $filter = array(
            'time_from' => $time,
            'time_to' => $time+86400,
        );
        $advanceObj = $this->app->model('analysis_advance');
        $money = $advanceObj->get_money($filter);
        $import_money = $money['import_money']; //订单量
        $explode_money = $money['explode_money']; //订单额

        $result[] = array('type'=>0, 'target'=>1, 'flag'=>0, 'value'=>$import_money);
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>0, 'value'=>$explode_money);

        return $result;
    }

    public function ext_detail(&$detail){
        $filter = $this->_params;
        $filter['time_from'] = isset($filter['time_from'])?strtotime($filter['time_from']):'';
        $filter['time_to'] = isset($filter['time_to'])?(strtotime($filter['time_to'])+86400):'';
        
        $advanceObj = $this->app->model('analysis_advance');
        $shop_advance = $advanceObj->get_shop_advance();
        $useNum = $advanceObj->get_member_num();
        
        $imorexmoney = $advanceObj->get_money($filter);
        $detail['使用人数']['value'] = $useNum;
        $detail['存入金额']['value'] = $imorexmoney['import_money'];
        $detail['消费金额']['value'] = $imorexmoney['explode_money'];
        $detail['余额']['value'] = $shop_advance;
    }

    public function finder() 
    {
        return array(
            'model' => 'b2c_mdl_analysis_advance',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href'=>'index.php?app=b2c&ctl=admin_analysis&act=advance&action=export',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('预存款统计'),
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }
}
