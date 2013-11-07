<?php
/**
 * @version $Id: abstractdb.php 2 2010-12-25 20:52:03Z nao $
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


abstract class AleCacheAbstractDB implements AleInterfaceCache {
	protected $quote = "'";
	protected $nameQuote = '"';
	
	protected $db;
	protected $table;
	protected $host;
	protected $path;
	protected $paramsRaw;
	protected $params;
	protected $maxDataSize;
	
	protected $row;
	
	public function __construct(array $config = array()) {
		$this->table = $this->_($config, 'table', 'alecache');
		$this->maxDataSize = $this->_($config, 'maxDataSize', null);
	}
	
	abstract protected function escape($string);
		
	protected function quote($value) {
		return $this->quote.$this->escape($value).$this->quote;;
	}
	
	protected function quoteName($name) {
		return $this->nameQuote.$name.$this->nameQuote;;
	}
	
	abstract protected function &execute($query);
	
	abstract protected function &fetchRow(&$result);
	
	abstract protected function freeResult(&$result);
	
	protected function _(&$array, $name, $default = null) {
		return isset($array[$name]) ? $array[$name] : $default;
	}
	
	protected function getWhere() {
		$result = '';
		foreach (array('host', 'path', 'params') as $field) {
			if ($result) {
				$result .= ' AND ';
			}
			$result .= sprintf("%s = %s", $this->quoteName($field), $this->quote($this->$field));
		}
		return $result;
		
	}
	
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
		
		$query = sprintf("SELECT * FROM %s WHERE %s", $this->quoteName($this->table), $this->getWhere());
		$result = $this->execute($query);
		$this->row = $this->fetchRow($result);
		$this->freeResult($result);
	}
	
	/**
	 * Store content
	 *
	 * @param string $content
	 * @param string $cachedUntil
	 * @return null
	 */
	public function store($content, $cachedUntil) {
		if ($this->maxDataSize && strlen($content) > $this->maxDataSize) {
			return;
		}
		if ($this->row) {
			$this->row['content'] = $content;
			$this->row['cachedUntil'] = $cachedUntil;
			$cachedUntil = $cachedUntil ? $this->quote($cachedUntil) : 'NULL';
			$query = sprintf('UPDATE %s SET %s = %s, %s = %s WHERE %s', 
				$this->quoteName($this->table), $this->quoteName('content'), $this->quote($content), $this->quoteName('cachedUntil'), $cachedUntil, $this->getWhere());
		} else {
			$this->row = array();
			$this->row['content'] = $content;
			$this->row['cachedUntil'] = $cachedUntil;
			foreach (array('host', 'path', 'params') as $field) {
				$this->row[$field] = $this->$field;
			}
			$fields = array();
			$values = array();
			foreach ($this->row as $field => $value) {
				$fields[] = $this->quoteName($field);
				$values[] = $value ? $this->quote($value): 'NULL';
			}
			$query = sprintf('INSERT INTO %s (%s) VALUES (%s);', 
				$this->quoteName($this->table), implode(', ', $fields), implode(', ', $values));
		}
		$this->execute($query);
	}
	
	/**
	 * Update cachedUntil value of recent call
	 *
	 * @param string $time
	 */
	public function updateCachedUntil($time) {
		if ($this->row) {
			$this->row['cachedUntil'] = $time;
			$cachedUntil = $time ? $this->quote($time) : 'NULL';
			$query = sprintf('UPDATE %s SET %s = %s WHERE %s', 
				$this->quoteName($this->table), $this->quoteName('cachedUntil'), $cachedUntil, $this->getWhere());
			$this->execute($query);
		}
	}
	
	/**
	 * Retrieve content as string
	 *
	 */
	public function retrieve() {
		if ($this->row) {
			return $this->row['content'];
		}
		return null;
	}
	
	/**
	 * Check if target is stored  
	 *
	 * @return int|null
	 */
	public function isCached() {
		if ($this->row == false) {
			return ALE_CACHE_MISSING;	
		}
		
		$tz = new DateTimeZone('UTC');
		$now = new DateTime(null, $tz);
		$cachedUntil = new DateTime($this->row['cachedUntil'], $tz);
		
		if ((int) $cachedUntil->format('U') < (int) $now->format('U')) {
			return ALE_CACHE_EXPIRED;
		}
		
		return ALE_CACHE_CACHED;
	}
	
	/**
	 * Remove old data from cache
	 *
	 * @param bool $all
	 */
	public function purge($all = false) {
		if ($all) {
			$query = sprintf("DELETE FROM %s WHERE %s = %s", $this->table, $this->quoteName('host'), $this->quote($this->host));
		} else {
			$tz = new DateTimeZone('UTC');
			$now = new DateTime(null, $tz);
			$query = sprintf("DELETE FROM %s WHERE %s = %s AND %s < %s", 
				$this->quoteName($this->table), $this->quoteName('host'), $this->quote($this->host), $this->quoteName('cachedUntil'), $this->quote($now->format('')));
		}
		$this->execute($query);
	}
	
}