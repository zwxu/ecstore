<?php


/**
 * 过滤规则基类(prefilter + postfilter)
 * $ 2010-05-11 14:00 $
 */
class b2c_sales_basic_filter
{
    public $default = null;   // 默认的处理规则,(用于标准模板数据结构里)
    #protected $_aOperator = null;      // 操作符集合
    #protected $_aAttribute = null;
    #protected $_aAggregator = null;

    public function __construct() {
        #$this->_init_operator();
        #$this->_init_attribute();
        #$this->_init_aggregator();
    }

    public function _init_all() {
        $this->_init_operator();
        $this->_init_attribute();
        $this->_init_aggregator();
    }

    public function __get( $var ) {
        switch( $var ) {
            case "_aOperator" : $this->_init_operator();break;
            case "_aAttribute" : $this->_init_attribute();break;
            case "_aAggregator" : $this->_init_aggregator();break;
        }
        return $this->$var;
    }

    // 具体实现由子类实现
    public function getItem(){return array();}

    // 所有的操作符集
    protected function _init_operator() {
        if(is_null($this->_aOperator)) {
            $aResult = kernel::single('b2c_sales_basic_operator')->get_all();
            $this->_aOperator = $aResult;
        }
    }

    // 所有属性项 由子类继承处理 只用aggregator才要使用到
    protected function _init_attribute() {}

    // 所有aggregator 由子类继承处理 只用aggregator才要使用到
    protected function _init_aggregator() {}

    /**
     * 获取操作符集
     *
     * @param array $aFilter
     * @param boolean $bPass
     * @return array
     */
    public function getOperators($aFilter = array(),$bPass = false) {
        // 为空 表示所有
        if(empty($aFilter)) return $this->_aOperator;
        $aResult = array();
        foreach($this->_aOperator as $key=>$row) {
            // 类型在$aFilter中 且
            if($bPass) {
                if(!in_array($row['type'],$aFilter)) {
                    $aResult[$key] = $row;
                }
            } else {
                if(in_array($row['type'],$aFilter)) {
                    $aResult[$key] = $row;
                }
            }
        }
        return $aResult;
    }

    /**
     * 获取指定操作符的详细信息
     *
     * @param string $sOperator
     * @return array
     */
    public function getOperator($sOperator) {
        return $this->_aOperator[$sOperator];
    }

    /**
     * 获取属性集
     *
     * @param array $aFilter
     * @param boolean $bPass
     * @return array
     */
    public function getAttributes($aFilter = array(),$bPass = false) {
        // 为空 表示所有
        if(empty($aFilter)) return $this->_aAttribute;
        $aResult = array();
        foreach($this->_aAttribute as $key=>$row) {
            // 类型在$aFilter中 且
            if($bPass) {
                if(!in_array($row['type'],$aFilter)) {
                    $aResult[$key] = $row;
                }
            } else {
                if(in_array($row['type'],$aFilter)) {
                    $aResult[$key] = $row;
                }
            }
        }
        return $aResult;
    }

    /**
     * 获取指定属性的详细信息
     *
     * @param string $sAttribute
     * @return array
     */
    public function getAttribute($sAttribute) {
        return $this->_aAttribute[$sAttribute];
    }

    /**
     * 获取聚合器集
     *
     * @param array $aFilter
     * @param boolean $bPass
     * @return array
     */
    public function getAggregators($aFilter = array(),$bPass = false) {
        /*
        // 为空 表示所有
        if(empty($aFilter)) return $aFilter;
        /* todo 这个方法目前实现方法不对 2010-05-17 16:53
        $aResult = array();
        foreach($this->_aAggregator as $key=>$row) {
            // 类型在$aFilter中 且
            if($bPass) {
                if(!in_array($row['type'],$aFilter)) {
                    $aResult[$key] = $row;
                }
            } else {
                if(in_array($row['type'],$aFilter)) {
                    $aResult[$key] = $row;
                }
            }
        }*/
        return $this->_aAggregator;
    }

    /**
     * 获取指定聚合器的详细信息
     *
     * @param string $sAggregator
     * @return array
     */
    public function getAggregator($sAggregator) {
        return $this->_aAggregator[$sAggregator];
    }

    /**
     * 将数据和模板合并在标准格式输出
     *
     * @param array $aStandard
     * @param array $aTamplate
     * @param array $aData
     * @return array
     */
    protected function _makeStandardData($aStandard,$aTamplate,$aData) {
        // 模板存在的处理
        if(!empty($aTamplate)) {
            // todo 这里可能还得做一下检查$aTamplate
            if(is_array($aTamplate)) {
                $aStandard = array_merge($aStandard,$aTamplate);
            } else {
                $aStandard['default'] = $aTamplate;
            }
        }

        // 无论是否存在模板 只要有值存在都用值
        if(is_array($aData) && !empty($aData)) {
            //$aData = trim($aData);
            $aStandard['default'] = $aData;
        } elseif(is_string($aData) && isset($aData)) {
            $aData = trim($aData);
            $aStandard['default'] = $aData;
        } elseif(is_int($aData) && isset($aData)){//增加判断是否是整数，因为这里增加过滤时间的判断@wuwei
            $aStandard['default'] = $aData;
        }

        return $aStandard;
    }

    protected function _standard_view($aStandard,$vpath,$level,$position, $table_info=array()){
        if(empty($aStandard['input'])) return false;
        $aStandard['name'] = $vpath;
        return kernel::single('b2c_sales_basic_input_'.$aStandard['input'])->create($aStandard, $table_info);
    }

    /**
     * 生成options选择项
     *
     * @param array | string $aData
     * @return  arraty | null
     */
    public function _makeOptions($aData) {
        if(is_array($aData)) return $aData;

        if(is_string($aData)) {
            $aData = explode(':',$aData);
            switch($aData[0]) {
                case 'table':
                    $aResult = kernel::database()->select($aData[1]);
                    return utils::array_change_key($aResult,'id');
                    break;
                default:
                    break;
            }
        }

        return null;
    }

    /**
     * 从$aData数组里取指定的值
     *
     * @param array $aData
     * @param string $path
     * @return mix
     */
    protected function _getData($aData,$path) {
        //echo $path, "------------------\r\n";
        $path_array = explode('/', $path);
        $_code = '$return = $aData';
        if($path_array){
            foreach($path_array as $s_path){
                $_code .= '[\''.$s_path.'\']';
            }
        }
        $_code = $_code.';';
        eval($_code);
        return $return;
    }

    public function getData($aData,$path) {
        return $this->_getData($aData,$path);
    }

    // 预过滤的处理(prefilter)
    public function filter(){return false;}
    // 订单促销规则的处理(postfilter)
    public function validate(){return false;}
    // postfilter
    public function valiate(){return false;}
    // 模板输出 虚函数
    public function view() {return false;}

/////////////////////////////////////////////////////////////////////////////////////
// 以下是模板要使用到的包含标签
    /**
     * 使用<li></li>标签包含
     *
     * @param string $html
     * @param int $level
     * @param int $position
     * @return string // html
     */
    function wrap_li($html,$level = 0,$position = null){
        return '<li vposition="'.$position.'" style="zoom:1;">'.$html.'</li>';
    }

    /**
     * 使用<span></span>标签包含
     *
     * @param string $html
     * @return string // html
     */
    function wrap_ul($html,$level = 0,$position = null){
        return '<ul>'.$html.'</ul>';
    }

    /**
     * 使用<span></span>标签包含
     *
     * @param string $html
     * @return string // html
     */
    function wrap_span($html){
        return '<span>'.$html.'</span>';
    }

    /**
     * 使用<div></div>标签包含
     *
     * @param string $html
     * @param int $level
     * @param int $position
     * @return string // html
     */
    function wrap_div($html,$level = 0,$position = null){
        return '<div>'.$html.'</div>';
    }

// 以上是模板要使用到的包含标签
/////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////
// 以下是自动配置模板要使用到的方法

    function create_remove(){
        $ui = new base_component_ui($this);
        return "<span style='margin:0 5px;cursor:pointer;float:left' onclick=\"$(this).getParent('li').remove();\">".$ui->img(array('src'=>'bundle/delecate.gif','app'=>'desktop'))."</span>";
    }

    function create_auto($ctl = 'admin_sales_goods',$act = 'conditions') {
        return <<<EOF
<script>
var showConditions = function(o){
   //$(o).getNext().setStyles({'display':'block'});$(o).setStyles({'display':'none'});

   $(o).getParent().getNext('ul').getChildren('li').getFirst('span').setStyles({'display':'block'});
}
var makeConditions = function(o){
       /*get position */
       var position = ($(o).getParent('li').getNext('li') == null)? 0 : (parseInt($(o).getParent('li').getNext('li').get('vposition')) + 1);
       var obj = new Element('li',{'vposition':position}).inject($(o).getParent('li'),'after');
       var data = 'condition='+$(o).value+'&path='+$(o).get('vpath')+'&level='+$(o).get('vlevel')+'&position='+position;
       new Request.HTML({url:'index.php?app=b2c&ctl={$ctl}&act={$act}',data:data,evalScripts:true,update:obj,onComplete:function(res){
           //var obj = new Element('li',{'vposition':position});
           //obj.innerHTML = res;
           //obj.inject($(o).getParent('li'),'after');

           $(o).getParent('span').setStyles({'display':'none'});
           $(o).selectedIndex = 0;
       }}).send();
}
</script>
EOF;
    }

// 以上是自动配置模板要使用到的方法
/////////////////////////////////////////////////////////////////////////////////////
}
?>
