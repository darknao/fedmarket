<?php
/**
 * @version $Id: pgsql.php 2 2010-12-25 20:52:03Z nao $
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


class AleCachePgSQL extends AleCacheAbstractDB {
	
	public function __construct(array $config = array()) {
		parent::__construct($config);
		if (isset($config['db']) && is_resource($config['db'])) {
			$this->db = $config['db'];
		} else {
			$config['host'] = $this->_($config, 'host', null);
                        $config['port'] = $this->_($config, 'port', null);
                        $config['database'] = $this->_($config, 'database', null);
			$config['user'] = $this->_($config, 'user', null);
			$config['password'] = $this->_($config, 'password', null);
			$config['new_link'] = (bool) $this->_($config, 'new_link', false);

                        $connection_string = "host='".$config['host']."' ".
                                             "port='".$config['port']."' ".
                                             "dbname='".$config['database']."' ".
                                             "user='".$config['user']."' ".
                                             "password='".$config['password']."' ";
                        
			if ($this->_($config, 'persistent')) {
				$this->db = pg_pconnect($connection_string, $config['new_link']);
			} else {
				$this->db = pg_connect($connection_string, $config['new_link']);
			}

			if ($this->db == false) {
				throw new AleExceptionCache(pg_last_error(), pg_connection_status());
			}
		}
	}
	
	protected function escape($string) {
		return pg_escape_string($string);
	}
	
	protected function &execute($query) {
		$result = pg_query($this->db, $query);
		if ($result === false) {
			throw new AleExceptionCache(pg_last_error($this->db), pg_result_status($result));
		}
		return $result;
	}
	
	protected function &fetchRow(&$result) {
		$row = pg_fetch_assoc($result);
		return $row;
	}
	
	protected function freeResult(&$result) {
		pg_free_result($result);
	}
			
}
