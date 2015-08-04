<?php



class b2c_api_callback_none implements b2c_api_callback_interface_app
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