<?php

 
////////////////////////////////////////////////////
// SMTP - PHP SMTP class
//
// Version 1.02
//
// Define an SMTP class that can be used to connect
// and communicate with any SMTP server. It implements
// all the SMTP functions defined in RFC821 except TURN.
//
// Author: Chris Ryan
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * SMTP is rfc 821 compliant and implements all the rfc 821 SMTP
 * commands except TURN which will always return a not implemented
 * error. SMTP also provides some utility methods for sending mail
 * to an SMTP server.
 * @package PHPMailer
 * @author Chris Ryan
 */
class desktop_email_smtp {

	private $_adapter = null;

	public function desktop_email_smtp($app) {
		// $this->_adapter = kernel::single("desktop_email_smtp_curl");
        if(constant("ECAE_MODE")) {
			$this->_adapter = kernel::single("desktop_email_smtp_ecae");
        }else if(function_exists("fsockopen")) {
			$this->_adapter = kernel::single("desktop_email_smtp_fsockopen");
		}else if(function_exists("curl_init")) {
			$this->_adapter = kernel::single("desktop_email_smtp_curl");
		} else {
			die("smtp no support!");
		}
	}

    public function setSubject($subject) {
        $this->_adapter->subject = $subject;
    }

    public function setBody($body) {
        $this->_adapter->body = $body;
    }

    /*************************************************************
     *                    CONNECTION FUNCTIONS                  *
     ***********************************************************/

    /**
     * Connect to the server specified on the port specified.
     * If the port is not specified use the default SMTP_PORT.
     * If tval is specified then a connection will try and be
     * established with the server for that number of seconds.
     * If tval is not specified the default is 30 seconds to
     * try on the connection.
     *
     * SMTP CODE SUCCESS: 220
     * SMTP CODE FAILURE: 421
     * @access public
     * @return bool
     */
    function Connect($host,$port=0,$tval=30) {
		return $this->_adapter->Connect($host,$port,$tval);
    }

    /**
     * Performs SMTP authentication.  Must be run after running the
     * Hello() method.  Returns true if successfully authenticated.
     * @access public
     * @return bool
     */
    function Authenticate($username, $password) {
		return $this->_adapter->Authenticate($username,$password);
    }

    /**
     * Returns true if connected to a server otherwise false
     * @access private
     * @return bool
     */
    function Connected() {
		return $this->_adapter->Connected();
    }

    /**
     * Closes the socket and cleans up the state of the class.
     * It is not considered good to use this function without
     * first trying to use QUIT.
     * @access public
     * @return void
     */
    function Close() {
		return $this->_adapter->Connected();
    }


    /***************************************************************
     *                        SMTP COMMANDS                       *
     *************************************************************/

    /**
     * Issues a data command and sends the msg_data to the server
     * finializing the mail transaction. $msg_data is the message
     * that is to be send with the headers. Each header needs to be
     * on a single line followed by a <CRLF> with the message headers
     * and the message body being seperated by and additional <CRLF>.
     *
     * Implements rfc 821: DATA <CRLF>
     *
     * SMTP CODE INTERMEDIATE: 354
     *     [data]
     *     <CRLF>.<CRLF>
     *     SMTP CODE SUCCESS: 250
     *     SMTP CODE FAILURE: 552,554,451,452
     * SMTP CODE FAILURE: 451,554
     * SMTP CODE ERROR  : 500,501,503,421
     * @access public
     * @return bool
     */
    function Data($msg_data) {
		return $this->_adapter->Data($msg_data);
    }

    /**
     * Expand takes the name and asks the server to list all the
     * people who are members of the _list_. Expand will return
     * back and array of the result or false if an error occurs.
     * Each value in the array returned has the format of:
     *     [ <full-name> <sp> ] <path>
     * The definition of <path> is defined in rfc 821
     *
     * Implements rfc 821: EXPN <SP> <string> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE FAILURE: 550
     * SMTP CODE ERROR  : 500,501,502,504,421
     * @access public
     * @return string array
     */
    function Expand($name) {
		return 	$this->_adapter->Expand($name);
    }

    /**
     * Sends the HELO command to the smtp server.
     * This makes sure that we and the server are in
     * the same known state.
     *
     * Implements from rfc 821: HELO <SP> <domain> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 501, 504, 421
     * @access public
     * @return bool
     */
    function Hello($host="") {
		return $this->_adapter->Hello($host);
    }

    /**
     * Sends a HELO/EHLO command.
     * @access private
     * @return bool
     */
    function SendHello($hello, $host) {
		return $this->_adapter->SendHello($hello,$host);
    }

    /**
     * Gets help information on the keyword specified. If the keyword
     * is not specified then returns generic help, ussually contianing
     * A list of keywords that help is available on. This function
     * returns the results back to the user. It is up to the user to
     * handle the returned data. If an error occurs then false is
     * returned with $this->error set appropiately.
     *
     * Implements rfc 821: HELP [ <SP> <string> ] <CRLF>
     *
     * SMTP CODE SUCCESS: 211,214
     * SMTP CODE ERROR  : 500,501,502,504,421
     * @access public
     * @return string
     */
    function Help($keyword="") {
		return $this->_adapter->Help($keyword);
    }

    /**
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command.
     *
     * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,421
     * @access public
     * @return bool
     */
    function Mail($from) {
		return $this->_adapter->Mail($from);
    }

    /**
     * Sends the command NOOP to the SMTP server.
     *
     * Implements from rfc 821: NOOP <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 421
     * @access public
     * @return bool
     */
    function Noop() {
		return $this->_adapter->Noop();
    }

    /**
     * Sends the quit command to the server and then closes the socket
     * if there is no error or the $close_on_error argument is true.
     *
     * Implements from rfc 821: QUIT <CRLF>
     *
     * SMTP CODE SUCCESS: 221
     * SMTP CODE ERROR  : 500
     * @access public
     * @return bool
     */
    function Quit($close_on_error=true) {
		return $this->_adapter->Quit($close_on_error);
    }

    /**
     * Sends the command RCPT to the SMTP server with the TO: argument of $to.
     * Returns true if the recipient was accepted false if it was rejected.
     *
     * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250,251
     * SMTP CODE FAILURE: 550,551,552,553,450,451,452
     * SMTP CODE ERROR  : 500,501,503,421
     * @access public
     * @return bool
     */
    function Recipient($to) {
		return $this->_adapter->Recipient($to);
    }

    /**
     * Sends the RSET command to abort and transaction that is
     * currently in progress. Returns true if successful false
     * otherwise.
     *
     * Implements rfc 821: RSET <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500,501,504,421
     * @access public
     * @return bool
     */
    function Reset() {
		return $this->_adapter->Reset();
	}

    /**
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in.
     *
     * Implements rfc 821: SEND <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     * @access public
     * @return bool
     */
    function Send($from) {
		return  $this->_adapter->Send($from);
    }

    /**
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in and send them an email.
     *
     * Implements rfc 821: SAML <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     * @access public
     * @return bool
     */
    function SendAndMail($from) {
		return $this->_adapter->SendAndMail($from);
    }

    /**
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more Recipient
     * commands may be called followed by a Data command. This command
     * will send the message to the users terminal if they are logged
     * in or mail it to them if they are not.
     *
     * Implements rfc 821: SOML <SP> FROM:<reverse-path> <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552,451,452
     * SMTP CODE SUCCESS: 500,501,502,421
     * @access public
     * @return bool
     */
    function SendOrMail($from) {
		return  $this->_adapter->SendOrMail($from);
    }

    /**
     * This is an optional command for SMTP that this class does not
     * support. This method is here to make the RFC821 Definition
     * complete for this class and __may__ be implimented in the future
     *
     * Implements from rfc 821: TURN <CRLF>
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE FAILURE: 502
     * SMTP CODE ERROR  : 500, 503
     * @access public
     * @return bool
     */
    function Turn() {
    	return  $this->_adapter->Turn();
	}

    /**
     * Verifies that the name is recognized by the server.
     * Returns false if the name could not be verified otherwise
     * the response from the server is returned.
     *
     * Implements rfc 821: VRFY <SP> <string> <CRLF>
     *
     * SMTP CODE SUCCESS: 250,251
     * SMTP CODE FAILURE: 550,551,553
     * SMTP CODE ERROR  : 500,501,502,421
     * @access public
     * @return int
     */
    function Verify($name) {
		return $this->_adapter->Verify($name);
    } 

	function getError() {
		return $this->_adapter->error;
	}

} // end the class
