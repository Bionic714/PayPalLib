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
namespace paypal\http;

// includes
require_once(PAYPAL_DIRECTORY.'http/RequestException.class.php');

/**
 * Builds an HTTP 1.1 request.
 * @author			Johannes "Akkarin" Donath
 * @copyright			© Copyright 2012 Evil-Co <http://www.evil-co.com>
 * @license			All rights reserved.
 * @category			PayPal/
 */
class RequestBuilder {
	
	/**
	 * Stores the HTTP header (Version)
	 * @var			string
	 */
	const HTTP_HEADER = 'HTTP/1.1';
	
	/**
	 * Stores the newline to use.
	 * Note: We're using Windows newlines. This should work on all systems.
	 * @var			string
	 */
	const NEWLINE = "\r\n";
	
	/**
	 * Stores the connection type.
	 * @var			string
	 */
	protected $connection = 'Close';
	
	/**
	 * Stores the data to send.
	 * @var			string
	 */
	protected $data = '';
	
	/**
	 * Stores a list of headers for this query.
	 * @var			string[]
	 */
	protected $headers = array();
	
	/**
	 * Stores the host which is used for the query.
	 * @var			string
	 */
	protected $host = null;
	
	/**
	 * Stores the path to query.
	 * @var			string
	 */
	protected $path = '/';
	
	/**
	 * Stores the query.
	 * @var			string
	 */
	protected $query = '';
	
	/**
	 * Stores the scheme used for the query.
	 * @var			string
	 */
	protected $scheme = 'http';
	
	/**
	 * Stores the type of this query (GET, POST, PUT, OPTIONS, etc).
	 * @var			string
	 */
	protected $type = 'GET';
	
	/**
	 * Stores the user agent for this query.
	 * @var			string
	 */
	protected $userAgent = 'HansPeter/1.1';
	
	/**
	 * Adds a header.
	 * @param			string			$name
	 * @param			string			$value
	 * @return			integer
	 */
	public function addHeader($name, $value) {
		$index = count($this->headers);
		$this->headers[] = array($name, $value);
		return $index;
	}
	
	/**
	 * Returns the current connection type.
	 * @return			string
	 */
	public function getConnection() {
		return $this->connection;
	}
	
	/**
	 * Returns the current data string.
	 * @return			string
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * Returns all headers.
	 * @return			string[]
	 */
	public function getHeaders() {
		return $this->headers;
	}
	
	/**
	 * Returns the host.
	 * @return			string
	 */
	public function getHost() {
		return $this->host;
	}
	
	/**
	 * Returns the path.
	 * @return			string
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * Returns the query.
	 * @return			string
	 */
	public function getQuery() {
		return $this->query;
	}
	
	/**
	 * Returns the scheme.
	 * @return			string
	 */
	public function getScheme() {
		return $this->scheme;
	}
	
	/**
	 * Returns the type.
	 * @return			string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Returns the user agent.
	 * @return			string
	 */
	public function getUserAgent() {
		return $this->userAgent;
	}
	
	/**
	 * Indicates whether this request was supposed to be send over SSL or not.
	 * @return			boolean
	 */
	public function isSSLEnabled() {
		return ($this->scheme == 'https');
	}
	
	/**
	 * Sets a new connection type.
	 * @param			string			$connection
	 * @return			void
	 */
	public function setConnection($connection) {
		$this->connection = $connection;
	}
	
	/**
	 * Sets a new data string.
	 * @param			string			$data
	 * @return			void
	 */
	public function setData($data) {
		$this->data = $data;
	}
	
	/**
	 * Sets a new host.
	 * @param			string			$host
	 * @return			void
	 */
	public function setHost($host) {
		$this->host = $host;
	}
	
	/**
	 * Sets a new path.
	 * @param			string			$path
	 * @return			void
	 */
	public function setPath($path) {
		$this->path = $path;
	}
	
	/**
	 * Sets a new query.
	 * @param			string			$query
	 * @return			void
	 */
	public function setQuery($query) {
		$this->query = $query;
	}
	
	/**
	 * Sets a new scheme.
	 * @param			string			$scheme
	 * @return			void
	 */
	public function setScheme($scheme) {
		$this->scheme = $scheme;
	}
	
	/**
	 * Sets a new type.
	 * @param			string			$type
	 * @throws			RequestException
	 */
	public function setType($type) {
		if (!in_array($type, array('GET', 'POST', 'PUT', 'OPTIONS'))) throw new RequestException("Wrong request type supplied: ".$type);
		$this->type = $type;
	}
	
	/**
	 * Sets a new user agent.
	 * @param			string			$userAgent
	 * @return			void
	 */
	public function setUserAgent($userAgent) {
		$this->userAgent = $userAgent;
	}
	
	/**
	 * Removes a header.
	 * @param			integer			$index
	 * @return			void
	 * @throws			RequestException
	 */
	public function removeHeader($index) {
		// validate
		if (!isset($this->headers[$index])) throw new RequestException("Index out of bounds: " + $index);
		
		// remove
		unset($this->headers[$index]);
	}
	
	/**
	 * Generates the whole request as string.
	 * @return			string
	 */
	public function __toString() {
		// validate
		if (empty($this->host)) throw new RequestException("No hostname supplied.");
		
		// build request header
		$request = $this->type.' '.$this->path.(!empty($this->query) ? $this->query : '').static::NEWLINE;
		$request .= static::HTTP_HEADER.static::NEWLINE;
		$request .= 'User-Agent: '.$this->userAgent.static::NEWLINE;
		$request .= 'Accept: */*'.static::NEWLINE;
		$request .= 'Host: '.$this->host.static::NEWLINE;
		
		// add headers
		foreach($this->headers as $header) {
			$request .= $header['name'].': '.$header['value'].static::NEWLINE;
		}
		
		// add connection type
		$request .= 'Connection: '.$this->connection.static::NEWLINE;
		
		// add spacer
		$request .= static::NEWLINE;
		
		// add data
		if (!empty($this->data)) $request .= $this->data;
		
		// got the request ready
		return $request;
	}
}
?>