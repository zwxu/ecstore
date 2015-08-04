<?php
/**
 * @author chris.zhang
 *
 */
class b2c_widgets_public {

    protected $db       = null;
    protected $app      = null;
    protected $prefix   = null;

    /**
     * Descrip: key为转换后的键值、value为转换前的键值
     * protected $_outData = array(
     *    'outName' => 'localName',
     * );
     */

    /**
     * Descrip: key为转换前的键值、value为转换后的键值
     * protected $_filter = array(
     *    'outName' => 'localName',
     * );
     */

    //返回图片的原图、大图、中图、小图
    protected $_imageData = array(
        'url_original'  => 'url',   //原图
        'url_lager'     => 'l_url', //大图
        'url_middle'    => 'm_url', //中图
        'url_small'     => 's_url', //小图
    );

    function __construct(&$app){
        $this->app = $app;
        $this->db = kernel::database();
        $this->prefix = 'items';
    }

    /**
     * 一维
     * @param array $data
     */
    protected function _getOutData($data, $accord=null) {
        $_data = array();
        if (is_array($data) && $data){
            if (is_array( $accord )){
                foreach ($accord as $_k => $_v){
                    $_data[$_k] = $data[$_v];
                }
            }elseif (is_array($this->_outData)){
                foreach ($this->_outData as $_k => $_v){
                    $_data[$_k] = $data[$_v];
                }
            }
        }
        unset($data, $accord);
        return $_data;
    }

    /**
     * 自动递归过滤
     * @param array $filter
     */
    protected function _getFilter($filter, $accord=null) {
        $_filter = array();
        if (is_array($filter) && $filter){
            if (is_array($accord) && $accord) {
                foreach ($accord as $_k => $_v){
                    if (isset($filter[$_k])) $_filter[$_v] = self::addslashes($filter[$_k]);
                }
            }elseif (is_array($this->_filter) && $this->_filter) {
                foreach ($this->_filter as $_k => $_v){
                    if (isset($filter[$_k])) $_filter[$_v] = self::addslashes($filter[$_k]);
                }
            }
        }
        unset($filter, $accord);
        return $_filter;
    }

    /**
     * 获取图片信息
     * @param unknown_type $image_id
     */
    public function get_image_url($image_id){
        //$imageObj = &app::get('image')->model('image');
        //$image = $imageObj->dump($image_id);
        //如果对应的图片不存在则使用默认图片
        $rs = app::get('image')->model('image')->getList('*',array('image_id'=>$image_id));
        if(!$rs)
        {
            $imageDefault = app::get('image')->getConf('image.set');
            $image_id = $imageDefault['M']['default_image'];
        }

        $strorager = kernel::single("base_storager");
        $image['url']   = $strorager->image_path($image_id);
        $image['l_url'] = $strorager->image_path($image_id,'l');

        $image['m_url'] = $strorager->image_path($image_id,'m');
        $image['s_url'] = $strorager->image_path($image_id,'s');

        return $this->_getOutData($image, $this->_imageData);
    }
    /**
     *  @Descrip: 自动递归过滤$value里的危险字符
     *  @param array $filter
     */
    public static function addslashes($value)
    {
        //史上最经典的递归，一行搞定
        return is_array($value) ? array_map(array('self','addslashes'), $value) : (is_int($value) ? $value : addslashes($value));
    }

    /**
     * 如同PHP自带array_merger_recursive()，但不会重新索引key值
     */
    public static function array_merge_recursive(){
        $arrays = func_get_args();
        $merged = $arrays[0];

        for($i = 0; $i < count($arrays); $i++) {
            if (is_array($arrays[$i])) {
                foreach ($arrays[$i] as $key => $val) {
                    if (is_array($val)) {
                        $merged[$key] = !empty($merged[$key]) && is_array($merged[$key]) ?
                                        self::array_merge_recursive($merged[$key], $arrays[$i][$key]) :
                                        $arrays[$i][$key];
                    } else {
                        $merged[$key] = $val;
                    }
                }
            }
        }
        return $merged;
    }

    public static function get_bool($value){
        return is_bool($value) ? $value : ((is_int($value) && $value === 1) ? true : false);
    }

    public static function get_link($params){
        return app::get('site')->router()->gen_url($params);
    }
}
