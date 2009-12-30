<?php
class UserHandler{
	function __construct($userObj){
		$this->id = $userObj->getId();
		$this->isAdminUser = in_array( 'sysop', $userObj->getGroups() );
	}
}
?>