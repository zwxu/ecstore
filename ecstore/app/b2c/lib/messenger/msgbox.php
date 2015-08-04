<?php

 
class b2c_messenger_msgbox{
    
    var $name = '站内消息'; //名称
    var $iconclass="sysiconBtn msgbox"; //操作区图标
    var $name_show = '发消息'; //列表页操作区名称
    var $version='$ver$'; //版本
    var $updateUrl=false;  //新版本检查地址
    var $isHtml = false; //是否html消息
    var $hasTitle = true; //是否有标题
//    var $maxtitlelength =300; //最多字符
    var $maxtime = 300; //发送超时时间 ,单位:秒
    var $maxbodylength =300; //最多字符
    var $allowMultiTarget=false; //是否允许多目标
//  var $targetSplit = ','; //多目标分隔符
    var $dataname='member_id';
    var $withoutQueue = true;
    var $sdfpath = 'pam_account/account_id'; //发送对象

    /**
     * send 
     * 必有方法,发送时调用
     *
     * config参数为getOptions取得的所有项的配置结果
     * 
     * @param mixed $to 
     * @param mixed $message 
     * @param mixed $config 
     * @access public
     * @return void
     */
    function __construct(){
    }

    /**
    * sendMsg
    *
    * @param int $from        发送人id
    * @param int $to        收信人id
    * @param string $meessage        信件内容
    * @param mixed  $options        其他参数 具体如下：rel_order:定单id
                                                    is_sec:信件是否保密值为字符窜形式的'true'和'false' 默认为'false'
                                                    from_type:是否来自管理员 1代表是，0代表会员 默认为0
                                                    to_type:是否发给管理员 1代表是，0代表会员 默认为0
                                                    msg_from:发送者的用户名,如果调用者不易取得发送者的用户则不要传该参数就可
                                                    subject: 信件的标题 若为空则默认值为‘无标题’
                                                    folder:'inbox'发送，'outbox'不发送存入草稿箱  默认是发送
    * @access public
    * @return boolean
    */
    //$options = array(msg_from=>username,'rel_order'=>order_id,'is_sec'=>'true','from_type'=>1,'to_type'=>0);
/*     function sendMsg($from,$to,$meessage,$options=false){ */
    
    function send($to,$subject,$message,$config){
        
        $oMember = app::get('b2c')->model('members');
        $sdf = $oMember->dump($to,'*',array(':account@pam'=>array('login_name')));
        $login_name = $sdf['pam_account']['login_name'];
        $obj_memmsg = kernel::single('b2c_message_msg');
        $aData = $params['data'];
        $aData['member_id'] = 1;
        $aData['uname'] =app::get('b2c')->_('管理员');
        $aData['to_uname'] =$login_name;
        $aData['to_id'] = $to;
        $aData['msg_to'] = $login_name;
        $aData['subject'] = $subject; 
        $aData['comment'] = $message;
        $aData['has_sent'] = 'true';
        return $obj_memmsg->send($aData);
    }

    /**
     * ready 
     * 可选方法，准备发送时触发
     * 
     * @param mixed $config 
     * @access public
     * @return void
     */
    function ready($config){

    }

    /**
     * finish 
     * 可选方法，结束发送时触发
     * 
     * @param mixed $config 
     * @access public
     * @return void
     */
    function finish($config){

    }
}
?>
