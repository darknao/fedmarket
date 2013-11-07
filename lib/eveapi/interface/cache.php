<?php
/**
 * @version $Id: cache.php 2 2010-12-25 20:52:03Z nao $
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

define('ALE_CACHE_MISSING', null);
define('ALE_CACHE_EXPIRED', 0);
define('ALE_CACHE_CACHED',  1);
define('ALE_CACHE_FORCED',  2);


interface AleInterfaceCache {
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array());
	
	/**
	 * Set host URL
	 *
	 * @param string $host
	 */
	public function setHost($host);
	
	/**
	 * Set call parameters
	 *
	 * @param string $path
	 * @param array $params
	 */
	public function setCall($path, array $params = array());
	
	/**
	 * Store content
	 *
	 * @param string $content
	 * @param string $cachedUntil
	 * @return null
	 */
	public function store($content, $cachedUntil);
	
	/**
	 * Update cachedUntil value of recent call
	 *
	 * @param string $time
	 */
	public function updateCachedUntil($time);
	
	/**
	 * Retrieve content as string
	 *
	 */
	public function retrieve();
	
	/**
	 * Check if target is stored  
	 *
	 * @return int|null
	 */
	public function isCached();
	
	/**
	 * Remove old data from cache
	 *
	 * @param bool $all
	 */
	public function purge($all = false);
	
}
