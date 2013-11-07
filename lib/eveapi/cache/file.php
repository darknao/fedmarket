<?php
/**
 * @version $Id: file.php 2 2010-12-25 20:52:03Z nao $
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

if (!defined('ALE_CACHE_ROOTDIR')) {
	define('ALE_CACHE_ROOTDIR', './cachedir');
}


class AleCacheFile implements AleInterfaceCache {
	private $host = '';
	private $path = '';
	private $paramsRaw = array();
	private $params = '';
	private $dir = '';
	private $cachedUntil = null;
	private $content = null;
	private $config = array();
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$this->config['rootdir'] = isset($config['rootdir']) ? $config['rootdir'] : ALE_CACHE_ROOTDIR; 
		$this->config['permissions'] = isset($config['permissions']) ? intval($config['permissions']) : 0711; 
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
		$this->paramsRaw = $params;
		$this->params = sha1(http_build_query($params, '', '&'));
		$this->dir = $this->config['rootdir'] . DIRECTORY_SEPARATOR . 
			preg_replace(array('#[^a-zA-Z0-9_\\/]#', '#\\/#'), array('', DIRECTORY_SEPARATOR), $path);
			
		if (!is_dir($this->dir)) {
			if (!mkdir($this->dir, $this->config['permissions'], true)) {
				throw new AleExceptionCache('Failed to create directory: '.$this->dir);
			}
		}
		$filename = $this->dir . DIRECTORY_SEPARATOR . $this->params;
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			if ($content === false) {
				throw new AleExceptionCache('Failed to open file: '.$filename);				
			}
			$chunks = explode("\n", $content, 2);
			$this->cachedUntil = $chunks[0];
			$this->content = $chunks[1]; 
		} else {
			$this->content = null;
			$this->cachedUntil = null;
		}
			
			
	}
	
	/**
	 * Store content
	 *
	 * @param string $content
	 * @param string $cachedUntil
	 * @return null
	 */
	public function store($content, $cachedUntil) {
		$filename = $this->dir . DIRECTORY_SEPARATOR . $this->params;
		$new = !file_exists($filename);
		
		$file = fopen($filename, 'w');
		if ($file === false) {
			throw new AleExceptionCache('Failed to open file: '.$filename);
		}
		$this->cachedUntil = $cachedUntil;
		$this->content = $content;
		fwrite($file, $cachedUntil."\n".$content);
		fclose($file);
		
		if ($new) {
			chmod($filename, $this->config['permissions']);
		}
	}
	
	/**
	 * Update cachedUntil value of recent call
	 *
	 * @param string $time
	 */
	public function updateCachedUntil($time) {
		$filename = $this->dir . DIRECTORY_SEPARATOR . $this->params;
		$file = fopen($filename, 'w');
		if ($file === false) {
			throw new AleExceptionCache('Failed to open file: '.$filename);
		}
		$this->cachedUntil = $time;
		fwrite($file, $this->cachedUntil."\n".$this->content);
		fclose($file);
	}
	
	/**
	 * Retrieve content as string
	 *
	 * @return string
	 */
	public function retrieve() {
		return $this->content;
	}
	
	/**
	 * Check if target is stored  
	 *
	 * @return int|null
	 */
	public function isCached() {
		if (is_null($this->cachedUntil)) {
			return ALE_CACHE_MISSING;
		}
		
		$tz = new DateTimeZone('UTC');
		$now = new DateTime(null, $tz);
		$cachedUntil = new DateTime($this->cachedUntil, $tz);
		
		if ((int) $cachedUntil->format('U') < (int) $now->format('U')) {
			return ALE_CACHE_EXPIRED;
		}
		
		return ALE_CACHE_CACHED;
	}
	
	private function _purge($dir, $all) {
		$dir_handle = opendir($dir);
		while ($fileaname = readdir($dir_handle)) {
			if ($fileaname == '.' || $fileaname == '..') {
				continue;
			}
			$fileaname = $dir.DIRECTORY_SEPARATOR.$fileaname;
			
			//recurseively search directories
			
			if (is_dir($fileaname)) {
				$this->_purge($fileaname, $all);
			} 
			//if not file, do nothing
			if (!is_file($fileaname)) {
				continue;
			}
			if ($all) {
				$del = true;
			} else {
				$content = file_get_contents($fileaname);
				$chunks = explode("\n", $content, 2);
				
				$tz = new DateTimeZone('UTC');
				$now = new DateTime(null, $tz);
				$cachedUntlil = new DateTime($chunks[0], $tz);
				$del = (int) $cachedUntlil->format('U') < (int) $now->format('U');
			}
			if ($del) {
				unlink($fileaname);
			}
		}
		
	}
	
	public function purge($all = false) {
		$this->_purge($this->config['rootdir'], $all);
	}
	
}
