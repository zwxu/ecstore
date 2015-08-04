<?php

class business_finder_balance
{
	var $column_control = '操作';
    var $column_control_width = 100;

 	function column_control($row){
		$refund = app::get('ectools')->model('refunds');
        $refund_data = $refund->dump($row['refund_id'],'*');

        if($refund_data['refund_type'] == '2'){
            if($refund_data['status'] == 'ready'){
                return '<a href="index.php?app=business&ctl=admin_balance&act=balance&refund_id='.$row['refund_id'].'"  >'.app::get('business')->_('结算').'</a>';
            }
        }else{
            if($refund_data['status'] == 'ready'){
                return '<a href="index.php?app=business&ctl=admin_balance&act=balance_refund&refund_id='.$row['refund_id'].'"  >'.app::get('business')->_('结算').'</a>';
            }
        }
    }

   
        
}