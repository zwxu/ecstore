<?php

 
#退款单
class operatorlog_refunds
{
    /**
     * 
     * 删除前获取提交过来的退款单信息
     * @param unknown_type $params
     */
    public function logDelInfoStart($params) 
    {
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        $this->info=$params;
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }//End Function
    /**
     * 
     * Enter description here ...
     * @param unknown_type $delflag 是否被删除标识
     */
    public function logDelInfoEnd($delflag=false) 
    {
        if($delflag==true){
            $refund_ids = implode($this->info['refund_id'], ',');
            $memo='退款单号('.$refund_ids.')';
            kernel::single('operatorlog_logs')->inlogs($memo, '删除退款单', 'orders');
        }
    }//End Function

}//End Class
