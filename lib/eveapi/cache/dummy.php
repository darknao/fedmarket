<?php
/**
 * @version $Id: dummy.php 2 2010-12-25 20:52:03Z nao $
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

require_once ALE_BASE.DIRECTORY_SEPARATOR.'interface'.DIRECTORY_SEPARATOR.'cache.php';

require_once ALE_BASE.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'cache.php';

class AleCacheDummy implements AleInterfaceCache {
	private $host = '';
	private $path = '';
	private $params = array();
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		//pass
	}
	
	/**
	 * Set host URL
	 *
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}
	
	/**
	 * Set call parameters
	 *
	 * @param string $path
	 * @param array $params
	 */
	public function setCall($path, array $params = array()) {
		$this->path = $path;
		$this->params = $params;
	}
	
	/**
	 * Store content
	 *
	 * @param string $content
	 * @param string $cachedUntil
	 * @return null
	 */
	public function store($content, $cachedUntil) {
		//pass
	}
	
	/**
	 * Update cachedUntil value of recent call
	 *
	 * @param string $time
	 */
	public function updateCachedUntil($time) {
		//pass
	}
	
	/**
	 * Retrieve content as string
	 *
	 * @return string
	 */
	public function retrieve() {
		//pass
	}
	
	/**
	 * Check if target is stored  
	 *
	 * @return int|null
	 */
	public function isCached() {
		return ALE_CACHE_MISSING;
	}
	
	public function purge($all = false) {
		//pass
	}
	
}
