<?php
//File:  Sms.Class.php

/**
 * @category   eMarka
 * @package    Sms Class API
 * @copyright  Copyright (c) 2014 eMarka (https://www.iletimerkezi.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Adem ARAS <adem@emarka.com.tr>
* ...
*/
class Sms {
	/* api setting */
	private $_api_user;
	private $_api_pass;
	private $_sender;

	/* sms info */
	private $_recipient;
	private $_message;

	/* result info */
	private $_response;
	private $_report_id;
	private $_status;

    /* debug info */
    public $errors = array();
    public $logs   = array();

	public function __construct($api_user, $api_pass, $sender) {
		$this->_api_user = $api_user;
		$this->_api_pass = $api_pass;
		$this->_sender   = $sender;
	}

	public function setRecipient($recipient) {
		$this->_recipient = $recipient;
	}

    public function getRecipient(){
        return $this->_recipient;
    }

	public function setMessage($message) {
		$this->_message = $message;
	}

    public function getMessage(){
        return $this->_message;
    }

	public function getReportId() {
		return $this->_report_id;
	}

	public function send() {
		$xml = <<<EOS
        <request>
            <authentication>
                <username>{$this->_api_user}</username>
                <password>{$this->_api_pass}</password>
            </authentication>
            <order>
                <sender>{$this->_sender}</sender>
                <sendDateTime></sendDateTime>
                <message>
                    <text><![CDATA[{$this->_message}]]></text>
                    <receipents>
                        <number>{$this->_recipient}</number>
                    </receipents>
                </message>
            </order>
        </request>
EOS;

        $this->_response = $this->_connect($xml,true);
	}

	private function _connect($xml, $send = false) {
        
        if (extension_loaded("curl")) {

            if($send)
                $url = 'http://api.iletimerkezi.com/v1/send-sms';
            else
                $url = 'http://api.iletimerkezi.com/v1/get-report';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);

            $result = curl_exec($ch);

        } else {
            $result = false;
            $this->addLog('Curl extension sunucunuzda yuklu degil.');
            $this->addError('Curl extension sunucunuzda yuklu degil.');
        }

        return $result;
    }

    private function _parseResponse() {
    	$response = simplexml_load_string($this->_response);
 
        if($response->status->code==200){
            $this->_report_id = $response->order->id;
            $this->_status = 1;//gönderiliyor
        } else {
            $this->_report_id = 0;
            $this->_status = 0;//hata
        }
    }

    public function addError($error) {
        $this->errors[] = $error;
    }

    public function addLog($log) {
        $this->logs[] = $log;
    }

    public function getErrors() {
        $res = '<pre><p><ul>';
        foreach($this->errors as $d){
            $res .= "<li>$d</li>";
        }
        $res .= '</ul></p></pre>';
        return $res;
    }

    public function getLogs() {
        $res = '<pre><p><strong>Sms gönderim detayı </strong><ul>';
        foreach($this->logs as $d){
            $res .= "<li>$d</li>";
        }
        $res .= '</ul></p></pre>';
        return $res;
    }
}