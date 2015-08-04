<?php


class b2c_apiv_exchanges_request implements b2c_api_rpc_request_interface{

    /**
     * app object
     */
    protected $app;

    /**
     * 构造方法
     * @param object app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function rpc_caller_request(&$sdf, $method=''){
        if( $method == '' )
            return false;

        //取到版本映射关系
        base_kvstore::instance('b2c_apiv')->fetch('apiv.mapper', $apiv_mapper);

        //循环绑定表
        $obj_shop = $this->app->model('shop');
        $obj_shop_filter = array('status' => 'bind');
        $arr_shops = $obj_shop->getList('*',$obj_shop_filter);
        $result = false;
        if( $arr_shops && $apiv_mapper )
        {
            foreach($arr_shops as $arr_shop)
            {
                $node_id = $arr_shop['node_id'];
                $node_type = $arr_shop['node_type'];
                $node_apiv = $arr_shop['node_apiv'];


                //得到 本地api版本号 
                $local_apiv = $apiv_mapper[ $node_type . '_' . $node_apiv ];
                if( !$local_apiv )
                    continue;


                //根据 本地api版本号 + 目标平台 + 动作，判断是否有对应的service
                $apiv_service = kernel::service( 'apiv_' . $local_apiv . '_' . $node_type . '_' . $method );
                if( $apiv_service )
                {
                    if( $apiv_service instanceof b2c_apiv_interface_request )
                    {
                        $request_method = $apiv_service->get_method();
                        $params = $apiv_service->get_params($sdf);
                        $callback = $apiv_service->get_callback();
                        $title = $apiv_service->get_title();
                        $timeout = $apiv_service->get_timeout();
                        $async = $apiv_service->is_async();


                        $params_addon = array(
                            'node_type' => $node_type,
                            'from_api_v' => $local_apiv,
                            'to_node_id' => $node_id,
                            'to_api_v' => $node_apiv,
                            );

 
                        $params = array_merge($params_addon, $params);

  

                        $result = $this->request($request_method, $params, $callback, $title, $timeout, null, $async);
                    }
                }
            }
        }
        return $result;
    }


	/**
	 * 重试发送的请求
	 * @param string method
     * @param array parameters
     * @param array callback array
     * @param string request title
     * @param int shop id
     * @param int time out type
     * @param string rpc id
	 * @return null
	 */
	public function rpc_recaller_request($method, $params, $callback=array(), $title, $shop_id=NULL, $time_out=1, $rpc_id=null, $async=true)
	{
        // 取到rpc表中的数据
        if (!is_null($rpc_id))
        {
            $obj_rpc_poll = app::get('base')->model('rpcpoll');
            $arr_pk = explode('-', $rpc_id);
            $real_rpc_id = $arr_pk[0];
            $rpc_calltime = $arr_pk[1];
            $tmp = $obj_rpc_poll->getList('*', array('id'=>$real_rpc_id,'calltime'=>$rpc_calltime));

            if ($tmp)
            {
                $arr_params = $tmp[0]['params'];
                $shop_node_type = $arr_params['node_type'];
                $shop_node_id = $arr_params['to_node_id'];
            }
        }
        if( !$shop_node_type || !$shop_node_id )
          return false;

        $params = array_merge(array('node_type' => $shop_node_type, 'to_node_id' => $shop_node_id), $params);

        return $this->request($method, $params, $callback, $title, $timeout, null, $async);
    }



    /**
     * 整理和验证请求的数据
     * @param string method
     * @param array parameters
     * @param array callback array
     * @param string request title
     * @param int shop id
     * @param int time out type
     * @param string rpc id
     * @return null
     */
    protected function request($method, $params, $callback=array(), $title, $time_out=1, $rpc_id=null, $async=true)
    {
        if($callback && $callback['class'] && $callback['method']){
            $rpc_callback = array('class' => $callback['class'], 'method' => $callback['method'], 'params' => $callback['params']);
        }else{
            $rpc_callback = array('class' => 'b2c_api_callback_none', 'method' => 'callback');
        }

        $this->write_log($title,$rpc_callback[0],'rpc_request',array($method,$params,$rpc_callback));
        return $this->rpc_request($method,$params,$rpc_callback,$time_out,$rpc_id,$async);
    }


    /**
     * rpc方式发送请求
     * @param string method name
     * @param array parameters array
     * @param array callback
     * @param int timeout type
     * @param string rpc id
     */
    private function rpc_request($method,$params,$callback,$time_out=1,$rpc_id=null,$async=true)
    {
        $node_id = $async ? 1 : 2;
        $callback_class = $callback['class'];
        $callback_method = $callback['method'];
        $callback_params = (isset($callback['params'])&&$callback['params'])?$callback['params']:array();
        $rst = $this->app->matrix($node_id)->set_callback($callback_class,$callback_method,$callback_params)
                    ->set_timeout($time_out)
                    ->call($method,$params,$rpc_id);
        return $rst;
    }


    private function write_log($title='', $class_name='b2c_api_callback_app', $request_method_name='rpc_request', $data=array())
    {
        if ($data)
        {
            /*$log_file = DATA_DIR.'/logs/b2c/request/{date}/api_request.php';
            $logfile = str_replace('{date}', date("Ymd"), $log_file);

            if(!file_exists($logfile))
            {
                if(!is_dir(dirname($logfile)))  utils::mkdir_p(dirname($logfile));

                $fs = fopen($logfile, 'w');
                $str_xml = "<?php exit(0);";
                $str_xml .= "'<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                $str_xml .= "<request>";
                $str_xml .= "</request>';";
                //error_log(print_r($str_xml,true),3,$logfile);
                fwrite($fs, $str_xml);
                fclose($fs);
            }*/

            $arr_data = array(
                'method' => $data[0],
                'params' => $data[1],
                'rpc_callback' => $data[2],
            );

            // 记录api日志
            /*if (filesize($logfile))
            {
                $fs = fopen($logfile, 'a+');
                $str_xml = fread($fs, filesize($logfile));
                $str_xml = substr($str_xml, 0, strlen($str_xml) - 12);
                fclose($fs);
            }
            $fs = fopen($logfile, 'w');*/
            $str_xml .= "<query>";
            $str_xml .= "<method>" . $arr_data['method'] . "</method>";
            $str_xml .= "<params>" . print_r($arr_data['params'], true) . "</params>";
            $str_xml .= "<rpc_callback>";
            foreach ($arr_data['rpc_callback'] as $key=>$value)
            {
                $str_xml .= "<$key>" . $value . "</$key>";
            }
            $str_xml .= "</rpc_callback></query>";
            //$str_xml .= "</request>';";

            /*fwrite($fs, $str_xml);
            fclose($fs);*/
            kernel::log($str_xml);
        }
    }

}