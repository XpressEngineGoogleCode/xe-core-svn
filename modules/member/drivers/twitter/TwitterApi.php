<?php

/**
 * @brief class of twitter api
 * @developer Arnia (support@xpressengine.org)
 */

//require_once('OAuth.php');
require_once _XE_PATH_ . 'modules/member/drivers/twitter/OAuth.php';

class TwitterApi
{	
	const SESSION_NAME = '__TWITTER_API__';
	
	/**
	 * Set API URLS
	 */
	private function accessTokenURL()  { return 'https://api.twitter.com/oauth/access_token'; }
	private function authenticateURL() { return 'https://twitter.com/oauth/authenticate'; }
	private function authorizeURL()    { return 'https://twitter.com/oauth/authorize'; }
	private function requestTokenURL() { return 'https://api.twitter.com/oauth/request_token'; }
	
	/**
	 * Set private vars
	 */
	private $consumerKey;
	private $consumerSecret;
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
	public function __construct($consumerKey, $consumerSecret, $autoSession = TRUE)
	{
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;

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
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1(); 
		$test_consumer = new OAuthConsumer($this->consumerKey, $this->consumerSecret); 
		
		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, NULL, "GET", $this->requestTokenURL());  
		$req_req->sign_request($sig_method, $test_consumer, NULL); 
		
		$oc = new OAuthCurl(); 
		$reqData = $oc->fetchData($req_req->to_url()); 
		
		parse_str($reqData['content'], $reqOAuthData); 
                 
		$req_token = new OAuthConsumer($reqOAuthData['oauth_token'], $reqOAuthData['oauth_token_secret'], 1); 
		                                 
		$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $req_token, "GET", $this->authorizeURL()); 
		$acc_req->sign_request($sig_method, $test_consumer, $req_token); 
		         
		$_SESSION['OAUTH_TOKEN'] = $reqOAuthData['oauth_token']; 
		$_SESSION['OAUTH_TOKEN_SECRET'] = $reqOAuthData['oauth_token_secret']; 
		         
		Header("Location: $acc_req"); 
	}
	
	/**
	 * @brief Get Person information
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getPerson()
	{
		$url = 'https://api.twitter.com/1/users/show.json?screen_name='.$this->screen_name.'&include_entities=true';
		$d = file_get_contents($url);
		$data = json_decode($d , true);

		try
		{
			$result = self::parsePerson($data);
		}
		catch(Exception $e)
		{
			throw new Exception(sprintf('%s in TwitterApi::getPerson', $e->getMessage()));
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
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1(); 
		$test_consumer =  new OAuthConsumer($this->consumerKey, $this->consumerSecret); 
		$params = array(); 
		
		$acc_token = new OAuthConsumer($_SESSION['OAUTH_TOKEN'], $_SESSION['OAUTH_TOKEN_SECRET'], 1); 
		
		$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "GET", $this->accessTokenURL()); 
		$acc_req->sign_request($sig_method, $test_consumer, $acc_token); 
		
		$oc = new OAuthCurl(); 
		$reqData = $oc->fetchData("{$acc_req}&oauth_verifier={$_GET['oauth_verifier']}"); 
		         
		parse_str($reqData['content'], $accOAuthData); 
		
		$_SESSION[self::SESSION_NAME]['FINAL_OAUTH_TOKEN'] = $accOAuthData['oauth_token']; 
		$_SESSION[self::SESSION_NAME]['FINAL_OAUTH_TOKEN_SECRET'] = $accOAuthData['oauth_token_secret'];

		$this->user_id = $accOAuthData['user_id'];
		$this->screen_name = $accOAuthData['screen_name'];
		
		$_SESSION[self::SESSION_NAME]['USER_ID'] = $this->user_id;
		$_SESSION[self::SESSION_NAME]['SCREEN_NAME'] = $this->screen_name;

		return ($accOAuthData['user_id'] && $accOAuthData['screen_name']);
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
		$result->nickname = (string)$data['screen_name'];
		$result->face = (string)$data['profile_image_url'];
		$result->description = (string)$data['description'];
		$result->realname = (string)$data['name'];
		$result->location->name = (string)$data['location'];
		$result->location->timezone = (string)$data['time_zone'];
		$result->twitterHome = 'http://twitter.com/'.$data['screen_name'];
		$result->friendsCount = (int)$data['friends_count'];
		$result->pinMeCount = (int)$data['favourites_count'];
		$result->totalPosts = (int)$data['statuses_count'];
		$result->registered = (string)$data['created_at'];
		
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

/* End of file TwitterApi.php */
/* Location: ./modules/member/drivers/twitter/TwitterApi.php */
