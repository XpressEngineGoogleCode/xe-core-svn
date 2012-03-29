<?php

/**
 * @brief class of facebook api
 * @developer Arnia (support@xpressengine.org)
 */


class FacebookApi
{	
	const SESSION_NAME = '__FACEBOOK_API__';
	
	/**
	 * Set API URLS
	 */
	
	private function getRedirectURL() { return Context::getDefaultUrl().'index.php?module=member&act=dispMemberDriverInterface&driver=facebook&dact=dispCallback'; }
	private function oauthRequestURL() { return 'http://www.facebook.com/dialog/oauth'; }
	private function accessTokenURL()  { return 'https://graph.facebook.com/oauth/access_token'; }
	private function getMeURL()  { return 'https://graph.facebook.com/me'; }
	
	
	
	/**
	 * Set private vars
	 */
	private $appId;
	private $appSecret;
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
	public function __construct($appId, $appSecret, $autoSession = TRUE)
	{
		$this->appId = $appId;
		$this->appSecret = $appSecret;

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
     	$_SESSION['Facebook_state'] = md5(uniqid(rand(), TRUE)); 
     	$dialog_url = $this->oauthRequestURL()."?client_id=". $this->appId."&redirect_uri=".urlencode($this->getRedirectURL())."&state=".$_SESSION['Facebook_state'];
		Header("Location: $dialog_url");
	}
	
	/**
	 * @brief Get Person information
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getPerson()
	{
		$url = $this->getMeURL()."?access_token=".$_SESSION['FACEBOOK_ACCESS_TOKEN'];
		$user = file_get_contents($url);
		$data = json_decode($user , true);

		try
		{
			$result = self::parsePerson($data);
		}
		catch(Exception $e)
		{
			throw new Exception(sprintf('%s in FacebookApi::getPerson', $e->getMessage()));
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
		if($_REQUEST['state'] == $_SESSION['Facebook_state']) {
			$code = $_REQUEST["code"];
     		$token_url = $this->accessTokenURL()."?client_id=".$this->appId."&redirect_uri=".urlencode($this->getRedirectURL()). "&client_secret=".$this->appSecret."&code=".$code;
     	
     	$response = @file_get_contents($token_url);
     	$accOAuthData = null;
     	parse_str($response, $accOAuthData);
		}
		
		$_SESSION['FACEBOOK_ACCESS_TOKEN'] = $accOAuthData['access_token'];
		$_SESSION['FACEBOOK_EXPIRES'] = $accOAuthData['expires'];

     	$user = json_decode(file_get_contents($this->getMeURL()."?access_token=".$accOAuthData['access_token']));
		
     	$this->user_id = $user->id;
		$this->screen_name = $user->name;
		
		$_SESSION[self::SESSION_NAME]['USER_ID'] = $this->user_id;
		$_SESSION[self::SESSION_NAME]['SCREEN_NAME'] = $this->screen_name;

		return ($user->id && $user->name);
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
		$result->nickname = (string)$data['name'];
		$result->face = "http://graph.facebook.com/".$data['username']."/picture";
		$result->firstname = (string)$data['first_name'];
		$result->lasttname = (string)$data['last_name'];
		$result->location->name = (string)$data['location']['name'];
		$result->location->timezone = (string)$data['timezone'];
		$result->facebookHome = (string)$data['link'];
		
		$url = "https://graph.facebook.com/".$data['username']."/friends?access_token=".$_SESSION['FACEBOOK_ACCESS_TOKEN'];
		$friends = file_get_contents($url);
		$friends_array = json_decode($friends , true);
		
		$result->friendsCount = count($friends_array['data']);
		
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

/* End of file FacebookApi.php */
/* Location: ./modules/member/drivers/facebook/FacebookApi.php */
