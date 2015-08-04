<?
//查询单个订单订单

require_once "base.php";
//系统级参数必填
$post_data['direct']=true;
$post_data['date']=date('Y-m-d H:i:s',time());//当前时间
$post_data['method']="businessapi.searchorder.search_order";

//应用级参数
//可选返回订单信息字段
$post_data['columns']='tid|created|modified|pay_status|status|promotion_details|order';
//订单ID
$post_data['order_id']='2013120213340637';

//验签
$token="";
$sign = gen_sign($post_data,$token);
$post_data['sign'] = $sign;

$url = 'http://58.214.7.150:8992/src/index.php/api';//接口地址	

//调用接口
$rs=post($post_data,$url);
//打印结果
output($rs);