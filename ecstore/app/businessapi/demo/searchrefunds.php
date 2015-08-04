<?
//查询所有退款单

require_once "base.php";
//系统级参数，必填
$post_data['direct']=true;
$post_data['date']=date('Y-m-d H:i:s',time());//当前时间
$post_data['method']="businessapi.searchrefund.search_refund";

//应用级参数
//可选退款单返回字段
$post_data['columns']='refund_id|member_id|account';

//店铺ID，可根据店铺查询店铺下的退款单,可填
$post_data['store_id']=58;

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
