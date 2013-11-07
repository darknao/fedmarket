<?php
/**
 * @version $Id: joomla.php 2 2010-12-25 20:52:03Z nao $
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

require_once ALE_BASE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'abstractdb.php';


class AleCacheJoomla extends AleCacheAbstractDB {
	
	public function __construct(array $config = array()) {
		parent::__construct($config);
		if (isset($config['db']) && is_resource($config['db'])) {
			$this->db = $config['db'];
		} else {
			$this->db = JFactory::getDBO();
		}
	}
	
	protected function escape($string) {
		return $this->db->getEscaped($string);
	}
	
	protected function quote($value) {
		return $this->db->quote($value);
	}
	
	protected function quoteName($name) {
		return $this->db->nameQuote($name);
	}
	
	protected function &execute($query) {
		$result = $this->db->Execute($query);
		if ($result === false) {
			throw new AleExceptionCache($this->db->getErrorMsg(), $this->db-getErrorNum());
		}
		return $result;
	}
	
	protected function &fetchRow(&$result) {
		return $result;
	}
	
	protected function freeResult(&$result) {
		//joomla does it immediately
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
		
		$query = sprintf("SELECT * FROM %s WHERE %s", $this->table, $this->getWhere());
		$result = $this->db->setQuery($query);
		$this->row = $this->db->loadAssoc();
		$this->freeResult($result);
	}
	
}
