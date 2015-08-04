<html>
<head>
<meta charset="utf-8">
<title></title>
<style>
 body{padding:0;margin:5px 0;font-size:12px;}
 .link{color:#333333;margin:0 5px;}
 .tb-style{border:1px solid #dddddd;border-bottom:none;font-size:12px;}
 .tb-style td,.tb-style th{padding:3px 5px;border-bottom:1px solid #ddd;}
 .tb-style th{font-weight:normal;border-right:1px solid #dddddd;}
 .bottom-tips{text-align:right;color:#666666;}
</style>
</head>
<?php require_once('post.php');
$result = get_info($_GET);
if($result['status'] === 1)
{
	?>
	<table cellspacing="0" cellpadding="0" border="0" class="tb-style" width="100%">
	<?php foreach((array)$result['data'] as $k=>$v)
	{
	?>

	<tr><th><?php echo $v['time'];?></th><td><?php echo $v['context'];?></td></tr>
	<?php
	}
	?>
	<table>
	<?php
}
else
{
?>
<div class="bottom-tips">
未读到物流信息,请前往物流公司网站查询</div>
<?php
}
?>
<div class="bottom-tips">
Powered by 快递100</div>

<script>
try{
var b = document.body.scrollHeight;
var d = document.documentElement.scrollHeight;
var h = Math.max(b,d);
top.document.getElementById('logistic-infor').height = h;
}catch(e){}
</script>
</html>
