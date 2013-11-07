<?php
/**
 * @version $Id: fsock.php 2 2010-12-25 20:52:03Z nao $
 * @license GNU/LGPL, see COPYING and COPYING.LESSER
 * This file is part of Ale - PHP API Library for EVE.
 * 
 * Ale is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Ale is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with Ale.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('ALE_BASE') or die('Restricted access');

require_once ALE_BASE.DIRECTORY_SEPARATOR.'interface'.DIRECTORY_SEPARATOR.'request.php';

require_once ALE_BASE.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'request.php';


class AleRequestFsock implements AleInterfaceRequest  {
	protected $config = array();
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$this->config['timeout'] = isset($config['timeout']) ? (int) $config['timeout'] : 30;
		$this->config['flattenParams'] = isset($config['flattenParams']) ? (bool) $config['flattenParams'] : false;
	}
	
	public function query($url, array $params = array()) {
		$parsed = parse_url($url);
		if (!isset($parsed['port'])) $parsed['port'] = 80;
		if (!isset($parsed['scheme']) || $parsed['scheme'] != 'http') throw new AleExceptionRequest('Unknown request protocol, use http:// only');
		if (!isset($parsed['path'])) $parsed['path'] = '/';
		
		if ($this->config['flattenParams']) {
			//this will allow to add multiple parameters with same key
			$tmp = array();
			foreach ($params as $key => $value) {
				if (is_array($value)) {
					//using iterators is less itense then recursion, at least so they say
					foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($value)) as $val) {
						$tmp[] = rawurlencode($key) . '=' . rawurlencode($val);
					}
				} else {
					$tmp[] = rawurlencode($key) . '=' . rawurlencode($value);
				}
			}
			$poststring = implode('&', $tmp);
		} else {
			$poststring = http_build_query($params, '', '&');
		}
		
		
		$fp = fsockopen($parsed['host'], $parsed['port'], $errno, $errstr, $this->config['timeout']);
		
		if (!$fp) {
			throw new AleExceptionCache($errstr, $errno);
		}
		
		fputs ($fp, "POST " . $parsed['path'] . " HTTP/1.0\r\n");
		fputs ($fp, "Host: " . $parsed['host'] . "\r\n");
		fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
		fputs ($fp, "User-Agent: Ale\r\n");
		fputs ($fp, "Content-Length: " . strlen($poststring) . "\r\n");
		fputs ($fp, "Connection: close\r\n\r\n");
		
		if (strlen($poststring) > 0) {
			fputs ($fp, $poststring."\r\n");
		}
		
		$contents = "";
		while (!feof($fp)) {
			$contents .= fgets($fp);
		}
					
		// close connection
		fclose($fp);
			
		//look for error  (4** and 5** ) response 
		$matches = array();
		if (preg_match('#^HTTP/[0-9]\\.[0-9] +([0-9]+) +(.*)#', $contents, $matches)) {
			if ($matches[1] >= 400) {
				throw new AleExceptionRequest('Server Response Error::'. $matches[2], $matches[1]);
			}
		}
		
		//remove response headers
		$start = strpos($contents, "\r\n\r\n");
		if ($start !== false) {
			$contents = substr($contents, $start + strlen("\r\n\r\n"));	
		}
		
		return $contents;
		
	}
	
	
}