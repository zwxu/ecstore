<?php


class b2c_sales_basic_input_checkbox
{
    public $type = 'checkbox';
    public function create($aData, $table_info=array()) {
        $aData['default'] = (is_null($aData['default']) || (empty($aData['default'])) )? array() : (is_array($aData['default'])? $aData['default'] : explode(',',$aData['default']) ) ;
        // 目前是调试 改成functions 后可以用封装好的js框架接口做 现在使用原生js
        $html = '<script>
        function promotion_check_all (o) {
            var checks = o.parentNode.parentNode.getElementsByTagName("input");
            if(checks == null) return false;
            for(var i = 0; i < checks.length; i++) {
                checks[i].checked = o.checked;
            }
        }
        validatorMap.set(\'checkboxrequired\', ["<{t}>必须选择一项<{/t}>", function(element) {
            var parent =  element.getParent(),chkbox;
            if(element.get(\'name\')) chkbox = parent.getElements(\'input[type=checkbox]\');
            else chkbox = parent.getElements(\'input[type=checkbox]\');
            return chkbox.some(function(chk) {
                return chk.checked == true;
            });
        }]);
        function promotion_check_child(o,isChild){
            if(isChild){
                o.checked = true;
            }
            if(!o.checked) return false;
            var childNode = $(o).getParent("span").getElements("input[pid="+o.get("value")+"]");
            if(!childNode) return false;
            Array.each(childNode, function(item,index){
                promotion_check_child(item,1);
            });
        }
        </script><label><input type="checkbox" onclick="promotion_check_all(this)"/>全选</label>';
        if(is_array($aData['options'])) {
            foreach($aData['options'] as $key => $row) {
                $html .= '<input type="checkbox" vtype="checkboxrequired" '. (isset($row['pid'])&&$row['pid']!=0?' pid='. $row['pid']:'') .' name="'.$aData['name'].'[]" value="'.$key.'" '.(in_array($key,$aData['default'])? 'checked' : ''). (isset($row['pid'])?' onclick="promotion_check_child(this,0)"':'') .'/>'.$row['name'].'';
            }
        }
        return "<span>{$html}</span>";
    }
}
?>
