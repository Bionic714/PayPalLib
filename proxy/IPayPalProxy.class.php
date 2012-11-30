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

/**
 * Allows to access the API server via PHP.
 * @author			Johannes "Akkarin" Donath
 * @copyright			© Copyright 2012 Evil-Co <http://www.evil-co.com>
 * @license			All rights reserved.
 * @category			PayPal/
 */
interface IPayPalProxy {
	
	/**
	 * Inits the proxy.
	 * @param			string			$serverURI
	 * @return			void
	 */
	public function init($serverURI);
	
	/**
	 * Sends the data to PayPal's API.
	 * @param			string			$data
	 * @return			string
	 * @throws			PayPalException
	 */
	public function send($data);
}
?>