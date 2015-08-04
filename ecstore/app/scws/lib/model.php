<?php

class scws_model implements search_interface_segment 
{
    public $name = 'scws分词组件';

    private $_pre_filter = array();
    
    private $_token_filter = array();

    private static $_defaultImpl;

    protected $_input = null;

    protected $_encoding = '';

    private $_position;

    private $_bytePosition;

    private $_cws;

    private $_resObj;

    function __construct() 
    {
        $this->_cws = scws_new();
        $this->_cws->set_dict(app::get('scws')->getConf('dict'));
        $this->_cws->set_rule(app::get('scws')->getConf('rule'));
    }//End Function

    public function set($input, $encode=''){
        $this->_input    = $input;
        $this->_encoding = $encode;
        $this->reset();
    }

    public function reset(){
        $this->_position     = 0;
        $this->_bytePosition = 0;

        // convert input into UTF-8
        if (strcasecmp($this->_encoding, 'utf8' ) != 0  && strcasecmp($this->_encoding, 'utf-8') != 0 ){
                $this->_input = iconv($this->_encoding, 'UTF-8', $this->_input);
                $this->_encoding = 'utf-8';
        }

        $this->_cws->set_charset($this->_encoding);
        $input = $this->_input;
        foreach($this->_pre_filter AS $obj){
            $input = $obj->normalize($input);
        }
        $this->_cws->send_text($input);
        $this->_get_result();
    }

    protected function _get_result() 
    {
        $rows = array();
        while($res = $this->_cws->get_result()){
            foreach($res AS $key=>$val){
                $rows[] = $val;
            }
        }
        $obj = new arrayObject($rows);
        $this->_resObj =  $obj->getIterator();
    }//End Function

    public function tokenize($input, $encode=''){
        $this->set($input, $encode);
        $token_list = array();
        while (($next = $this->next()) !== null) {
            $token_list[] = $next;
        }
        return new arrayObject($token_list);
    }

    public function next(){
        if($this->_resObj->valid()){
            $res = $this->_resObj->current();
            $this->_resObj->next();
            $res['text'] = $res['word'];
            foreach($this->_token_filter AS $obj){
                $res['text'] = $obj->normalize($res['text']);
            }
            return $res;
        }else{
            //$this->_cws->close();
            return null;
        }
    }

    public function pre_filter(search_interface_filter $obj){
        $this->_pre_filter[] = $obj;
    }

    public function token_filter(search_interface_filter $obj){
        $this->_token_filter[] = $obj;
    }

    function __destruct() 
    {
        $this->_cws->close();
    }//End Function

}//End Class