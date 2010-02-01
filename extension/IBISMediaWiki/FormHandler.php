<?php
require_once("YAMLHandler.php");
require_once("PageHandler.php");
class FormHandler extends PageHandler{
	function __construct($user,$ibis){
		$this->user = $user;
		if(gettype($ibis)=='array'){
			$this->ibis = $ibis;
		}
		else{
			$this->ibis = YAMLHandler::YAMLToArray($ibis);
		}
	}
	function fnEscapeQuotes($data){
		return preg_replace("/\"/","&quot;",$data);
	}
	function fnGetSelectedTypeMap(){
		return array(
			'%issue%' => '',
			'%position%' => '',
			'%supporting_argument%' => '',
			'%opposing_argument%' => '',
		);
	}
	
	function fnGetEditFormTemplate(){
		return '<form method="post" action="">
	<table>
	<tr>
		<td>
			Type:
		</td>
		<td>
			<select name="type">
				<option value="issue" %issue%>Issue</option>
				<option value="position" %position% >Position</option>
				<option value="supporting_argument" %supporting_argument% >Supporting Argument</option>
				<option value="opposing_argument" %opposing_argument% >Opposing Argument</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			Title:
		</td>
		<td>
			<input type="text" name="ibis_title" size="50" value="%s"/>
		</td>
	</tr>
	<tr>
		<td>
			Description:
		</td>
		<td>
			<textarea rows="3" cols="25" name="desc" >%s</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="hidden" name="user" value="%s" />
			<input type="submit" value="Save" name="save">
			<input type="submit" value="Cancel" name="cancel"/>
		</td>
	</tr>
	</table>
</form>
';
	}

	function get_form($title='', $type='', $desc='', $user='') {
		if(!$user){
			$user = $this->user->id;
		}
		//Escape Quote chars
		$title = $this->fnEscapeQuotes($title);
		// A Map of response type and selected
		$selected_type = $this->fnGetSelectedTypeMap();
		// Response template
		$response_template = $this->fnGetEditFormTemplate();
		// Changing null to selected for respective response type
		$selected_type["%".$type."%"] = 'selected';
		// Replace selected response type map with response_template
		$response_template = str_replace(array_keys($selected_type),array_values($selected_type),$response_template);
		// Format response template with response title and its node info
		$response = sprintf($response_template,$title,$desc,$user);
		return $response;
	}
	
	function get_response_form() {
		return $this->get_form();
	}
	
	function get_discussion_form(){
		$title=isset($this->ibis['title'])?$this->fnEscapeQuotes($this->ibis['title']):"";
		$type=isset($this->ibis['type'])?$this->ibis['type']:"";
		$user=isset($this->ibis['user'])?$this->ibis['user']:$this->user->id;
		$desc=isset($this->ibis['desc'])?$this->ibis['desc']:"";
		return $this->get_form($title,$type,$desc,$user);
	}
}
?>
