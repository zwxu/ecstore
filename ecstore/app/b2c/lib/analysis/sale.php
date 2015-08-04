<?php


class b2c_analysis_sale extends ectools_analysis_abstract implements ectools_analysis_interface 
{
    public $detail_options = array(
        'hidden' => false,
        'force_ext' => false,
    );
    public $graph_options = array(
        'hidden' => true,
    );
    
    public $logs_options = array(
        '1' => array(   
            'name' => '收款额',
            'flag' => array(),
            'memo' => '支付单支付金额总计（排除预存款）',
            'icon' => 'money.gif',
        ),
        '2' => array(
            'name' => '退款额',
            'flag' => array(),
            'memo' => '退款单退款金额总计',
            'icon' => 'money_delete.gif',
        ),
        '3' => array(
            'name' => '收入',
            'flag' => array(),
            'memo' => '“收款额”减去“退款额”',
            'icon' => 'coins.gif',
        ),
    );

    public function get_logs($time){
        $filter = array(
            'time_from' => $time,
            'time_to' => $time+86400,
        );
        $saleObj = $this->app->model('analysis_sale');
        $payMoney = $saleObj->get_pay_money($filter);
        $refundMoney = $saleObj->get_refund_money($filter);
        $earn = $payMoney-$refundMoney;

        $result[] = array('type'=>0, 'target'=>1, 'flag'=>0, 'value'=>$payMoney);
        $result[] = array('type'=>0, 'target'=>2, 'flag'=>0, 'value'=>$refundMoney);
        $result[] = array('type'=>0, 'target'=>3, 'flag'=>0, 'value'=>$earn);

        return $result;
    }

    public function ext_detail(&$detail){
        $filter = $this->_params;
        $filter['time_from'] = isset($filter['time_from'])?strtotime($filter['time_from']):'';
        $filter['time_to'] = isset($filter['time_to'])?(strtotime($filter['time_to'])+86400):'';

        $saleObj = $this->app->model('analysis_sale');
        $detail['收款额']['value'] = $saleObj->get_pay_money($filter);
        $detail['退款额']['value'] = $saleObj->get_refund_money($filter);
        $detail['收入']['value'] =  $detail['收款额']['value'] - $detail['退款额']['value'];
        
        $detail['收款额']['value'] = $detail['收款额']['value']?number_format($detail['收款额']['value'],2,"."," "):0;
        $detail['退款额']['value'] = $detail['退款额']['value']?number_format($detail['退款额']['value'],2,"."," "):0;
        $detail['收入']['value'] = $detail['收入']['value']?number_format($detail['收入']['value'],2,"."," "):0;
    }

    public function finder() 
    {
        return array(
            'model' => 'b2c_mdl_analysis_sale',
            'params' => array(
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('生成报表'),
                        'class'=>'export',
                        'icon'=>'add.gif',
                        'href'=>'index.php?app=b2c&ctl=admin_analysis&act=sale&action=export',
                        'target'=>'{width:400,height:170,title:\''.app::get('b2c')->_('生成报表').'\'}'),
                ),
                'title'=>app::get('b2c')->_('销售收入统计'),
                'use_buildin_recycle'=>false,
                'use_buildin_selectrow'=>false,
            ),
        );
    }
}
