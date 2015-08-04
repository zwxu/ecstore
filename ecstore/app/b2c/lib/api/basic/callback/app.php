<?php



class b2c_api_basic_callback_app implements b2c_api_callback_interface_app
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function callback($result)
    {
        /*$log_file = DATA_DIR.'/logs/b2c/callback/{date}/api_result.php';
        $logfile = str_replace('{date}', date("Ymd"), $log_file);

        if(!file_exists($logfile))
        {
            if(!is_dir(dirname($logfile)))  utils::mkdir_p(dirname($logfile));

            $fs = fopen($logfile, 'w');
            $str_xml .= "<?php exit(0);";
            $str_xml .= "'<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
            $str_xml .= "<response>";
            $str_xml .= "</response>';";

            fwrite($fs, $str_xml);
            fclose($fs);
        }

        // 记录api日志
        if (filesize($logfile))
        {
            $fs = fopen($logfile, 'a+');
            $str_xml = fread($fs, filesize($logfile));
            $str_xml = substr($str_xml, 0, strlen($str_xml) - 13);
            fclose($fs);
        }
        $fs = fopen($logfile, 'w');*/
        $str_xml .= "<query>";
        foreach ($result->response as $key=>$value)
        {
            $str_xml .= "<$key>" . $value . "</$key>";
        }
        $str_xml .= "</query>";
        //$str_xml .= "</response>';";

        /*fwrite($fs, $str_xml);
        fclose($fs);*/
        kernel::log($str_xml);

        // 生成通知信息
        $arr_callback_params = $result->get_callback_params();
        $status = $result->get_status();
        $res_message = $result->get_result();
        $data = $result->get_data();
        include_once(ROOT_DIR.'/app/b2c/lib/api/rpc/request_api_method.php');

        $message = 'msg_id:' . $result->response['msg_id'] . ', ' . $arr_apis[$arr_callback_params['method']] . (($status == 'succ') ? app::get('b2c')->_('成功，') : app::get('b2c')->_('失败，')). (($res_message) ? ($res_message.', ') : '') . app::get('b2c')->_('单号：') . $data['tid'];

        $arr_msg = array(
            'rsp' => $status,
            'res' => $message,
            'data' => $data,
        );

        return $arr_msg;
    }

    public function response_log($response, $params=array())
    {
        if ($response)
        {
            $response = json_decode($response, 1);
            $obj_rpc_poll = app::get('base')->model('rpcpoll');
            $arr_rpc_id = explode('-', $params['rpc_key']);
            $rpc_id = $arr_rpc_id[0];
            $rpc_calltime = $arr_rpc_id[1];
            $filter = array(
                'id'=>$rpc_id,
                'calltime'=>$rpc_calltime,
            );

            $obj_rpc_poll->update(array('result'=>$response), $filter);
        }
    }
}