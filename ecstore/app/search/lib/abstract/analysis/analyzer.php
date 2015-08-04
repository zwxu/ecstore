<?php

require_once('Zend/Search/Lucene/Analysis/Analyzer/Common.php');

abstract class search_abstract_analysis_analyzer extends Zend_Search_Lucene_Analysis_Analyzer_Common
{    
    
    protected  $_preFilters = array();

    protected  $_defaultToken = array();

    function __construct() 
    {

    }//End Function

    public function addPreFilter(search_abstract_analysis_analyzer_filter $filter){
        $this->_preFilters[] = $filter;
    }

    public function handleInput($input) 
    {
        $this->_defaultToken = array();
        foreach ($this->_preFilters as $filter) {
            $input = $filter->normalize($input);
            if(method_exists($filter, 'getDefaultToken')){
                $this->_defaultToken = array_merge($this->_defaultToken, $filter->getDefaultToken());
            }   
            if ($input === null) {
                return null;
            }
        }
        return $input;
    }//End Function

}//End Class