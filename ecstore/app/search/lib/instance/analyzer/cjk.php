<?php

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class search_instance_analyzer_cjk extends search_abstract_analysis_analyzer
{

    /**
     * Current char position in an UTF-8 stream
     *
     * @var integer
     */
    private $_position;

    /**
     * Current binary position in an UTF-8 stream
     *
     * @var integer
     */
    private $_bytePosition;

    private $_cnStopWords = array();

    /**
     * Object constructor
     *
     * @throws Zend_Search_Lucene_Exception
     */
    public function __construct()
    {
        parent::__construct();
        
        /*
        if (@preg_match('/\pL/u', 'a') != 1) {
            // PCRE unicode support is turned off
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Utf8 analyzer needs PCRE unicode support to be enabled.');
        }
        */
    }
    
    /**
     * Reset token stream
     */
    public function reset()
    {
        $this->_position     = 0;
        $this->_bytePosition = 0;

        // convert input into UTF-8
        if (strcasecmp($this->_encoding, 'utf8' ) != 0  &&
            strcasecmp($this->_encoding, 'utf-8') != 0 ) {
                $this->_input = iconv($this->_encoding, 'UTF-8', $this->_input);
                $this->_encoding = 'UTF-8';
        }
        
        $this->_input = $this->handleInput($this->_input);
        //echo count($this->_defaultToken) . "<br>";
        $arrayObj = new ArrayObject($this->_defaultToken);
        $this->_defTokenObj = $arrayObj->getIterator();
    }
    
    /**
     * Tokenization stream API
     * Get next token
     * Returns null at the end of stream
     *
     * @return Zend_Search_Lucene_Analysis_Token|null
     */
    public function nextToken()
    {
        while($this->_defTokenObj->valid()){
            $tmpToken = $this->_defTokenObj->current();
            $this->_defTokenObj->next();
            return $tmpToken;
        }
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
            $token = new Zend_Search_Lucene_Analysis_Token(
                                      substr($this->_input,
                                             $termStartPosition,
                                             $this->_position - $termStartPosition),
                                      $termStartPosition,
                                      $this->_position);
            $token = $this->normalize($token);
            if($isCnWord)$this->_position = $this->_position - 3;
            if ($token !== null) {
                return $token;
            }
        }
        return null;
    }

}//End Class