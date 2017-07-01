<?php

/**
 *
 * Biscolab Laravel Gestpay - GestpayResponse Class
 * web : robertobelotti.com, github.com/biscolab
 *
 * @package Biscolab\Gestpay
 * @author author: Roberto Belotti - info@robertobelotti.com
 * @license MIT License @ https://github.com/biscolab/laravel-gestpay/blob/master/LICENSE
 */

namespace Biscolab\Gestpay;

class GestpayResponse {

	/**
	 * The transaction_result
	 * boolean true | false
	 */
	protected $transaction_result	= false;

	/**
	 * The shop_transaction_id
	 * Transaction ID
	 */	
	protected $shop_transaction_id	= '';

	/**
	 * The error_code
	 * Error code
	 */		
	protected $error_code	= '';

	/**
	 * The error_description
	 * Error description
	 */		
	protected $error_description	= '';

	/**
	 * see http://api.gestpay.it/#introduction
	 *
	 * @param $transaction_result boolean The transaction_result
	 * @param $shop_transaction_id string The shop_transaction_id
	 * @param $error_code string The error_code
	 * @param $error_description string The error_description
	 */
	public function __construct($transaction_result, $shop_transaction_id, $error_code, $error_description)
	{
		$this->transaction_result	= $transaction_result;
		$this->shop_transaction_id	= $shop_transaction_id;
		$this->error_code			= $error_code;
		$this->error_description	= $error_description;
	}
	
	/**
	 * Build and Encrypt XML string in order to Perform payment
	 *
	 * @param $amount the amount payable
	 * @param $shopTransactionId the transaction ID
	 * @param $languageId the language ID (for future use), default = 1 (italian) - see http://api.gestpay.it/#language-codes
	 *
	 * @return boolean | redirect on payment page
	 */
    public function pay($amount, $shopTransactionId, $languageId = 1)
    {

        $res = $this->Encrypt(['amount' => '20', 'shopTransactionId' => $shopTransactionId]);

        if ( false !== strpos($res, '<TransactionResult>OK</TransactionResult>') && preg_match('/<CryptDecryptString>([^<]+)<\/CryptDecryptString>/', $res, $match) ) {
        	$payment_page_url = ($this->test)? $this->payment_page_test_url : $this->payment_page_prod_url;
            header('Location: '.$payment_page_url.'?a=' . $this->shopLogin . '&b=' . $match[1]);
            exit;
        }

        return false;
    }

	/**
	 * http://api.gestpay.it/#encrypt
	 *
	 * @param array $data
	 *
	 * @return string Encrypted XML string
	 */
    public function Encrypt($data = [])
    {
    	$xml_data = '';

    	if(!isset($data['amount'])) {throw new Exception('Manca importo');}
    	if(!isset($data['shopTransactionId'])) {throw new Exception('Manca transazione');}

    	$data = array_merge(['shopLogin' => $this->shopLogin, 'uicCode' => $this->uicCode], $data);
    	foreach ($data as $key => $value) {
    		$xml_data.= '<'.$key.'>'.$value.'</'.$key.'>';
    	}

        $xml = file_get_contents( dirname(__FILE__) . '/../xml/encrypt.xml');

        $xml = str_replace('{request}', $xml_data, $xml);

        return $this->call($xml, 'Encrypt');
    }

	/**
	 * Decrypt SOAP response 
	 * http://api.gestpay.it/#decrypt
	 *
	 * @param string $CryptedString The SOAP response crypted string
	 *
	 * @return string XML SOAP API call
	 */
    function Decrypt($CryptedString)
    {
        $xml_data = '';
    	$data = ['shopLogin' => $this->shopLogin, 'CryptedString' => $CryptedString];
    	foreach ($data as $key => $value) {
    		$xml_data.= '<'.$key.'>'.$value.'</'.$key.'>';
    	}
    	$xml = file_get_contents( dirname(__FILE__) . '/../xml/decrypt.xml');
        $xml = str_replace('{request}', $xml_data, $xml);

        $res = $this->call($xml, 'Decrypt');

        return $res;
    }

	/**
	 * Decrypt SOAP response in order to checks whether the payment has been successful
	 *
	 * @return array $result containing 'transaction_result' (boolean true|false) and 'shop_transaction_id'
	 */
    public function checkResponse()
    {

    	$b = request()->input('b');

        $xml_response = $this->Decrypt($b);

        $xml = self::cleanXML($xml_response);

        $response = $xml->Body->DecryptResponse->DecryptResult->GestPayCryptDecrypt;

        $transaction_result 	= (strtolower($response->TransactionResult) == 'ok');
        $shop_transaction_id 	= (string)$response->ShopTransactionID;        
        $error_code 			= (string)$response->ErrorCode;        
        $error_description 		= (string)$response->ErrorDescription;        

        $result = [
        	'transaction_result' 	=> $transaction_result,
			'shop_transaction_id'	=> $shop_transaction_id,
			'error_code' 			=> $error_code,
			'error_description' 	=> $error_description,
        ];

        return $result;
    }

	/**
	 * perform GestPay API call
	 *
	 * @param string $xml The XML string to send
	 * @param string $op The function called - Default 'Encrypt'
	 *
	 * @return string The SOAP response
	 */
    public function call($xml, $op = 'Encrypt')
    {
        $header = array(
            "Content-type: text/xml; charset=utf-8\"",
            "Accept: text/xml",
            "Content-length: ".strlen($xml),
            "SOAPAction: \"https://ecomm.sella.it/".$op."\"",
        );

        $api_url = ($this->test)? $this->api_test_url : $this->api_prod_url;

        $soap = curl_init();
        curl_setopt($soap, CURLOPT_URL, $api_url );
        curl_setopt($soap, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap, CURLOPT_POST,           true );
        curl_setopt($soap, CURLOPT_POSTFIELDS,     $xml);
        curl_setopt($soap, CURLOPT_HTTPHEADER,     $header);

        $xml_res = curl_exec($soap);

        curl_close($soap);

        return $xml_res;
    }

	/**
	 * Clean SOAM XML code 
	 *
	 * @param string $xml_response The XML string to "clean up"
	 *
	 * @return string 
	 */
    public static function cleanXML($xml_response){
        $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xml_response);
        return simplexml_load_string($clean_xml);    	
    }

}