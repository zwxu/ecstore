<?php
class b2c_mdl_message_log extends dbeav_model{
    public function __construct( &$app ) {
        $this->app = $app;
        parent::__construct( $app );
    }

    /*
    * @method : isSpiteRequest
    * @description : 验证是否是恶意请求
    * @params :
    *       $request_time : 请求时间
    *       $target : 目标请求者
    *       $msg : 返回信息
    * @return : string
    * @author : zlj
    * @date : 2013-9-16 14:06:24
    */
    public function isSpiteRequest($request_time, $target, &$msg){
        $msg = '可以请求';
        $todaydate = strtotime(date('Y-m-d',$request_time));//获取当天0点0分0秒的时间戳

        $row = $this->db->select('SELECT method,request_time,target,ip,memo,type,is_pass FROM ' . $this->table_name(1) . ' WHERE target=' . $this->db->quote($target) . ' AND (request_time BETWEEN ' . $todaydate . ' AND ' . $request_time . ') AND is_pass=\'true\' ORDER BY request_time DESC');//获取当天的所有请求数据

        if(!empty($row)){
            $count = count($row);
            if($count >= 5){
                $msg = app::get('b2c')->_('每天最多只能成功请求5次');
                return 'more';
            }else{
                if($request_time - $row[0]['request_time'] <= 120){
                    if($request_time - $row[0]['request_time'] == 0){
                        $msg = app::get('b2c')->_('请勿恶意请求');
                        return 'spite';
                    }else{
                        $msg = app::get('b2c')->_('2分钟之后才能再次获取');
                        return 'wait';
                    }
                }
            }
        }

        return 'ok';//可以请求
    }

    /*
    * @method : saveMessageLog
    * @description : 保存请求日志
    * @params :
    *       $method : 请求方法
    *       $request_time : 请求时间
    *       $target : 目标请求者
    *       $ip : 请求IP
    *       $type : 请求类型
    *       $request_way : 请求方式
    * @return : string
    * @author : zlj
    * @date : 2013-9-16 14:12:05
    */
    public function saveMessageLog($method, $request_time, $target, $ip, $type, $request_way){
        if($type == 'ok'){
            $data['memo'] = app::get('b2c')->_('正常请求');
            $data['is_pass'] = 'true';
        }else{
            if($type == 'more'){
                $data['memo'] = app::get('b2c')->_('超限请求');
            }elseif($type == 'wait'){
                $data['memo'] = app::get('b2c')->_('超频请求');
            }else{
                $data['memo'] = app::get('b2c')->_('恶意请求');
            }
            $data['is_pass'] = 'false';
        }

        $data['method'] = $method;
        $data['request_time'] = $request_time;
        $data['target'] = $target;
        $data['ip'] = $ip;
        $data['type'] = $request_way;

        $result = $this->save($data);
        return $result;
    }
}
