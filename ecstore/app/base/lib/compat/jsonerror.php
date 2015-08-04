<?php


if (class_exists('PEAR_Error')) {

    class base_compat_jsonerror extends PEAR_Error
    {
        function base_compat_jsonerror($message = 'unknown error', $code = null,
                                     $mode = null, $options = null, $userinfo = null)
        {
            parent::PEAR_Error($message, $code, $mode, $options, $userinfo);
        }
    }

} else {

    /**
     * @todo Ultimately, this class shall be descended from PEAR_Error
     */
    class base_compat_jsonerror
    {
        function base_compat_jsonerror($message = 'unknown error', $code = null,
                                     $mode = null, $options = null, $userinfo = null)
        {

        }
    }
}