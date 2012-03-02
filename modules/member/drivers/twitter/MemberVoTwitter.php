<?php

/**
 * @brief Class of member value object
 * @developer Arnia (support@xpressengine.org)
 */
class MemberVoTwitter extends MemberVO
{
	private $twitterId;
	private $twitterNickName;
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
		$this->memberInfo->twitterId = $this->twitterId = $memberInfo->user_id;
		$this->memberInfo->twitterNickName = $this->twitterNickName = $memberInfo->nick_name;
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
		return $this->getTwitterNickName();
	}

	/**
	 * @brief get user id
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getTwitterId()
	{
		return $this->twitterId;
	}

	/**
	 * @brief get nick name
	 * @access public
	 * @return string
	 * @developer Arnia (support@xpressengine.org)
	 */
	public function getTwitterNickName()
	{
		return $this->twitterNickName;
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
