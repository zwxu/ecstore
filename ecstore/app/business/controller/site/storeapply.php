<?php

class business_ctl_site_storeapply extends b2c_frontpage {

    public function __construct(&$app) {
        parent :: __construct($app);
        $obj_members = &app :: get('b2c') -> model('members');
        $this -> member = $obj_members -> get_current_member();

        $shopname = $app -> getConf('system.shopname');
        $this -> header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this -> _response -> set_header('Cache-Control', 'no-store');
        $this -> title = $this -> app -> _('申请入驻');
        $this -> keywords = app :: get('business') -> _('申请入驻') . '_' . $shopname;
        $this -> description = app :: get('business') -> _('申请入驻') . '_' . $shopname;

        //$this->sto= kernel::single("business_memberstore",$this -> member['member_id']);
    } 

    public function index() {
        $this -> path[] = array('title' => app :: get('business') -> _('申请入驻'), 'link' => $this -> gen_url(array('app' => 'b2c', 'ctl' => 'site_member', 'act' => 'index', 'full' => 1)));
        $this -> path[] = array('title' => app :: get('business') -> _('申请入驻'), 'link' => '#');
        $GLOBALS['runtime']['path'] = $this -> path;
        
        //2013-07-01  Add  先退出在注册。 PanF
        $this->unset_member();
        app :: get('b2c')->model('cart_objects')->setCartNum($arr);
        
		//设置模版
        $this->set_tmpl('investment');

        $obj_members = &app :: get('b2c') -> model('members');
        $this -> member = $obj_members -> get_current_member();
        $this -> page('site/store/index.html', false, 'business');
    } 


     function unset_member(){
        //卖家不强制退出。
       if( $this -> member['seller']=='seller'){
         return;
       }

        $auth = pam_auth::instance(pam_account::get_account_type($this -> member['member_id']));
        foreach(kernel::servicelist('passport') as $k=>$passport){
           $passport->loginout($auth);
        }

        app :: get('b2c')->member_id = 0;
        kernel::single('base_session')->start();
        unset($_SESSION['account'][pam_account::get_account_type(app :: get('b2c')->app_id)]);

        $this->cookie_path = kernel::base_url().'/';
        $this->set_cookie('MEMBER',null,time()-3600);
        $this->set_cookie('UNAME','',time()-3600);
        $this->set_cookie('MLV','',time()-3600);
        $this->set_cookie('CUR','',time()-3600);
        $this->set_cookie('LANG','',time()-3600);
        $this->set_cookie('S[MEMBER]','',time()-3600);
        foreach(kernel::servicelist('member_logout') as $service){
            $service->logout();
        }

		//同步到ucenter yindingsheng
		if($uc = kernel::service("uc_user_synlogout")){
			$uc->uc_user_synlogout();
		}
		//同步到ucenter yindingsheng
    }


} 
