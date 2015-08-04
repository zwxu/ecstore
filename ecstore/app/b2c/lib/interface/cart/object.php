<?php

 
/**
 * 购物车项处理接口
 * $ 2010-04-28 19:44 $
 */
interface b2c_interface_cart_object{
    public function add_object($aData);    // 增加包括追加
    public function update($sIdent,$quantity); // 更新
    public function get($sIdent = null,$rich = false);
    public function getAll($rich = false);
    public function delete($sIdent = null);
    public function deleteAll();
    public function count(&$aData);
}
?>
