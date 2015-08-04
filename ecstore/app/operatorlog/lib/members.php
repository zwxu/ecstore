<?php

 
#会员
class operatorlog_members
{
    /**
     * 
     * 删除前获取提交过来的会员信息
     * @param unknown_type $params
     */
    public function logDelInfoStart($params) 
    {
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        $o = app::get('pam')->model('account');
        $rows = $o->getList('login_name',array('account_id'=>$params['member_id'],'account_type'=>'member'));
        $this->info=$rows;
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }//End Function
    /**
     * 
     * Enter description here ...
     * @param unknown_type $delflag 是否被删除标识
     */
    public function logDelInfoEnd($delflag=false) 
    {
        if($delflag==true){
        	$unametmp='';
        	foreach($this->info as $value){
        		$unametmp.= $value['login_name'].',';
        	}
        	$unames=rtrim($unametmp,',');
        	$memo='会员名('.$unames.')';
            kernel::single('operatorlog_logs')->inlogs($memo, '删除会员', 'members');
        }
    }//End Function

    /**
     * 
     * Enter 记录会员邮箱是否被修改
     * @param array $delflag
     */
    public function logMemberModifyInfo($data) 
    {
        $memberLvObj = app::get('b2c')->model('member_lv');
        $uNameObj = app::get('pam')->model('account');

        $uName = $uNameObj->getList('*',array('account_id'=>$data['member_id'],'account_type'=>'member'),$offset=0, $limit=1);
        $member_lv_name = $memberLvObj->getList('*',array('member_lv_id'=>$data['member_lv']['member_group_id']),$offset=0, $limit=1);

        $memo='修改会员名为('.$uName[0]['login_name'].')的会员等级为('.$member_lv_name[0]['name'].')';
        kernel::single('operatorlog_logs')->inlogs($memo, '修改会员信息', 'members');
    }//End Function

}//End Class
