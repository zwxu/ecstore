<?php

 
#退货单
class operatorlog_reship
{
    /**
     * 
     * 删除前获取提交过来的退货单信息
     * @param unknown_type $params
     */
    public function logDelInfoStart($params) 
    {
        $this->info=$params;
    }//End Function
    /**
     * 
     * Enter description here ...
     * @param unknown_type $delflag 是否被删除标识
     */
    public function logDelInfoEnd($delflag=false) 
    {
        if($delflag==true){
            $reship_ids = implode($this->info['reship_id'], ',');
            $memo='退货单号('.$reship_ids.')';
            kernel::single('operatorlog_logs')->inlogs($memo, '删除退货单', 'orders');
        }
    }//End Function

}//End Class
