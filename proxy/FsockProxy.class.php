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
namespace paypal\proxy;
use paypal\http\RequestBuilder;
use paypal\http\RequestException;
use paypal\PayPal;
use paypal\PayPalException;

/**
 * 
 * @author			Johannes "Akkarin" Donath
 * @copyright			© Copyright 2012 Evil-Co <http://www.evil-co.com>
 * @license			All rights reserved.
 * @category			PayPal/
 */
class FsockProxy implements IProxy {
	
	/**
	 * Stores the current serverURI.
	 * @var			string
	 */
	protected $serverURI = '';
	
	/**
	 * (non-PHPdoc)
	 * @see \paypal\proxy\IProxy::init()
	 */
	public function init($serverURI) {
		$this->serverURI = $serverURI;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \paypal\proxy\IProxy::send()
	 */
	public function send($data) {
		// parse server URI
		$uri = parse_url($this->serverURI);
		
		// validation
		if ($uri === false) throw new PayPalException("Malformed server URI supplied.", 400);
		
		// generate http query
		$query = new RequestBuilder();
		$query->setScheme($uri['scheme']);
		$query->setHost($uri['host']);
		$query->setType('POST');
		
		$query->setPath($uri['path']);
		if (!empty($uri['query'])) $query->setQuery($uri['query']);
		
		$query->setUserAgent(PayPal::USER_AGENT);
		$query->addHeader('Content-Type', 'application/x-www-form-urlencoded;Charset=UTF-8');
		$query->addHeader('Content-Length', strlen($data));
		
		$query->setData($data);
		
		// open socket
		$errorNo = $errorString = null;
		$socket = fsockopen(($query->isSSLEnabled() ? 'ssl://' : '').$uri['host'], (isset($uri['port']) ? $uri['port'] : ($query->isSSLEnabled() ? 443 : 80)), $errorNo, $errorString);
		
		// validate socket
		if ($socket === false) throw new PayPalException("Cannot open socket (".$errorNo."): ".errorString);
		fwrite($socket, $query->__toString());
		
		// read return value
		$inHeader = true;
		$rawHeaders = array();
		$buffer = '';
		
		while(!feof($socket)) {
			// append contents
			$line = fgets($socket);
			
			if ($inHeader) {
				if (rtrim($line) == '') {
					$inHeader = false;
					continue;
				}
				
				$rawHeaders[] = $line;
			} else {
				// append data
				$buffer .= $line;
			}
		}
		
		$headers = array();
		foreach($rawHeaders as $header) {
			if (strpos($header, ':') === false) continue;
			$exploded = explode(':', $header, 2);
			$headers[$exploded[0]] = $exploded[1];
		}
		
		if (strpos($rawHeaders[0], '200') === false) throw new PayPalException('Got error code from server.'); // TODO: This is pretty simple.
		
		// shut down socket
		fclose($socket);
		
		// return data
		return $buffer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \paypal\proxy\IProxy::isSupported()
	 */
	public static function isSupported() {
		return (function_exists('fsockopen'));
	}
}
?>