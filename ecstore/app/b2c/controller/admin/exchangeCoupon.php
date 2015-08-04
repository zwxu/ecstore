<?php
 
 
include_once('objectPage.php');
class b2c_ctl_admin_exchangeCoupon extends objectPage{

    var $workground = 'b2c_ctl_admin_sale';
    var $object = 'trading/exchangeCoupon';
    var $finder_action_tpl = 'sale/coupon/exchange/finder_action.html'; //默认的动作html模板,可以为null
    var $noRecycle = true;
    var $filterUnable = true;

    function _detail(){
        return array('show_detail'=>array('label'=>app::get('b2c')->_('退款单信息'),'tpl'=>'sale/coupon/exchange/addExchange.html'));
    }
    function show_detail($cpnsId){
        $oCoupon = $this->app->model('trading/coupon');
        $aList = $oCoupon->getUserCouponArr();
        $this->pagedata['cpns_list'] = $aList;
        if ($cpnsId) {
            $this->pagedata['cpns'] = $oCoupon->getCouponById($cpnsId);
        }else{
            $this->pagedata['cpns']['cpns_id'] = $aList[0][0]['cpns_id'];
        }
    }
    function showAddExchange($cpnsId=null){
        $oCoupon = $this->app->model('trading/coupon');
        $aList = $oCoupon->getUserCouponArr();
        $this->pagedata['cpns_list'] = $aList;
        if ($cpnsId) {
            $this->pagedata['cpns'] = $oCoupon->getCouponById($cpnsId);
        }else{
            $this->pagedata['cpns']['cpns_id'] = $aList[0][0]['cpns_id'];
        }
        $this->page('sale/coupon/exchange/addExchange.html');
    }

    function addExchange(){
        $this->begin('index.php?app=b2c&ctl=admin_sale/exchangeCoupon&act=index');
        if(empty($_POST['cpns_id']) || $_POST['cpns_id']=='undefined'){
            $this->end(false, app::get('b2c')->_("优惠券名称不能为空"), 'index.php?app=b2c&ctl=admin_sale/exchangeCoupon&act=index');
        }
        $oExchangeCoupon = &$this->app->model('trading/exchangeCoupon');
        if(!$oExchangeCoupon->saveExchange($_POST)) {
            $this->end(false, $oExchangeCoupon->message, 'index.php?app=b2c&ctl=admin_sale/exchangeCoupon&act=index');
        }else{
            $this->end(true, app::get('b2c')->_('保存成功'), 'index.php?app=b2c&ctl=admin_sale/exchangeCoupon&act=index');
        }
    }
}
?>
