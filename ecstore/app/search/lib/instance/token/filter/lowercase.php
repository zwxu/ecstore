<?php

class search_instance_token_filter_lowercase extends search_abstract_analysis_token_filter 
{
    /**
     * Object constructor
     */
    public function __construct()
    {
        if (!function_exists('mb_strtolower')) {
            // mbstring extension is disabled
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Utf8 compatible lower case filter needs mbstring extension to be enabled.');
        }
    }

    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param Zend_Search_Lucene_Analysis_Token $srcToken
     * @return Zend_Search_Lucene_Analysis_Token
     */
    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {
        $newToken = new Zend_Search_Lucene_Analysis_Token(
                                     mb_strtolower($srcToken->getTermText()),
                                     $srcToken->getStartOffset(),
                                     $srcToken->getEndOffset());

        $newToken->setPositionIncrement($srcToken->getPositionIncrement());

        return $newToken;
    }
}//End Class