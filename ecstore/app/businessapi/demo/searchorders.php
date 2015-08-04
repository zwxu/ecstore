<?
//查询所有订单

require_once "base.php";
//系统级参数必填
$post_data['direct']=true;
$post_data['date']=date('Y-m-d H:i:s',time());//当前时间
$post_data['method']="businessapi.searchorder.search_order";

//店铺ID，可查询店铺下的所有订单,可填
$post_data['store_id']=58;

//可选返回订单信息字段
$post_data['columns']='tid|created|modified|pay_status|status|promotion_details|order';

//订单状态，可根据订单状态查询
$post_data['status']='TRADE_ACTIVE';

//开始时间 可根据时间查询
$post_data['start_time']=date('Y-m-d H:i:s',strtotime('2012-1-2'));
//结束时间
$post_data['end_time']=date('Y-m-d H:i:s',strtotime('2014-12-3'));
//分页大小
$post_data['counts']=2;
//页数
$post_data['page']=1;

//验签
$token="";
$sign = gen_sign($post_data,$token);
$post_data['sign'] = $sign;

$url = 'http://58.214.7.150:8992/src/index.php/api';//接口地址	

//调用接口
$rs=post($post_data,$url);
//打印结果
output($rs);