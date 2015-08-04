<?php
class b2c_api_basic_member {
    public $app;

    public function __construct($app) {
        $this->app = $app;

         //店铺校验 
         $data = $_POST ? $_POST: $_GET;
        if($data['method'] &&  trim($data['source_type']) !='system'){
            foreach(kernel::servicelist('business.api_verify_store') as $object)
            {
                 if(is_object($object))
                 {
                     if(method_exists($object,'verifyStore'))
                     {
                        $result = $object->verifyStore(trim($data['store_cert']));
                        if( $result==false){
                            echo app::get('b2c')->_('店铺校验失败！');
                            exit;
                        }
                     }
                 }
            }
        }
    }
    /**
     * 返回所有会员级别
     * @param array $request
     * @param object $rpcService, 框架传入参数 @see base_rpc_service::process_rpc()
     * @return array
     */
    public function get_member_lv_all( $request, $rpcService ) {
        $memberLvMdl = $this->app->model('member_lv');
        $rows = $memberLvMdl->getList('member_lv_id,name as member_lv_name');
        // 不要向api框架抛出NULL，强制类型转换得到 array()
        return (array) $rows;
    }

    /**
    * 根据注册时间、会员等级查询,返回基础字段.
    * @param array $request 查询参数
    * @param object $rpcService @see get_member_lv_all()
    */
    public function get_member_filter($request, $rpcService) {
        if ( ! $request ) {
            //$rpcService->send_user_error(app::get('b2c')->_('重要参数缺失'));
        }
        $member_lv_ids = $request['member_lv_ids'] ? json_decode($request['member_lv_ids'],1) : array();
        $member_regtime_begin = $request['member_regtime_begin'] ? (string)$request['member_regtime_begin'] : '0';
        $member_regtime_end = $request['member_regtime_end'] ? (string)$request['member_regtime_end'] : time();
        $page_size = $request['page_size'] ? (int)$request['page_size'] : 200; // 分页数据
        $page_no = $request['page_no'] ? max(1,(int)$request['page_no']) : 1;

        if ( ! $memberMdl = $this->app->model('members') ) {
            $rpcService->send_user_error(app::get('b2c')->_('系统错误'));
        }
        $filter = array(
            '_regtime_search'=>'between',
            'regtime_from'=>$member_regtime_begin,
            'regtime_end'=>$member_regtime_end
        );
        if ( $member_lv_ids ) {
            $filter['member_lv_id'] = $member_lv_ids;
        }

        $rows = $memberMdl->getList('member_id,member_lv_id,point,area,addr,email,mobile,tel,sex,reg_ip,disabled',$filter,($page_no-1)*$page_size,$page_size);
        if ( ! $rows ) {
            return array();
        }

        // 取account中的login_name
        $member_ids = array();
        foreach ( $rows as $row ) {
            $member_ids[] = $row['member_id'];
        }
        $accountMdl = app::get('pam')->model('account');
        $accounts = $accountMdl->getList('login_name,account_id', array('account_id'=>$member_ids),0,$page_size);
        foreach ( $rows as &$row ) {
            foreach ( $accounts as $account ) {
                if ( $row['member_id']==$account['account_id'] ) {
                    $row['login_name'] = $account['login_name'];
                }
            }
        }
        unset($row);
        return (array) $rows;
    }

    /**
     * 新建会员
     * @param array $request 会员数据
     * @param object $rpcService @see get_member_lv_all()
     */
    public function add($request, $rpcService) {
        $memberMdl =& $this->app->model("members");
        $errMsg = '';

        $tmp['pam_account']['login_name'] = $request['account'];
        $tmp['pam_account']['login_password'] = $request['password'];
        $tmp['pam_account']['psw_confirm'] = $request['psw_confirm'];
        $tmp['contact']['email'] = $request['email'];
        if( ! $memberMdl->validate($tmp, $errMsg)){
            $rpcService->send_user_error(app::get('b2c')->_('验证字段失败:'.$errMsg), $request );
        }
        if ( ! $member_id = $memberMdl->create($tmp) ) {
            $rpcService->send_user_error(app::get('b2c')->_('添加会员失败'), $tmp );
        }
        $account['member_id'] = $member_id;
        $account['uname'] = $tmp['pam_account']['login_name'];
        $account['passwd'] = $tmp['pam_account']['psw_confirm'];
        $account['email'] = $tmp['contact']['email'];
        $account['is_frontend'] = false;
        $accountMdl =& $this->app->model('member_account');
        $accountMdl->fireEvent('register',$account,$member_id);

        return $account;
    }

    /**
     * 根据会员member_id获得会员详细信息
     * @param array $request 查询信息
     * @param object $rpcService @see get_member_lv_all()
     */
    public function get_member( $request, $rpcService ){
        if ( ! $request['member_id'] ) {
            $rpcService->send_user_error(app::get('b2c')->_('重要参数缺失'), array('member_id'=>(int)$request['member_id']));
        }
        $memberMdl = $this->app->model('members');
        $member = $memberMdl->getList('member_id,member_lv_id,point,area,addr,email,mobile,tel,sex,reg_ip,disabled',array('member_id'=>$request['member_id']),0,1);
        if ( ! $member ) {
            return array();
        }
        $member = current($member);
        $accountMdl = app::get('pam')->model('account');
        $account = $accountMdl->getList('login_name',array('account_id'=>$request['member_id']),0,1);
        if ( ! $account ) {
            return array();
        }
		$account = current($account);
        $member['login_name'] = $account['login_name'];
        // 当member_id不存在时$memberMdl->dump()返回NULL，不要向api框架抛出NULL，强制类型转换得到 array()
        return (array) $member;
    }

	/**
     * 根据登陆用户名获得会员详细信息
     * @param array $request 查询信息
     * @param object $rpcService @see get_member_lv_all()
     */
    public function get_member_by_name( $request, $rpcService ){
        if ( ! $request['login_name'] ) {
            $rpcService->send_user_error(app::get('b2c')->_('重要参数缺失'), array('login_name'=>$request['login_name']));
        }
        $accountMdl = app::get('pam')->model('account');
        $account = $accountMdl->getList('login_name,account_id',array('login_name'=>$request['login_name']),0,1);
        if ( ! $account ) {
            return array();
        }
		$account = current($account);

        $memberMdl = $this->app->model('members');
        $member = $memberMdl->getList('member_id,member_lv_id,point,area,addr,email,mobile,tel,sex,reg_ip,disabled',array('member_id'=>$account['account_id']),0,1);
        if ( ! $member ) {
            return array();
        }
        $member = current($member);
        $member['login_name'] = $account['login_name'];
        // 当member_id不存在时$memberMdl->dump()返回NULL，不要向api框架抛出NULL，强制类型转换得到 array()
        return (array) $member;
    }
}