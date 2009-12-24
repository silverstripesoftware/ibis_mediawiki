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
  'url'        => 'url',
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
	$article = new Article($wgTitle);
	$content = $article->getContent();
	$array = fnIBISYamlToArray($content);
	$text = fnIBISArrayToHTML($array);
	return true;
}

function fnIBISEdit( &$editpage)
{	
	global $wgOut,$wgRequest;
	$type_map = array(
		'issue' => 'Issue',
		'position' => 'Position',
		'supporting_argument' => 'Supporting Argument',
		'opposing_argument' => 'Opposing Argument',
	);
	$wgOut->setPageTitle('Editing IBIS Node : '.$editpage->mTitle->getText());
	
	if ( $wgRequest->wasPosted() ) {
		$desc = $wgRequest->getText( 'description' );
		$type = $wgRequest->getText( 'type' );
		if($wgRequest->getCheck('save')){
			if($desc==''){
				$wgOut->addWikiText('<strong style="color:red;">Error : Description cannot be left blank</strong>');
				//$wgOut->errorpage( 'badtitle', 'badtitletext');
			}
			else{		
				$page_title = fnIBISCreatePage($desc,$type);
				// Get the current page content
				$content = $editpage->mArticle->getContent();
				// Add the response to the current page content
				$content = fnIBISAddResponse($content,$desc,$type,$page_title);
				// Update the current page content with new response
				$editpage->mArticle->updateArticle( $content, '', false,	false, false, '' );
				$wgOut->redirect($editpage->mTitle->getFullUrl());
				#$wgOut->addWikiText(sprintf('<strong style="color:green;">%s Added</strong>',$type_map[$type]));
			}
		}
		if($wgRequest->getCheck('cancel')){
			$wgOut->redirect($editpage->mTitle->getFullUrl());
		}
	}
	// Render Edit form;
	$edit_form = fnIBISEditForm();
	$wgOut->addHTML	($edit_form);

	return false;
}
function fnIBISEditForm(){
	$form = '<form action="" method="post">
				<h3> Response </h3>
				<select name="type">
					<option value="issue">Issue</option>
					<option value="position">Position</option>
					<option value="supporting_argument">Supporting Argument</option>
					<option value="opposing_argument">Opposing Argument</option>
				</select>
				<input type="text" name="description" size="50" />
				<br /><br />
				<input type="submit" value="Save" name="save"/>
				<input type="submit" value="Cancel" name="cancel"/>
			</form>';
	return $form;
}

function fnIBISCreatePage($desc,$type){
	$id = fnIBISGetNextPageID();
	$new_title = "IBIS_".$id;
	$body = fnIBISBuildYAMLConversation($desc,$type);
	$title = Title::newFromText($new_title);
	$article = new Article($title);
	$article->doEdit($body,'');
	return $new_title;
}

function fnIBISAddResponse($content,$desc,$type,$page_title)
{
	$ibis = fnIBISYamlToArray($content);
	
	$response = array();
	$response['title'] = $desc;
	$response['type'] = $type;
	$response['node'] = $page_title;
	
	$ibis['responses'][]= $response;
	
	$content = fnIBISArrayToYaml($ibis);
	
	return $content;
	
}
function fnIBISBuildYAMLConversation($desc,$type){
	$ibis = array();
	$ibis['title'] = $desc;
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