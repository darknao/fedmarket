<?php
/**
 * @version $Id: factory.php 2 2010-12-25 20:52:03Z nao $
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

if (!defined('ALE_BASE')) {
	define('ALE_BASE', dirname(__FILE__));
}

if (!defined('ALE_CONFIG_DIR')) {
	define('ALE_CONFIG_DIR', ALE_BASE);
}


class AleFactory {
	/**
	 * Instances of Ale classes
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Look for class within Ale directory
	 *
	 * @param string $name
	 * @param string $type
	 * @return string
	 */
	private static function _class($name, $type = '') {
		$class = 'Ale'.ucfirst($type).$name;
		if (class_exists($class)) {
			return $class;
		}
		$path = ALE_BASE.DIRECTORY_SEPARATOR;
		if ($type) {
			$path .= strtolower($type).DIRECTORY_SEPARATOR;
		}
		$path .= strtolower($name).'.php';
		if (!file_exists($path)) {
			throw new LogicException(sprintf('Cannot find class [%s] in file \'%s\'', $class, $path));
		}
		require_once $path;
		if (!class_exists($class)) {
			throw new LogicException(sprintf('Cannot find class [%s] in file \'%s\'', $class, $path));
		}
		return $class;
	}

	/**
	 * Get value from array if exists, or return default
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private static function _default(&$array, $key, $default) {
		return isset($array[$key]) ? $array[$key] : $default;
	}

	/**
	 * Initialise new instance of Ale class
	 *
	 * @param string $name
	 * @param array $config
	 */
	private static function init($name, $config) {
		$_name = strtolower($name);
		$configfile = self::_default($config, 'config', ALE_CONFIG_DIR.DIRECTORY_SEPARATOR.$_name.'.ini');
		if ($configfile !== false) {
			if (file_exists($configfile)) {
				$tmp = parse_ini_file($configfile, true);
			} else {
				throw new LogicException(sprintf("Configuration file '%s' not found", $configfile));
			}
			if ($tmp === false) {
				throw new LogicException(sprintf("Could not parse configuration file '%s'", $configfile));
			}
		} else {
			$tmp = array();
		}

		$mainConfig 	= self::_default($tmp, 'main', array());
		$cacheConfig 	= self::_default($tmp, 'cache', array());
		$requestConfig 	= self::_default($tmp, 'request', array());

		foreach($config as $key => $value) {
			// parse the dot notation
			$split = explode('.', $key, 2);
			// if dot notation was used
			if (count($split) == 2) {
				// check for a class name
				if ($split[0] == 'main' || $split[0] == 'cache' || $split[0] == 'request') {
					// set key to class name
					$key = $split[0];
				} else {
					// default to main
					$key = 'main';
				}
				// assign the single value to an array
				$value = array($split[1] => $value);
			}

			// populate config arrays with data
			if ($key == 'main' && is_array($value)) {
				foreach ($value as $k => $v) {
					$mainConfig[$k] = $v;
				}
			} elseif ($key == 'cache' && is_array($value)) {
				foreach ($value as $k => $v) {
					$cacheConfig[$k] = $v;
				}
			} elseif ($key == 'request' && is_array($value)) {
				foreach ($value as $k => $v) {
					$requestConfig[$k] = $v;
				}
			} else {
				// no class name means main config value
				$mainConfig[$key] = $value;
			}
		}

		$mainName 		= self::_default($mainConfig, 'class', $name);
		$cacheName 		= self::_default($cacheConfig, 'class', 'Dummy');
		$requestName 	= self::_default($requestConfig, 'class', 'Curl');

		$mainClass 		= self::_class($mainName);
		$cacheClass 	= self::_class($cacheName, 'cache');
		$requestClass 	= self::_class($requestName, 'request');

		$request 		= new $requestClass($requestConfig);
		$cache 			= new $cacheClass($cacheConfig);
		$main 			= new $mainClass($request, $cache, $mainConfig);

		self::$instances[$_name] = $main;

	}

	/**
	 * Loads configuration file and returns instance of Ale class
	 * If object already exists and no new config is provided,
	 * method returns old instance
	 *
	 * @param string $name file name
	 * @param array $config
	 * @return AleBase AleBase object or its descendant
	 */
	public static function get($name, array $config = array(), $newInstance = false) {
		$_name = strtolower($name);
		if ($newInstance || !isset(self::$instances[$_name])) {
			self::init($name, $config);
		}
		return self::$instances[$_name];
	}

	/**
	 * Loads configuration file and returns instance of AleBase class
	 *
	 * @param string $name
	 * @param array $params
	 * @return AleBase
	 */
	public static function __callStatic($name, $params) {
		if (substr($name, 0, 3) != 'get') {
			throw new BadMethodCallException("Method has to have 'get' prefix");
		}
		$name = substr($name, 3);
		$config = self::_default($params, 0, array());
		$newInstance = self::_default($params, 1, false);
		return self::get($name, $config, $newInstance);
	}

	/**
	 * Loads configuration file and returns instance of AleEVEOnline class
	 *
	 * @param array $config
	 * @return AleEVEOnline
	 */
	public static function getEVEOnline(array $config = array(), $newInstance = false) {
		return self::get('EVEOnline', $config, $newInstance);
	}

	/**
	 * Loads configuration file and returns instance of AleEVECentral class
	 *
	 * @param array $config
	 * @return AleEVEOnline
	 */
	public static function getEVECentral(array $config = array(), $newInstance = false) {
		return self::get('EVECentral', $config, $newInstance);
	}

	public static function getEVEMetrics(array $config = array(), $newInstance = false) {
		return self::get('EVEMetrics', $config, $newInstance);
	}
}
