<?php

/**
 * @brief Class of member value object
 * @developer Arnia (support@xpressengine.org)
 */
class MemberVoGoogleplus extends MemberVO
{
	private $googleplusId;
	private $googleplusNickName;
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
		$this->memberInfo->googleplusId = $this->googleplusId = $memberInfo->user_id;
		$this->memberInfo->googleplusNickName = $this->googleplusNickName = $memberInfo->nick_name;
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
		return $this->getGoogleplusNickName();
	}

	/**
	 * @brief get user id
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getGoogleplusId()
	{
		return $this->googleplusId;
	}

	/**
	 * @brief get nick name
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getGoogleplusNickName()
	{
		return $this->googleplusNickName;
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
