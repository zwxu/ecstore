<?php
 
 
class b2c_ctl_admin_couponGenerate extends desktop_controller{

    var $workground = 'b2c.workground.sale';
    var $object = 'trading/couponGenerate';
    var $finder_filter_tpl = 'sale/coupon/generate/finder_filter.html';
    var $deleteAble = false;
    var $allowImport = false;
    var $allowExport = false;

    function index($cpnsId=null) {
        if($cpnsId){
            parent::index(array('params'=>array('cpns_id'=>$cpnsId)));
        }else{
            parent::index();
        }
    }

//-------------------------------------------
    function addCouponGenerate($cpnsId, $pmtId=null) {
        $_SESSION['SWP_PROMOTION'] = null;
        $oCoupon = &$this->app->model('trading/coupon');
        $oPromotion = &$this->app->model('trading/promotion');
        $_SESSION['SWP_PROMOTION']['couponInfo'] = $oCoupon->getCouponById($cpnsId);
        if ($pmtId != null) {
            $aData = $oPromotion->getPromotionFieldById($pmtId,array('*'));
            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = array(
                                                                    'pmt_solution' => unserialize($aData['pmt_solution']),
                                                                    'pmt_ifcoupon' => $aData['pmt_ifcoupon'],
                                                                    'pmt_time_begin' => dateFormat($aData['pmt_time_begin']),
                                                                    'pmt_time_end' => dateFormat($aData['pmt_time_end']),
                                                                    'pmt_describe' => $aData['pmt_describe']
            );
            $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'] = $aData['pmts_id'];
            $_SESSION['SWP_PROMOTION']['selectProduct']['pmt_bond_type'] = $aData['pmt_bond_type'];

            $_SESSION['SWP_PROMOTION']['basic']['pmt_id'] = $pmtId;
        }
        $_SESSION['SWP_PROMOTION']['basic']['cpns_id'] = $cpnsId;
        $this->selectPromotionRule();
    }

    function doSelectPromotionRule() {
        $oPromotion = &$this->app->model('trading/promotion');
        if (!empty($_POST['pmts_id']) && ($_POST['pmts_id'] != $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'])) {
//            $getSchemeFieldById
            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = null;
            $_SESSION['SWP_PROMOTION']['selectProduct'] = null;//������֮�������ƿ�
        }
        $aData = $oPromotion->getSchemeFieldById('pmts_type', $_POST['pmts_id']);
        $_SESSION['SWP_PROMOTION']['selectPromotionRule'] = &$_POST;
        $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmt_type'] = $aData['pmts_type'];
        $this->writePromotionRule();

    }

    function doWritePromotionRule() {
        $this->_filterPost();
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_ifcoupon'] = $_POST['pmt_ifcoupon'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'] = $_POST['pmt_time_begin'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'] = $_POST['pmt_time_end'];
        $pmtSolution = &$_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'];
        foreach($pmtSolution['condition'] as $k => $condition) {
            $pmtSolution['condition'][$k][1] = $_POST[$pmtSolution['condition'][$k][0]];
        }
        foreach($pmtSolution['method'] as $k => $method) {
            $pmtSolution['method'][$k][1] = $_POST[$pmtSolution['method'][$k][0]];
        }
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'] = $_POST['pmt_describe'];
        $this->selectProduct();
    }

    function doSelectProduct() {
        $_SESSION['SWP_PROMOTION']['selectProduct'] = &$_POST;
        $this->publish();
    }

    function doPublish() {
        $oPromotion = &$this->app->model('trading/promotion');
        $aPromotion = array_merge($_SESSION['SWP_PROMOTION']['selectPromotionRule'], $_SESSION['SWP_PROMOTION']['writePromotionRule'], $_SESSION['SWP_PROMOTION']['selectProduct'],        $_SESSION['SWP_PROMOTION']['basic']);
        $oPromotion->addPromotion($aPromotion, 2);
        $this->splash('success', 'index.php?app=b2c&ctl=admin_sale/coupon&act=index');
    }

    function _filterPost() {
        if (is_array($_POST)) {
            foreach ($_POST as $k => $v) {
                if (substr($k, 0 ,4) == 'ext-') {
                    unset($_POST[$k]);
                }
            }
        }
    }
}
//-------------------------------------------

?>
