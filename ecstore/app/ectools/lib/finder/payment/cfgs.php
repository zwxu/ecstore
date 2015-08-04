<?php


/**
 * 支付方式的finder下拉的操作列
 * 
 * @version 0.1
 * @package ectools.lib.finder
 */
class ectools_finder_payment_cfgs{

	/**
	 * @var string 操作列名称
	 */
    var $column_control = '配置';

    /**
     * 配置列显示的html
     * @param array 该行的数据
     * @return string html
     */
    function column_control($row){
        return '<a target="dialog::{width:0.6,height:0.7,title:\'支付方式配置\'}" href="index.php?app=ectools&ctl=payment_cfgs&act=setting&p[0]='.$row['app_class'].'&finder_id='. $_GET['_finder']['finder_id'] . '">'.app::get('ectools')->_('配置').'</a>';
    }

}
