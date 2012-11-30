<?php
/**
 * This file is part of the PayPal Library.
 *
 * The PayPal Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The PayPal Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace paypal;
use paypal\proxy\IProxy;
use paypal\proxy\FsockProxy;

// defines
define('PAYPAL_DIRECTORY', dirname(__FILE__).'/');

// includes
require_once(PAYPAL_DIRECTORY.'PayPalException.class.php');

require_once(PAYPAL_DIRECTORY.'http/RequestBuilder.class.php');
require_once(PAYPAL_DIRECTORY.'http/RequestException.class.php');

require_once(PAYPAL_DIRECTORY.'proxy/IProxy.class.php');
require_once(PAYPAL_DIRECTORY.'proxy/FsockProxy.class.php');

/**
 * Allows easy access to paypal's API.
 * @author			Johannes "Akkarin" Donath
 * @copyright			© Copyright 2012 Evil-Co <http://www.evil-co.com>
 * @license			All rights reserved.
 * @category			PayPal
 */
class PayPal {
	
	/**
	 * Defines the API version to use.
	 * @var			string
	 */
	const API_VERSION = '53.0';
	
	/**
	 * Defines the authorization URI used for production use.
	 * @var			string
	 */
	const DEFAULT_AUTHORIZATION_URI = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=%s';
	
	/**
	 * Defines the authorization URI used for development purposes (i.e. sandbox).
	 * @var			string
	 */
	const DEVELOPMENT_AUTHORIZATION_URI = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=%s';
	
	/**
	 * Defines the server URI used for development purposes (i.e. sandbox)
	 * @var			string
	 */
	const DEVELOPMENT_SERVER_URI = 'https://api-3t.sandbox.paypal.com/nvp';
	
	/**
	 * Stores the useragent which should be used for all proxies.
	 * @var			string
	 */
	const USER_AGENT = 'HTTP.PHP (PayPal Library; +http://www.evil-co.com; +http://www.github.com/Evil-Co/PayPalLib)';
	
	/**
	 * Stores the authorization URI.
	 * @var			string
	 */
	protected $authorizationURI = '';
	
	/**
	 * Stores the pasword.
	 * @var			string
	 */
	protected $password = '';
	
	/**
	 * Stores the proxy used to call the API.
	 * @var			proxy\IProxy
	 */
	protected $proxy = null;
	
	/**
	 * Stores the server URI.
	 * @var			string
	 */
	protected $serverURI = '';
	
	/**
	 * Stores the API signature.
	 * @var			string
	 */
	protected $signature = '';
	
	/**
	 * Stores the username.
	 * @var			string
	 */
	protected $username = '';
	
	/**
	 * Constructs the object.
	 * @param			string			$server
	 * @param			string			$username
	 * @param			string			$password
	 * @param			string			$signature
	 * @param			boolean			$development
	 */
	public function __construct($username, $password, $signature, $development = true, proxy\IProxy $proxy = null) {
		$this->username = $username;
		$this->password = $password;
		$this->signature = $signature;
		
		$this->setDevelopmentMode($development);
		$this->setCallProxy(($proxy === null ? new FsockProxy() : $proxy));
	}
	
	/**
	 * Calls the API.
	 * @param			array			$parameters
	 * @return			string
	 * @throws			PayPalException
	 */
	protected function call($method, $parameters) {
		// get query array
		$connectionParameters = array_merge(array('METHOD' => $method), $this->getDefaultConnectionparameters());
		$queryArray = array_merge($connectionParameters, $parameters);
		return $this->decodeRequest($this->proxy->send(http_build_query($queryArray)));
	}
	
	/**
	 * Returns a decoded version of the returned data.
	 * @param			string			$raw
	 * @return			mixed[]
	 */
	public function decodeRequest($raw) {
		$return = array();
		parse_str($raw, $return);
		return $return;
	}
	
	/**
	 * Returns the default connection parameters (e.g. version, pwd, user & signature).
	 * @return			string[]
	 */
	protected function getDefaultConnectionParameters() {
		return array(
			'VERSION'		=>	static::API_VERSION,
			'PWD'			=>	$this->password,
			'USER'			=>	$this->username,
			'SIGNATURE'		=>	$this->signature
		);
	}
	
	/**
	 * Redirects the user to PayPal.
	 * @param			string			$URI
	 * @return			void
	 */
	protected function redirect($URI) {
		header('HTTP/1.1 302 Found');
		header('Location: '.$URI);
		exit; // Stop PHP here
	}
	
	/**
	 * Sets a call proxy which is used to query the API.
	 * @param			proxy\IProxy			$instance
	 * @return			void
	 */
	public function setCallProxy(proxy\IProxy $instance) {
		$this->proxy = $instance;
		$this->proxy->init($this->serverURI);
	}
	
	/**
	 * Sets the development mode on or off.
	 * @param			boolean			$mode
	 * @return			void
	 */
	public function setDevelopmentMode($mode) {
		$this->serverURI = ($mode ? static::DEVELOPMENT_SERVER_URI : static::DEFAULT_SERVER_URI);
	}
	
	/**
	 * Starts the payment
	 * @param			float			$amount
	 * @param			string			$currency			This must be a currency code (e.g. USD).
	 * @param			string			$returnURI			The URI where the user will land if the transaction was successfull.
	 * @param			string			$cancelURI			The URI where the user will land if he cancels the whole thing.
	 * @param			string			$type				The payment type (leave this empty if you don't know what it does).
	 * @return			void
	 * @throws			PayPalException
	 */
	public function startPayment($amount, $currency, $returnURI, $cancelURI, $type) {
		// build request array
		$parameters = array(
				'AMT'			=>	$amount,
				'PAYMENTACTION'		=>	$type,
				'RETURNURL'		=>	$returnURI,
				'CANCELURL'		=>	$cancelURI,
				'CURRENCYCODE'		=>	$currency
		);
		$response = $this->call('SetExpressCheckout', $parameters);
	
		// validate
		if (strtoupper($response['ACK']) != 'SUCCESS') throw new PayPalException('Cannot start payment: Got wrong status code from API.');
	
		// redirect
		$this->redirect($this->authorizationURI.urldecode($response['TOKEN']));
	}
	
	/**
	 * Validates the payment
	 * @return			boolean
	 * @throws			PayPalException
	 */
	public function validatePayment() {
		// validate request
		if (!isset($_REQUEST['token']) or empty($_REQUEST['token'])) throw new PayPalException('No payment information found in request.');
		
		// extract token
		$token = urlencode($_REQUEST['token']);
		
		// build parameters
		$parameters = array(
			'TOKEN'		=>	$token
		);
		
		// send verification request
		$response = $this->call('GetExpressCheckoutDetails', $parameters);
		
		// validate
		if (strtoupper($response['ACK']) != 'SUCCESS') throw new PayPalException('Got no valid response from PayPal. This seems to be a bad joke ...');
		
		// everything's good
		return true;
	}
}
?>