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
	function fnIBISDiscussionTemplate(){
		$html = '
		<form method="post" action="">
			<select name="type">
				<option value="issue" %issue%>Issue</option>
				<option value="position" %position% >Position</option>
				<option value="supporting_argument" %supporting_argument% >Supporting Argument</option>
				<option value="opposing_argument" %opposing_argument% >Opposing Argument</option>
			</select>
			<input type="text" name="ibis_title" size="50" value="%s"/>
			<input type="hidden" name="user" value="%s" />
			<br /><br />
			<input type="submit" value="Save Discussion" name="save">
			<input type="submit" value="Cancel" name="cancel"/>
		</form>
		';
		return $html;
	}

	function fnCreateResponseForm($title,$type,$node,$user){
		//Escape Quote chars
		$title = $this->fnEscapeQuotes($title);
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
		// Building pre-filled response form
		if(isset($this->ibis['responses'])){
			$this->factory = new ArticleFactory();
			foreach($this->ibis['responses'] as $response){
				if(!isset($response['user']) || $response['user']==''){
					$response['user'] = '1';
				}
				$response_user = (int)$response['user'];
				if($response_user == $this->user->id || $this->user->isAdminUser){
					$ibis = $this->GetContent($response['node'],True);
					$ibis['node'] = $response['node'];
					$responses_html .= $this->get_field_html($ibis);
				}
			}
		}
		// Adding a new response form at the end of prefilled responses(if applicable)	
		$responses_html .= $this->get_field_html();
		
		return sprintf($form,$responses_html);
	}
	
	function get_discussion_form(){
		$title=isset($this->ibis['title'])?$this->fnEscapeQuotes($this->ibis['title']):"";
		$type=isset($this->ibis['type'])?$this->ibis['type']:"";
		$user=isset($this->ibis['user'])?$this->ibis['user']:$this->user->id;
		$template = $this->fnIBISDiscussionTemplate();
		$selected_type = $this->fnGetSelectedTypeMap();
		if($type){
			$selected_type["%".$type."%"] = 'selected';
		}
		$template = str_replace(array_keys($selected_type),array_values($selected_type),$template);
		$html = sprintf($template,$title,$user);
		return $html;
	}
}
?>
