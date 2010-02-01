<?php
class TabsHandler{
	function __construct(&$tabs){	
		$this->tabs = &$tabs;
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
		if (preg_match("/^IBIS\s\d+$/",$this->title->getText())){
			return True;
		}
		return False;
	}	
	function removeTab($key){
		unset($this->tabs[$key]);
	}
	
}
?>
