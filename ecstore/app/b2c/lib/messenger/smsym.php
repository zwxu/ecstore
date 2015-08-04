<?php
/**
 * @author ql
 * @version 2013-11-10
 * @package messenger
 * @description 亿美软通短信发送
 */

class b2c_messenger_smsym{
    /**
     * @description 短信发送
     * @access public
     * @param void
     * @return void
     */
    public function send($contents,$config,&$msg) {
        $b2c_app = app::get('b2c');
        //序列号
        $sms_code = array();
        $sms_code['serialNumber'] = $b2c_app->getConf('sms_code_num');
        $sms_code['password'] = $b2c_app->getConf('sms_code_psw');
        $sms_code['sessionKey'] = $b2c_app->getConf('sms_code_key');

        if(empty($sms_code)){
            $msg=app::get('b2c')->_('接口参数配置错误');
            return false;
        }

        if(!$contents){
            $msg=app::get('b2c')->_('手机短信不能为空！');
            return false;
        }

        $phones = $contents[0]['phones'];
        $message = $contents[0]['content'];

        $smsObj = kernel::single('sms_operating',json_encode($sms_code));

        $result = $smsObj->sendSMS($phones,$message);

        $err_msg = array(
                '17'=>'发送信息失败',
                '18'=>'发送定时信息失败',
                '303'=>'客户端网络故障',
                '305'=>'服务器端返回错误，错误的返回值（返回值不是数字字符串）',
                '307'=>'目标电话号码不符合规则，电话号码必须是以0、1开头',
                '997'=>'平台返回找不到超时的短信，该信息是否成功无法确定',
                '998'=>'由于客户端网络问题导致信息发送超时，该信息是否成功下发无法确定'
        );

        if($result == '0') {
            $msg = app::get('b2c')->_('短信发送成功！');
            return true;
        }elseif(isset($err_msg[$result])){
            $msg = $err_msg[$result];
            return false;
        }
        $msg = app::get('b2c')->_('短信发送失败！');
        return false;
    }

    /**
     * @description 获取免登录地址
     * @access public
     * @param void
     * @return void
     */
    public function getSmsBuyUrl() {
        $url = "index.php?app=sms&ctl=admin_sms&act=showUrl";
        return $url;
    }

}
?>
