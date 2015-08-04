<?php

 
class b2c_promotion_conditions_goods_allgoods{
    public $tpl_name = "所有商品";
    public $tpl_type = 'html';

    function __construct($app){ $this->app = $app; }

    function getConfig($aData = array()) {
        $___b2c=app::get('b2c')->_('所有商品');
        return <<<EOF
<h4 align="center">$___b2c</h4>
<input type="hidden" name="conditions[type]" value="b2c_sales_goods_aggregator_combine" />
<input type="hidden" name="conditions[aggregator]" value="all" />
<input type="hidden" name="conditions[value]" value="1" />

<input type="hidden" name="conditions[conditions][0][type]" value="b2c_sales_goods_item_goods" />
<input type="hidden" name="conditions[conditions][0][attribute]" value="goods_goods_id" />
<input type="hidden" name="conditions[conditions][0][operator]" value=">=" />
<input type="hidden" name="conditions[conditions][0][value]" value="1" />
EOF;
    }
	
	/**
	 * 校验参数是否正确
	 * @param mixed 需要校验的参数
	 * @param string error message
	 * @return boolean 是否成功
	 */
	public function verify_form($data=array(), &$msg='')
	{
		return true;
	}
}
?>
