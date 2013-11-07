<?php
/**
 * @version $Id: adodb.php 2 2010-12-25 20:52:03Z nao $
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


class AleCacheADOdb extends AleCacheAbstractDB {
	
	public function __construct(array $config = array()) {
		parent::__construct($config);
		if (isset($config['adodb_dir'])) {
			require_once $config['adodb_dir'].DIRECTORY_SEPARATOR.'adodb.inc.php';
		}
		if ($config['adodb_error'] == 'exception') {
			require_once ADODB_DIR.DIRECTORY_SEPARATOR.'adodb-exceptions.inc.php';
		}		
		if (!defined('_ADODB_LAYER')) {
			throw new AleExceptionCache('ADOdb layer not defined');
		}
		if (isset($config['db']) && is_resource($config['db'])) {
			$this->db = $config['db'];
		} else {
			if (!isset($config['dsn'])) {
				throw new AleExceptionCache('ADOdb dsn (Data Source Name) config missing');
			}
			$this->db = ADONewConnection($config['dsn']);
			
			if ($this->db == false) {
				throw new AleExceptionCache('ADODb connection failed');
			}
		}
		$this->nameQuote = $this->db->nameQuote; 
	}
	
	protected function escape($string) {
		return $this->db->escape($string);
	}
	
	protected function quote($value) {
		return $this->db->quote($value);
	}
	
	protected function &execute($query) {
		$result = $this->db->Execute($query);
		if ($result === false) {
			throw new AleExceptionCache($this->db->ErrorMsg(), $this->db->ErrorNo());
		}
		return $result;
	}
	
	protected function &fetchRow(&$result) {
		$row = $result->GetRowAssoc(2);
		return $row;
	}
	
	protected function freeResult(&$result) {
		unset($result);
	}
			
}
