<?php

/**
 * @brief class of googleplus api
 * @developer Arnia (support@xpressengine.org)
 */

require_once '/src/apiClient.php';
require_once '/src/contrib/apiPlusService.php';

class GoogleplusApi
{	
	const SESSION_NAME = '__GOOGLEPLUS_API__';
	
	/**
	 * Set API URLS
	 */
	
	private function getScopeURL() { return 'https://www.googleapis.com/auth/plus.me'; }
	private function getRedirectURL() { return 'http://www.dan.com/member16/index.php?module=member&act=dispMemberDriverInterface&driver=googleplus&dact=dispCallback'; }
	
	/**
	 * Set private vars
	 */
	private $clientId;
	private $clientSecret;
	private $developerkey;
	private $user_id;
	private $screen_name;

	/**
	 * @brief Constructor
	 * @access public
	 * @param $apiKey
	 * @param $autoSession if TRUE, auto process session
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function __construct($clientId, $clientSecret, $developerKey, $autoSession = TRUE)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->developerkey = $developerKey;
		//set google plus client
		$this->client = new apiClient();
		$this->client->setClientId($this->clientId);
		$this->client->setClientSecret($this->clientSecret);
		$this->client->setRedirectUri($this->getRedirectURL());
		$this->client->setDeveloperKey($this->developerkey);
		$this->client->setScopes(array($this->getScopeURL()));
		$this->plus = new apiPlusService($this->client);
		
		if($autoSession)
		{
			$this->setSession();
		}
	}
	
	/**
	 * @brief do login, set 'location' header
	 * @access public
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function doLogin()
	{		
		$authUrl = $this->client->createAuthUrl();
		Header("Location: $authUrl");
	}
	
	/**
	 * @brief Get Person information
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getPerson()
	{		
		$data = $this->plus->people->get('me');

		try
		{
			$result = self::parsePerson($data);
		}
		catch(Exception $e)
		{
			throw new Exception(sprintf('%s in GoogleplusApi::getPerson', $e->getMessage()));
		}

		return $result;
	}

	/**
	 * @brief set session
	 * @access public
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function setSession()
	{
			$userId = $_SESSION[self::SESSION_NAME]['USER_ID'];
			$screenName = $_SESSION[self::SESSION_NAME]['SCREEN_NAME'];

			self::destroySession();
			$this->setUserKey($userId, $screenName);
	}

	/**
	 * @brief set user key
	 * @access public
	 * @param $userId
	 * @param $userKey
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function setUserKey($userId, $screenName)
	{
		$this->user_id = $userId;
		$this->screen_name = $screenName;
		$_SESSION[self::SESSION_NAME]['USER_ID'] = $userId;
		$_SESSION[self::SESSION_NAME]['SCREEN_NAME'] = $screenName;
	}

	/**
	 * @brief get user key
	 * @access public
	 * @return stdClass
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getUserKey()
	{
		$result->user_id = $this->user_id;
		$result->screen_name = $this->screen_name;
		return $result;
	}

	/**
	 * @brief is logged
	 * @access public
	 * @return boolean
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function isLogged()
	{
		$this->client->authenticate();
  		$_SESSION['GOOGLEPLUS_ACCESS_TOKEN'] = $this->client->getAccessToken();
  		
  		$user = $this->plus->people->get('me');
  		
     	$this->user_id = $user['id'];
		$this->screen_name = $user['displayName'];
		
		$_SESSION[self::SESSION_NAME]['USER_ID'] = $this->user_id;
		$_SESSION[self::SESSION_NAME]['SCREEN_NAME'] = $this->screen_name;

		return ($user['id'] && $user['displayName']);
	}

	/**
	 * @brief parse Person
	 * @access private
	 * @param $oXml Instance of SimpleXMLElement
	 * @return stdClass
	 * @developer Arnia (support@xpressengine.org)
	 */
	private static function parsePerson($data)
	{
		$result = new stdClass();
		$result->id = (string)$data['id'];
		$result->nickname = (string)$data['displayName'];
		$result->face = (string)$data['image']['url'];
		$result->firstname = (string)$data['name']['familyName'];
		$result->lasttname = (string)$data['name']['givenName'];
		$result->gender = (string)$data['gender'];
		$result->googleplusHome = (string)$data['url'];
		
		return $result;
	}

	/**
	 * @brief destory session
	 * @access public
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	public static function destroySession()
	{
		unset($_SESSION[self::SESSION_NAME]);
	}

}

/* End of file GoogleplusApi.php */
/* Location: ./modules/member/drivers/googleplus/GoogleplusApi.php */
