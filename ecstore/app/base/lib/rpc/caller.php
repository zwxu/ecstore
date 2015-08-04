<?php

 
class base_rpc_caller{

    var $timeout = 10;

    function __construct(&$app,$node_id,$version){
        $this->network_id = $node_id;
        $this->app = $app;
		$this->api_request_version = $version;
    }

    private function begin_transaction($method,$params,$rpc_id=null){
        $obj_rpc_poll = app::get('base')->model('rpcpoll');
        if (is_null($rpc_id))
        {    
            $time = time();
            $microtime = utils::microtime();
            $rpc_id = str_replace('.','',strval($microtime));
			//mt_srand($microtime);
			//$randval = mt_rand();
            $randval = uniqid('', true);
			$rpc_id .= strval($randval);
            $rpc_id = md5($rpc_id);
            //$rpc_id = rand(0,$microtime);

            $data = array(
                'id'=>$rpc_id,
                'network'=>$this->network_id,
                'calltime'=>$time,
                'method'=>$method,
                'params'=>$params,
                'type'=>'request',
                'callback'=>$this->callback_class.':'.$this->callback_method,
                'callback_params'=>$this->callback_params,
            );
            $rpc_id = $rpc_id.'-'.$time;

            $obj_rpc_poll->insert($data);
        }
        else
        {
            $arr_pk = explode('-', $rpc_id);
            $rpc_id = $arr_pk[0];
            $rpc_calltime = $arr_pk[1];
            $tmp = $obj_rpc_poll->getList('*', array('id'=>$rpc_id,'calltime'=>$rpc_calltime));
            
            if ($tmp)
            {
                $data = array(
                    'fail_times'=>$tmp[0]['fail_times']+1,
                );
                $fiter = array(
                    'id'=>$rpc_id,
                    'calltime'=>$rpc_calltime,
                );
                
                $obj_rpc_poll->update($data,$fiter);
            }
            $rpc_id = $rpc_id.'-'.$rpc_calltime;
        }
        return $rpc_id;
    }

    private function get_url($node){
        $row = app::get('base')->model('network')->getlist('node_url,node_api', array('node_id'=>$this->network_id));
        if($row){
            if(substr($row[0]['node_url'],-1,1)!='/'){
                $row[0]['node_url'] = $row[0]['node_url'].'/';
            }
            if($row[0]['node_api']{0}=='/'){
                $row[0]['node_api'] = substr($row[0]['node_api'],1);
            }
            $url = $row[0]['node_url'].$row[0]['node_api'];
        }

        return $url;
    }

    public function call($method,$params,$rpc_id=null,$gzip=false){
        if (is_null($rpc_id))
            $rpc_id = $this->begin_transaction($method,$params);
        else
            $rpc_id = $this->begin_transaction($method,$params,$rpc_id);
        
        $obj_rpc_poll = app::get('base')->model('rpcpoll');
        
        $headers = array(
            /*'Connection'=>$this->timeout,*/
            'Connection'=>'Close',
        );
        if($gzip){
            $headers['Content-Encoding'] = 'gzip';
        }

        $query_params = array(
            'app_id'=>'ecos.'.$this->app->app_id,
            'method'=>$method,
            'date'=>date('Y-m-d H:i:s'),
            'callback_url'=>kernel::openapi_url('openapi.rpc_callback','async_result_handler',array('id'=>$rpc_id,'app_id'=>$this->app->app_id)),
            'format'=>'json',
            'certi_id'=>base_certificate::certi_id(),
            'v'=>$this->api_version($method),
            'from_node_id' => base_shopnode::node_id($this->app->app_id),
        );
        
        // rpc_id 分id 和 calltime
        $arr_rpc_key = explode('-', $rpc_id);
        $rpc_id = $arr_rpc_key[0];
        $rpc_calltime = $arr_rpc_key[1];
		$query_params['task'] = $rpc_id;
        $query_params = array_merge((array)$params,$query_params);
		if (!base_shopnode::token($this->app->app_id))
			$query_params['sign'] = base_certificate::gen_sign($query_params);
		else
			$query_params['sign'] = base_shopnode::gen_sign($query_params,$this->app->app_id);

        $url = $this->get_url($this->network_id);
        
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->set_timeout($this->timeout)->post($url,$query_params,$headers);
        
        kernel::log('Response: '.$response);
        if($this->callback_class && method_exists(kernel::single($this->callback_class), 'response_log')){
            $response_log_func = 'response_log';
            $callback_params = $this->callback_params ? array_merge($this->callback_params, array('rpc_key'=>$rpc_id.'-'.$rpc_calltime)) : array('rpc_key'=>$rpc_id.'-'.$rpc_calltime);
            kernel::single($this->callback_class)->$response_log_func($response, $callback_params);
        }

        if($response===HTTP_TIME_OUT){
            $headers = $core_http->responseHeader;
            kernel::log('Request timeout, process-id is '.$headers['process-id']);
            $obj_rpc_poll->update(array('process_id'=>$headers['process-id'])
                ,array('id'=>$rpc_id,'calltime'=>$rpc_calltime,'type'=>'request'));
            $this->status = RPC_RST_RUNNING;
            return false;
        }else{
            $result = json_decode($response);
            if($result){
                $this->error = $response->error;
                switch($result->rsp){
                case 'running':
                    $this->status = RPC_RST_RUNNING;
					// 存入中心给的process-id也就是msg-id
					$obj_rpc_poll->update(array('process_id'=>$result->msg_id),array('id'=>$rpc_id,'type'=>'request','calltime'=>$rpc_calltime));
                    return true;

                case 'succ':
                    //$obj_rpc_poll->delete(array('id'=>$rpc_id,'calltime'=>$rpc_calltime,'type'=>'request','fail_times'=>1));
                    $obj_rpc_poll->delete(array('id'=>$rpc_id,'calltime'=>$rpc_calltime,'type'=>'request','fail_times'=>1));
                    $this->status = RPC_RST_FINISH;
                    $method = $this->callback_method;
					if ($method && $this->callback_class)
						kernel::single($this->callback_class)->$method($result->data);
					$this->rpc_response = $response;
                    return $result->data;

                case 'fail':
                    $this->error = 'Bad response';
                    $this->status = RPC_RST_ERROR;
					$this->rpc_response = $response;
                    return false;
                }
            }else{
                //error 解码失败
            }
        }
    }

    public function set_callback($callback_class,$callback_method,$callback_params=null){
        $this->callback_class = $callback_class;
        $this->callback_method = $callback_method;
        $this->callback_params = $callback_params;
        return $this;
    }

    public function set_timeout($timeout){
        $this->timeout = $timeout;
        return $this;
    }
	
	public function set_api_version($version){
		$this->api_request_version = $version;
	}

    private function api_version($method){ return $this->api_request_version; }

}
