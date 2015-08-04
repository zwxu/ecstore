<?php
/**
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_ctl_admin_setting extends desktop_controller
{
    public $status;

    public function __construct( &$app ) {
        $this->app = $app;
        parent::__construct( $app );
    }

    /*
     * 配置项
     */
    public function index()
    {
        $o = kernel::single('proregister_setting');
        $setting = $o->getSetting();
        if( !$setting['status'] ) $setting['status'] = '1';
        $this->pagedata['setting'] = $setting;
        $this->pagedata['status'] = $o->getStatusArr();

        $this->pagedata['action_url'] = app::get('desktop')->router()->gen_url( array('app'=>'proregister','ctl'=>'admin_setting','act'=>'save') );

        $this->init_getcoupon_filter();

        //是否显示送积分
        if( !kernel::single("proregister_promotion_getscore")->get_status() ) {
            $this->pagedata['show_score'] = 'false';
        }

        $payment_cfg = app::get('ectools')->model('payment_cfgs');
        $arrPayments = $payment_cfg->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));
        $hasDeposit = 'false';
        foreach($arrPayments as $key=>$payment)
        {
            if (trim($payment['app_id']) == 'deposit')
            {
                $hasDeposit = 'true';
            }
        }
        $this->pagedata['is_deposit'] = $hasDeposit;
        $this->page( 'admin/setting.html' );
    }
    #End Func

    /*
     * save setting
     */
    public function save()
    {
        $this->begin( );
        $aSave = $_POST['setting'];
        $etime = $aSave['etime'];
        $stime = $aSave['stime'];
        $aSave['enrollment'] = intval($aSave['enrollment']); //添加注册人数限制 

        if( strtotime($etime)<=strtotime($stime) ) {
            $this->end( false,'活动结束时间必须大于开始时间!' );
        }

        if( strtotime($etime)<time() ) {
            $this->end( false,'活动结束时间小于当前!' );
        }

        if( $aSave['getcoupon'] ) {
            if( is_array($aSave['getcoupon']) ) {
                $cpns_id = $aSave['getcoupon'];
            } else {
                //$cpns_id = explode(',',$aSave['getcoupon']); //因为object与rows重复出现，导致无法正确删除，所以只解析一种值。
                $cpns_id = 0;
            }

            if( !app::get('b2c')->model('coupons')->count( array('cpns_id'=>$cpns_id) ) ) {
                $aSave['getcoupon'] = '';
            }
        }

        if( empty($aSave['getcoupon']) && empty($aSave['getadvance']) && empty($aSave['getscore']) ) {
            $this->end( false,'配置失败！必填一项优惠信息' );
        }
        kernel::single('proregister_setting')->setSetting($aSave);
        $this->end( true,'配置成功！' );
    }
    #End Func

    /*
     * 优惠券初始化筛选条件
     */
    private function init_getcoupon_filter()
    {
        $this->pagedata['filter']['cpns_id'] = kernel::single('b2c_coupon_filter')->get_coupon();
    }
    #End Func
}