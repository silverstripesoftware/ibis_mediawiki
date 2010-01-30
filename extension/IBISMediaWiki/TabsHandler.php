<?php
require_once("PageHandler.php");
class TabsHandler extends PageHandler{
	function __construct(&$tabs,$user,$title){
		$this->user = $user;
		$this->t_title = $title;
		$this->title = $title->getText();
		$this->tabs = &$tabs;
		$this->factory = new ArticleFactory();
	}
	function RemoveEditTab(){
		if(isset($this->tabs['edit'])){
			$this->removeTab('edit');
		}
		if(isset($this->tabs['viewsource'])){
			$this->removeTab('viewsource');
		}
	}
		
	function isIBISNode(){
		if (preg_match("/^IBIS\s\d+$/",$this->t_title->getText())){
			return True;
		}
		return False;
	}
	
	function changeTabName($key,$new_name){
		$this->tabs[$key]['text'] = $new_name;
	}
	
	function addNewTab($key,$name,$op)
	{
		$this->tabs[$key] = Array(
			'class' => '',
			'text' => $name,
			'href' => $this->t_title->getLocalURL("action=discussion&op=".$op),
		);
	}
	
	function removeTab($key){
		unset($this->tabs[$key]);
	}
	function addEditDiscussionTabIfApplicable(){
		$this->LoadCurrentPage(False);
		if (($this->ibis['user'] == $this->user->id) or $this->user->isAdminUser){
			$this->addNewTab('edit_discussion','Edit Discussion','edit');
		}
	}
	
}
?>
