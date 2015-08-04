<?php


class b2c_analysis_index extends ectools_analysis_abstract implements ectools_analysis_interface  
{
    protected $_title = '经营概况';
    public $logs_options = array(
        '1' => array(   
                        'name' => '订单量',
                        'flag' => array(),
                    ),
        '2' => array(
                        'name' => '订单额',
                        'flag' => array(),
                    ),
    );

    public $graph_options = array(
        'iframe_height' => 300,
    );

    public $finder_options = array(
        'hidden' => true,
    );

    public function ext_detail(&$detail){
        $detail = array();
        $filter = $this->_params;
        $filter['time_from'] = isset($filter['time_from'])?strtotime($filter['time_from']):'';
        $filter['time_to'] = isset($filter['time_to'])?(strtotime($filter['time_to'])+86400):'';

        $saleObj = $this->app->model('analysis_sale');
        $payMoney = $saleObj->get_pay_money($filter);
        $refundMoney = $saleObj->get_refund_money($filter);
        $earn = $payMoney-$refundMoney;

        $detail[app::get('b2c')->_('收入')]['value']= $earn;
        $detail[app::get('b2c')->_('收入')]['memo']= app::get('b2c')->_('“收款额”减去“退款额”');
        $detail[app::get('b2c')->_('收入')]['icon'] = 'coins.gif';

        $shopsaleObj = $this->app->model('analysis_shopsale');
        $filterOrder = array(
            'time_from' => $filter['time_from'],
            'time_to' => $filter['time_to'],
        );
        $filterShip = array(
            'time_from' => $filter['time_from'],
            'time_to' => $filter['time_to'],
            'ship_status' => 1,
        );
        $filterPay = array(
            'time_from' => $filter['time_from'],
            'time_to' => $filter['time_to'],
            'pay_status' => 1,
        );

        $order = $shopsaleObj->get_order($filterOrder); //全部
        $orderAll = $order['saleTimes'];
        
        $orderShip = $shopsaleObj->get_order($filterShip); //已发货
        $orderShip = $orderShip['saleTimes'];
        
        $orderPay = $shopsaleObj->get_order($filterPay); //已支付
        $orderPay = $orderPay['saleTimes'];


        $detail[app::get('b2c')->_('新增订单')]['value']= $orderAll;
        $detail[app::get('b2c')->_('新增订单')]['memo']= app::get('b2c')->_('新增加的订单数量');
        $detail[app::get('b2c')->_('新增订单')]['icon'] = 'application_add.gif';
        $detail[app::get('b2c')->_('付款订单')]['value']= $orderPay;
        $detail[app::get('b2c')->_('付款订单')]['memo']= app::get('b2c')->_('付款的订单数量');
        $detail[app::get('b2c')->_('付款订单')]['icon'] = 'application_key.gif';
        $detail[app::get('b2c')->_('发货订单')]['value']= $orderShip;
        $detail[app::get('b2c')->_('发货订单')]['memo']= app::get('b2c')->_('发货的订单数量');
        $detail[app::get('b2c')->_('发货订单')]['icon'] = 'application_go.gif';

        $memObj = $this->app->model('members');
        $filterMem = array(
            'regtime' => 'true',
            'regtime_from' => date('Y-m-d',$filter['time_from']),
            'regtime_to' => date('Y-m-d',$filter['time_to']),
            '_regtime_search' => 'between',
            '_DTIME_' => array(
                'H'=>array('regtime_from'=>'00','regtime_to'=>'00'),
                'M'=>array('regtime_from'=>'00','regtime_to'=>'00')
            ),
        );

        $memberNewadd = $memObj->count($filterMem);
        $memberNum = $memObj->count();

        $detail[app::get('b2c')->_('新增会员')]['value']= $memberNewadd;
        $detail[app::get('b2c')->_('新增会员')]['memo']= app::get('b2c')->_('新增加的会员数量');
        $detail[app::get('b2c')->_('新增会员')]['icon'] = 'folder_user.gif';
        $detail[app::get('b2c')->_('会员总数')]['value']= $memberNum;
        $detail[app::get('b2c')->_('会员总数')]['memo']= app::get('b2c')->_('网店会员总数');
        $detail[app::get('b2c')->_('会员总数')]['icon'] = 'group_add.gif';
    }

    public function rank(){
        $filter = $this->_params;
        $filter['time_from'] = isset($filter['time_from'])?$filter['time_from']:'';
        $filter['time_to'] = isset($filter['time_to'])?$filter['time_to']:'';

        $render = kernel::single('base_render');

        $productObj = $this->app->model('analysis_productsale');
        $numProducts = $productObj->getlist('*', $filter, $offset=0, 5, 'saleTimes desc');
        $priceProducts = $productObj->getlist('*', $filter, $offset=0, 5, 'salePrice desc');

        $render->pagedata['numProducts'] = $numProducts;
        $render->pagedata['priceProducts'] = $priceProducts;
        $imageDefault = app::get('image')->getConf('image.set');
        $render->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $html = $render->fetch('admin/analysis/productlist.html','b2c');

        $this->_render->pagedata['rank_html'] = $html;
    }
}//End Class