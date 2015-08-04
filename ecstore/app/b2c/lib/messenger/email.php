<?php

 
class b2c_messenger_email{

    var $name = '电子邮件'; //名称
    var $iconclass="sysiconBtn email"; //操作区图标
    var $name_show = '发邮件'; //列表页操作区名称
    var $version='$ver$'; //版本
    var $updateUrl='';  //新版本检查地址
    var $isHtml = true; //是否html消息
    var $hasTitle = true; //是否有标题
    var $maxtime = 300; //发送超时时间 ,单位:秒
    var $maxbodylength =300; //最多字符
    var $allowMultiTarget=false; //是否允许多目标
    var $targetSplit = ',';
    var $dataname='email';
    var $debug = false;
    var $sdfpath = 'contact/email'; // 发送对象
    function ready($config){
        $this->email = kernel::single('desktop_email_email');
        if($config['sendway']=='smtp'){
            if(!$this->email->SmtpConnect($config)) return false;
        }
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
        if($config['sendway']=='smtp'){
            $this->email->SmtpClose();
        }
    }
    


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
    function send($to, $subject, $body, $config){ 
        if ($config['sendway']=="mail"){
            $this->email = kernel::single('desktop_email_email');
        } 
        $this->Sender = $config['usermail'];
        $this->Subject = $this->email->inlineCode($subject);
        $this->email->Sender = $this->Sender;
        $this->email->Subject = $this->Subject;
        
        $header = array(
            'Return-path'=>'<'.$config['usermail'].'>',
            'Date'=>date('r'),
            'From'=>$this->email->inlineCode(app::get('site')->getConf('site.name')).'<'.$config['usermail'].'>',
            #'From'=>'sss',
            'MIME-Version'=>'1.0',
            'Subject'=>$this->Subject,
            'To'=>$to,
            'Content-Type'=>'text/html; charset=UTF-8; format=flowed',
            'Content-Transfer-Encoding'=>'base64'
        );
      
        $body = chunk_split(base64_encode($body));
        $header = $this->email->buildHeader($header);
        $config['sendway']=($config['sendway'])?$config['sendway']:'smtp';
        switch($config['sendway'])
        {
            case "sendmail":
                $result = $this->email->SendmailSend($to,$header, $body);
                break;
            case "mail":
                $result = $this->email->MailSend($to,$header, $body);
                break;
            case "smtp":
                $result = $this->email->SmtpSend($to,$header, $body,$config);
                break;
            default:
               # trigger_error('mailer_not_supported',E_ERROR);
                $result = false;
                break;
        }
        return $result;
    }

    function getOptions(){
        return array(
            'sendway'=>array('label'=>app::get('b2c')->_('发送方式'),'type'=>'radio','options'=>array('mail'=>app::get('b2c')->_("使用本服务器发送"),'smtp'=>app::get('b2c')->_("使用外部SMTP发送")),'value'=>"mail"),
            'usermail'=>array('label'=>app::get('b2c')->_('发信人邮箱'),'type'=>'input','value'=>'yourname@domain.com'),
            'smtpserver'=>array('label'=>app::get('b2c')->_('smtp服务器地址'),'type'=>'input','value'=>'mail.domain.com'),
            'smtpport'=>array('label'=>app::get('b2c')->_('smtp服务器端口'),'type'=>'input','value'=>'25'),
            'smtpuname'=>array('label'=>app::get('b2c')->_('smtp用户名'),'type'=>'input','value'=>''),
            'smtppasswd'=>array('label'=>app::get('b2c')->_('smtp密码'),'type'=>'password','value'=>'')
        );
    }
}
?>
