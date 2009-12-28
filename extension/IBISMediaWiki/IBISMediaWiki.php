<?php

/*
* Author : Karthik Jayapal
* Started on : 24/12/2009	
* For : iMorph Inc.,
*/

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die();
}

require_once('spyc.php');

$wgExtensionFunctions[] = 'fnIBISMediaWiki';
$wgExtensionCredits['other'][] = array(
  'name'       => 'IBIS_Mediawiki',
  'author'     => 'Karthik jayapal',
  'url'        => '',
  'description'=> 'IBIS conversation in Mediawiki',
);

function fnIBISMediaWiki()
{
	global $wgHooks;
	$wgHooks['AlternateEdit'][] = 'fnIBISEdit';
	$wgHooks['OutputPageBeforeHTML'][] = 'fnIBISPageRenderer';

}

function fnIBISArrayToHTML($array){
	global $wgScript;
	$template_main = '<div class="ibis_conversation">
	<h1 class="type_%s">%s</h1>';
	$template_response = '<li class="type_%s"><a href="'.$wgScript.'/%s">%s</a></li>';
	//Main HTML
	$main = sprintf($template_main,$array['type'],$array['title']);
	//Response HTML
	$responses = '';
	if(!empty($array['responses'])){
	foreach($array['responses'] as $response){
		$responses .= sprintf($template_response,$response['type'],$response['node'],$response['title']);
	}
	if($responses){
		$html = sprintf("%s\n<ul>%s</ul></div>",$main,$responses);
	}
	}
	else{
		$html = $main."</div>";
	}
	return $html;
}

function fnIBISPageRenderer( &$out, &$text ){
	global $wgTitle;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$article = new Article($wgTitle);
		$content = $article->getContent();
		$array = fnIBISYamlToArray($content);
		if(isset($array['title']) && isset($array['type'])){
			$text = fnIBISArrayToHTML($array);
		}
	}
	return true;
}

function fnIBISEdit( &$editpage)
{	
	global $wgOut,$wgRequest,$wgTitle;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$type_map = array(
			'issue' => 'Issue',
			'position' => 'Position',
			'supporting_argument' => 'Supporting Argument',
			'opposing_argument' => 'Opposing Argument',
		);
		$wgOut->setPageTitle('Editing IBIS Node : '.$editpage->mTitle->getText());
		
		$content = $editpage->mArticle->getContent();
		
		$ibis_array = fnIBISYamlToArray($content);
		
		if ( $wgRequest->wasPosted() ) {		
			if($wgRequest->getCheck('save')){
				fnIBISSaveResponses($wgRequest,$editpage,$ibis_array);
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
		}
		// Render Edit form
		$edit_form = fnIBISEditForm($ibis_array);
		$wgOut->addHTML	($edit_form);

		return false;
	}
	else{
		return True;
	}
}
function fnIBISEditForm($ibis_array){
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
				<br /><br />';
	}
	function fnCreateResponseForm($title,$type,$node){
		// A Map of response type and selected
		$selected_type = fnGetSelectedTypeMap();
		// Response template
		$response_template = fnGetResponseTemplate();
		// Changing null to selected for respective response type
		$selected_type["%".$type."%"] = 'selected';
		// Replace selected response type map with response_template
		$response_template = str_replace(array_keys($selected_type),array_values($selected_type),$response_template);
		// Format response template with response title and its node info
		$response = sprintf($response_template,$title,$node);
		
		return $response;
	}
	
	$form = '<form action="" method="post">
				<h3> Responses : </h3>
				%s
				<input type="submit" value="Save" name="save"/>
				<input type="submit" value="Cancel" name="cancel"/>
			</form>';

	// A varaible to hold all the response form
	$responses = '';
	//Building pre-filled response form with node content
	foreach($ibis_array['responses'] as $response){
		$responses .= fnCreateResponseForm($response['title'],$response['type'],$response['node']);
	}
	//Adding a new response form at the end to add new response by passing empty values to CreateResponseForm funciton
	$responses .= fnCreateResponseForm('','','');
	$form = sprintf($form,$responses);

	return $form;
}
function fnIBISSaveResponses($request,$editpage,$ibis_array){
	// Removing the existing responses
	unset($ibis_array['responses']);
	
	$types = $request->data['type'];
	$titles = $request->data['ibis_title'];
	$nodes = $request->data['node'];
	for($i=0;$i<count($titles);$i++){
		$title = $titles[$i];
		$type = $types[$i];
		$node = $nodes[$i];
		
		//Proceed only if title is not empty
		if($title!=''){
			if($node==''){
				//Create page
				$page_title = fnIBISEditPage($title,$type);
			}
			else{
				//Edit page
				$page_title = fnIBISEditPage($title,$type,$node);
			}
			// Add the response to the current page content
			$ibis_array = fnIBISAddResponse($ibis_array,$title,$type,$page_title);
		}
	}
	
	//Convert IBIS array to YAML
	$content = fnIBISArrayToYaml($ibis_array);
	
	// Update the current page content with new responses
	$editpage->mArticle->updateArticle( $content, '', false, false, false, '' );
	return;
}
function fnIBISEditPage($ibis_title,$type,$page_title=''){
	$newPage = False;
	if($page_title==''){
		$newPage = True;
		$id = fnIBISGetNextPageID();
		$page_title = "IBIS_".$id;
		$body = fnIBISBuildYAMLConversation($ibis_title,$type);
	}
	$title = Title::newFromText($page_title);
	$article = new Article($title);
	if($newPage){
		$body = fnIBISBuildYAMLConversation($ibis_title,$type);
	}
	else{
		$content = $article->getContent();
		$ibis = fnIBISYamlToArray($content);
		$body = fnIBISBuildYAMLConversation($ibis_title,$type,$ibis);
	}
	$article->doEdit($body,'');
	return $page_title;
}

function fnIBISAddResponse($ibis_array,$title,$type,$page_title)
{
	$response = array();
	$response['title'] = $title;
	$response['type'] = $type;
	$response['node'] = $page_title;
	
	$ibis_array['responses'][]= $response;
	
	return $ibis_array;
	
}
function fnIBISBuildYAMLConversation($title,$type,$ibis=array()){
	$ibis['title'] = $title;
	$ibis['type'] = $type;
	return fnIBISArrayToYaml($ibis);
}

function fnIBISGetNextPageID(){
	$dbr = &wfGetDB( DB_SLAVE );
	$q = "select max(page_id) as max_page_id from page";
	$res = $dbr->query($q);
	$row = $dbr->fetchObject($res);
	$next_id = ((int)$row->max_page_id)+1;
	return $next_id;
}

// Yaml wrappers

function fnIBISYamlToArray($yaml){
	return Spyc::YAMLLoad($yaml);
}

function fnIBISArrayToYaml($array){
	return Spyc::YAMLDump($array);
}
?>