<?php
/**
 * @version $Id: context.php 2 2010-12-25 20:52:03Z nao $
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


class AleUtilContext {
	
	private $object;
	private $context = array();
	
	/**
	 * Enter description here...
	 *
	 * @param AleBase $object
	 * @param string $context
	 */
	public function __construct($object, $context) {
		$this->object = $object;
		$this->context[] = $context;
	}
	
	/**
	 * Add path segment
	 *
	 * @param string $name
	 * @return AleUtilContext $this
	 */
	public function __get($name) {
		$this->context[] = $name;
		return $this;
	}
	
	/**
	 * Add path segment and retrieve xml
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		$this->context[] = $name;
		return $this->object->_retrieveXml($this->context, $arguments);
	}
	
	/**
	 * Retrieve xml
	 * for PHP 5.3
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$arguments = func_get_args();
		return $this->object->_retrieveXml($this->context, $arguments);
	}

}
