<?php

class search_service_segment_cjk implements search_interface_segment 
{
    public $name = '二元分词器';

    private $_pre_filter = array();
    
    private $_token_filter = array();

    private static $_defaultImpl;

    protected $_input = null;

    protected $_encoding = '';

    private $_position;

    private $_bytePosition;

    function __construct() 
    {
        /*
        if (@preg_match('/\pL/u', 'a') != 1) {
            trigger_error('Utf8 analyzer needs PCRE unicode support to be enabled.', E_USER_ERROR);
        }
        */
    }//End Function

    public function set($input, $encodeing=''){
        $this->_input    = $input;
        $this->_encoding = $encodeing;
        $this->reset();
    }

    public function tokenize($input, $encodeing=''){
        $this->set($input, $encodeing);
        $token_list = array();
        while (($next = $this->next()) !== null) {
            $token_list[] = $next;
        }
        return new arrayObject($token_list);
    }

    public function reset(){
        $this->_position     = 0;
        $this->_bytePosition = 0;

        // convert input into UTF-8
        if (strcasecmp($this->_encoding, 'utf8' ) != 0  &&
            strcasecmp($this->_encoding, 'utf-8') != 0 ) {
                $this->_input = iconv($this->_encoding, 'UTF-8', $this->_input);
                $this->_encoding = 'UTF-8';
        }
        
        foreach($this->_pre_filter AS $obj){
            $this->_input = $obj->normalize($this->_input);
        }
    }

    public function next(){
        if ($this->_input === null) {
            return null;
        }

        while ($this->_position < strlen($this->_input)) {
            while ($this->_position < strlen($this->_input) &&
                    $this->_input[$this->_position]==' ' ) {
                $this->_position++;
            }
            $termStartPosition = $this->_position;      
            $temp_char = $this->_input[$this->_position];
            $isCnWord = false;
            if(ord($temp_char)>127){  
                $i = 0;       
                while ($this->_position < strlen($this->_input) &&
                ord( $this->_input[$this->_position] )>127) {
                    $this->_position = $this->_position + 3;
                    $i ++;
                    if($i==2){
                        $isCnWord = true;
                        break;
                    }
                }
                if($i==1)continue;
            }else{
                while ($this->_position < strlen($this->_input) &&
                ctype_alnum( $this->_input[$this->_position] )) {
                    $this->_position++;
                }
            }
            if ($this->_position == $termStartPosition) {
                return null;
            }

            $curInput = substr($this->_input, $termStartPosition, $this->_position - $termStartPosition);
            foreach($this->_token_filter AS $obj){
                foreach($this->_pre_filter AS $obj){
                    $curInputt = $obj->normalize($curInput);
                }
            }

            $token = array(
                'text' => $curInput,
                'offset' => $termStartPosition,
                'len' => $this->_position
            );

            if($isCnWord)$this->_position = $this->_position - 3;
            if ($curInputt !== null) {
                return new arrayObject($token);
            }
        }
        return null;
    }

    public function pre_filter(search_interface_filter $obj){
        $this->_pre_filter[] = $obj;
    }

    public function token_filter(search_interface_filter $obj){
        $this->_token_filter[] = $obj;
    }

}//End Class