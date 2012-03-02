<?php

/**
 * @brief Class of member value object
 * @developer Arnia (support@xpressengine.org)
 */
class MemberVoFacebook extends MemberVO
{
	private $facebookId;
	private $facebookNickName;
	private $face;

	/**
	 * @brief set member info
	 * @param $memberInfo
	 * @access public
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function setMemberInfo($memberInfo)
	{
		// make common variable
		$myInfo = array('user_id', 'nick_name', 'face');
		$commonInfo = clone $memberInfo;
		foreach($myInfo as $name)
		{
			unset($commonInfo->{$name});
		}

		parent::setMemberInfo($commonInfo);

		// set member variable
		$this->memberInfo->facebookId = $this->facebookId = $memberInfo->user_id;
		$this->memberInfo->facebookNickName = $this->facebookNickName = $memberInfo->nick_name;
		$this->memberInfo->face = $this->face = $memberInfo->face;
		$this->setExtraVars($memberInfo->extra_vars);
	}

	/**
	 * @brief Set extra variable
	 * @access private
	 * @param $extraVariable
	 * @return void
	 * @developer Arnia (support@xpressengine.org)
	 */
	private function setExtraVars($extraVariable)
	{
		$this->extraVariable = unserialize($extraVariable);
		foreach($this->extraVariable as $name => $variable)
		{
			$this->memberInfo->{$name} = $variable;
		}
	}

	/**
	 * @brief Get display name
	 * @access public
	 * @return String
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getDisplayName()
	{
		return $this->getFacebookNickName();
	}

	/**
	 * @brief get user id
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getFacebookId()
	{
		return $this->facebookId;
	}

	/**
	 * @brief get nick name
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getFacebookNickName()
	{
		return $this->facebookNickName;
	}

	/**
	 * @brief get face image url
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getFace()
	{
		return $this->face;
	}
}
