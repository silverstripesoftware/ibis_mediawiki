<?php
require_once("YAMLHandler.php");
class FormHandler {
	function __construct($user,$ibis){
		$this->user = $user;
		if(gettype($ibis)=='array'){
			$this->ibis = $ibis;
		}
		else{
			$this->ibis = YAMLHandler::YAMLToArray($ibis);
		}
	}
	function fnGetSelectedTypeMap(){
		return array(
			'%issue%' => '',
			'%position%' => '',
			'%supporting_argument%' => '',
			'%opposing_argument%' => '',
		);
	}

	function fnGetResponseTemplate(){
		return '<select name="type[]">
					<option value="issue" %issue%>Issue</option>
					<option value="position" %position% >Position</option>
					<option value="supporting_argument" %supporting_argument% >Supporting Argument</option>
					<option value="opposing_argument" %opposing_argument% >Opposing Argument</option>
				</select>
				<input type="text" name="ibis_title[]" size="50" value="%s"/>
				<input type="hidden" name="node[]" value="%s" />
				<input type="hidden" name="user[]" value="%s" />
				<br /><br />';
	}
	
	function fnGetEditFormTemplate(){
		return '<form action="" method="post">
				<h3> Responses : </h3>
				%s
				<input type="submit" value="Save" name="save"/>
				<input type="submit" value="Cancel" name="cancel"/>
			</form>';
	}
	

	function fnCreateResponseForm($title,$type,$node,$user){
		// A Map of response type and selected
		$selected_type = $this->fnGetSelectedTypeMap();
		// Response template
		$response_template = $this->fnGetResponseTemplate();
		// Changing null to selected for respective response type
		$selected_type["%".$type."%"] = 'selected';
		// Replace selected response type map with response_template
		$response_template = str_replace(array_keys($selected_type),array_values($selected_type),$response_template);
		// Format response template with response title and its node info
		$response = sprintf($response_template,$title,$node,$user);

		return $response;
	}
	
	function get_field_html($field=array("title"=>'', "type"=>'', "node"=>'')) {
		if(!isset($field['user'])){
			$field['user'] = $this->user->id;
		}
		return $this->fnCreateResponseForm($field["title"], $field["type"], $field["node"], $field['user']);
	}
	
	function get_edit_form() {
		$form = $this->fnGetEditFormTemplate();
		// A varaible to hold all the response form html
		$responses_html = '';
		//Building pre-filled response form
		if(isset($this->ibis['responses'])){
			foreach($this->ibis['responses'] as $response){
				if(!isset($response['user']) || $response['user']==''){
					$response['user'] = '1';
				}
				$response_user = (int)$response['user'];
				if($response_user == $this->user->id || $this->user->isAdminUser){
					$responses_html .= $this->get_field_html($response);
				}				
			}
		}
		//Adding a new response form at the end of prefilled responses(if applicable)	
		$responses_html .= $this->get_field_html();		
		
		return sprintf($form,$responses_html);
	}
}
?>
