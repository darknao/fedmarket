<?php
/**
 * @version $Id: eveonline.php 11 2011-12-03 16:21:41Z nao $
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

require_once ALE_BASE.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'eveonline.php';

define('ALE_AUTH_DEFAULT', 0);
define('ALE_AUTH_NONE', 1);
define('ALE_AUTH_USER', 2);
define('ALE_AUTH_CHARACTER', 3);
define('ALE_AUTH_AVAILABLE', 4);


class AleEVEOnline extends AleBase {
	
	private $userID;
	private $apiKey;
	private $characterID;
	
	private $xml;
	
	protected $default = array(
		'host' => 'http://api.eve-online.com/',
		'suffix' => '.xml.aspx',
		'parserClass' => 'SimpleXMLElement' ,
		'serverError' => 'throwException',
		'requestError' => 'throwException',
		'cacheUpdateError' => array(103, 115, 116, 117, 119, ), 
		);
	
	public function __construct(AleInterfaceRequest $request, AleInterfaceCache $cache = null, array $config = array()) {
		if (isset($config['cacheUpdateError']) && !is_array($config['cacheUpdateError'])) {
			$tmp = explode(',', $config['cacheUpdateError']);
			$config['cacheUpdateError'] = array();
			foreach ($tmp as $value) {
				if (trim($value)) {
					$config['cacheUpdateError'][] = trim($value);
				}
			}
		}
		parent::__construct($request, $cache, $config);
	}
	
	/**
	 * Extract cached until time
	 *
	 * @param string $content
	 * @return string
	 */
	protected function getCachedUntil($content) {
		if (!isset($this->xml)) {
			$this->xml = new SimpleXMLElement($content);
		}
		return (string) $this->xml->cachedUntil;
	}
	
	/**
	 * Check for server error. Return null, string or object, based on configuration
	 *
	 * @param string $content
	 * @param bool $useCache
	 * @return mixed 
	 */
	protected function handleContent($content, &$useCache = true) {
		if (is_null($content)) {
			return null;
		}
		$errorCode = 0;
		$errorText = '';
		
		//get error code and error mesage first, I'm using xpath because I could pull it from config later
		$this->xml = new SimpleXMLElement($content);
		if ($this->config['serverError'] != 'ignore') {
			$xerrorCode = $this->xml->xpath('/eveapi/error/@code');
			$xerrorText = $this->xml->xpath('/eveapi/error/text()');
			if ($xerrorCode) {
				$errorCode = (int) $xerrorCode[0];
			}
			if ($xerrorText) {
				$errorText = (string) $xerrorText[0];
			}
		}
		
		if (in_array($errorCode, $this->config['cacheUpdateError'])) {
			$this->cache->updateCachedUntil((string) $this->xml->cachedUntil);
		}
		
		//if we found an error
		if ($errorCode || $errorText) {
			//we do not want to cache error, right?
			$useCache = false;
			switch ($this->config['serverError']) {
				case 'returnParsed':
					break;
				case 'returnNull':
					return null;
					break;
				case 'throwException':
				default:
					if (100 <= $errorCode && $errorCode < 200) {
						throw new AleExceptionEVEUserInput($errorText, $errorCode, (string) $this->xml->cachedUntil);
					} elseif (200 <= $errorCode && $errorCode < 300) {
						throw new AleExceptionEVEAuthentication($errorText, $errorCode, (string) $this->xml->cachedUntil);
					} elseif (500 <= $errorCode && $errorCode < 600) {
						throw new AleExceptionEVEServerError($errorText, $errorCode, (string) $this->xml->cachedUntil);
					} else {
						throw new AleExceptionEVEMiscellaneous($errorText, $errorCode, (string) $this->xml->cachedUntil);
					}
			}
		}
		
		$parserClass = $this->config['parserClass'];
		//check if we have result we want
		if (strtolower($parserClass) == strtolower('SimpleXMLElement') && isset($this->xml)) {
			return $this->xml; 
		}
		return parent::handleContent($content, $useCache);
	}
		
	/**
	 * Resolves ALE_AUTH_DEFAULT credentials setting
	 *
	 * @param string $context
	 * @param int $auth Credentials level
	 * @return int
	 */
	protected function getAuth($context, $auth) {
		if ($auth == ALE_AUTH_DEFAULT) {
			switch ($context) {
				case 'eve':
				case 'map':
					$auth = ALE_AUTH_NONE;
					break;
				case 'account':
					$auth = ALE_AUTH_USER;
					break;
				case 'char':
				case 'corp':
					$auth = ALE_AUTH_CHARACTER;
					break;
				default:
					$auth = ALE_AUTH_AVAILABLE;
			}
		}
		return $auth;
	}
	
	/**
	 * Add Credentials to parameters
	 *
	 * @param array $params
	 * @param int $auth Credentials level
	 */
	protected function addCredentials(array &$params, $auth) {
		switch ($auth) {
			case ALE_AUTH_CHARACTER:
				if ($this->characterID) {
					$params['characterID'] = $this->characterID;
				} else {
					throw new LogicException('Api call requires characterID');
				}
			case ALE_AUTH_USER:
				if ($this->userID && $this->apiKey) {
					$params['keyID'] = $this->userID;
					$params['vCode'] = $this->apiKey;
				} else {
					throw new LogicException('Api call requires user credentials');
				}
			case ALE_AUTH_NONE:
				break;
			case ALE_AUTH_AVAILABLE:
				if ($this->userID && $this->apiKey) {
					$params['keyID'] = $this->userID;
					$params['vCode'] = $this->apiKey;
					if ($this->characterID) {
						$params['characterID'] = $this->characterID;
					}
				}
				break;
			default:
				throw new InvalidArgumentException('Unknown credentials level');
		}
	}
	
	public function  _retrieveXml(array $context, array $arguments) {
		$params = isset($arguments[0]) && is_array($arguments[0]) ? $arguments[0] : array();
		$auth = isset($arguments[1]) ? $arguments[1] : ALE_AUTH_DEFAULT;
		
		$auth = $this->getAuth(reset($context), $auth);
		//let's add credentials first, remember kids: ALE_AUTH_DEFAULT is invalid
		$this->addCredentials($params, $auth);
		$arguments[0] = $params;
				
		return parent::_retrieveXml($context, $arguments);
		
	}	
	/**
	 * Set userID 
	 *
	 * @param int $userID
	 */
	public function setUserID($userID)
	{
		// The user ID must be a numeric value.
		if (!is_numeric($userID))
		{
			// ERROR: User ID is not numeric.
			throw new UnexpectedValueException("setUserID: userID must be a numeric value.");
		}
		
		// Validation checks out, set the User ID
		$this->userID = $userID;
	}	
	
	/**
	 * Set apiKey
	 *
	 * @param string $apiKey
	 */
	public function setApiKey($apiKey)
	{
		// The API Key must be a string.
		if (!is_string($apiKey))
		{
			// ERROR: Api Key is not a string!!
			throw new UnexpectedValueException("setApiKey: apiKey must be a string value. It is " . getType($apiKey));
		}
		
		// Validation checks out, set the Api Key
		$this->apiKey = $apiKey;
	}
	
	/**
	 * Set CharacterID
	 *
	 * @param int $characterID
	 */
	public function setCharacterID($characterID = null) {
		// The char ID must be a numeric value.
		if (!empty($characterID) && !is_numeric($characterID))
		{
			// ERROR: User ID is not numeric.
			throw new UnexpectedValueException("setCharacterID: characterID must be a numeric value.");
		}
		
		// Validation checks out, set the User ID, if it's empty, set to null.
		if (!empty($characterID))
			$this->characterID = $characterID;
		else 
			$this->characterID = null;
	}
	
	/**
	 * Set API credentials
	 *
	 * @param int $userID
	 * @param string $apiKey
	 * @param int $characterID
	 */
	public function setCredentials($userID, $apiKey, $characterID = null) {
		$this->setUserID($userID);
		$this->setApiKey($apiKey);
		$this->setCharacterID($characterID);
	}

}
