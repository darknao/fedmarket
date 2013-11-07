<?php
/**
 * @version $Id: evecentral.php 2 2010-12-25 20:52:03Z nao $
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

defined('ALE_BASE') or define('ALE_BASE', dirname(__FILE__));

require_once ALE_BASE.DIRECTORY_SEPARATOR.'base.php';

class AleEVECentral extends AleBase {
	
	protected $default = array(
		'host' => 'http://api.eve-central.com/api/',
		'suffix' => '',
		'parserClass' => 'SimpleXMLElement',
		'requestError' => 'throwException',
		'cacheTime' => 300, 
		);
	
	public function __construct(AleInterfaceRequest $request, AleInterfaceCache $cache = null, array $config = array()) {
		parent::__construct($request, $cache, $config);
	}
	
	/**
	 * Extract cached until time
	 *
	 * @param string $content
	 * @return string
	 */
	protected function getCachedUntil($content) {
		$tz = new DateTimeZone('UTC');
		$now = new DateTime(null, $tz);
		$now->modify(sprintf("+%d seconds", $this->config['cacheTime']));
		return $now->format("Y-m-d H:i:s");
	}
	
}
