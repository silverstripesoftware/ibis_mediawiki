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

require_once('FormHandler.php');
require_once('PageHandler.php');
require_once('DisplayHandler.php');
require_once('UserHandler.php');
require_once('DiscussionHandler.php');

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
	$wgHooks['SkinTemplateContentActions'][] = 'fnIBISTabsHandler';
	$wgHooks['AlternateEdit'][] = 'fnIBISEdit';
	$wgHooks['OutputPageBeforeHTML'][] = 'fnIBISPageRenderer';
	$wgHooks['UnknownAction'][] = 'fnIBISDiscussionHandler';
}

function fnIBISDiscussionHTML(){
	$html = '
	<form method="post" action="">
		<select name="type">
			<option value="issue" %issue%>Issue</option>
			<option value="position" %position% >Position</option>
			<option value="supporting_argument" %supporting_argument% >Supporting Argument</option>
			<option value="opposing_argument" %opposing_argument% >Opposing Argument</option>
		</select>
		<input type="text" name="ibis_title" size="50" value=""/>
		<br /><br />
		<input type="submit" value="Save Discussion" name="add">
		<input type="submit" value="Cancel" name="cancel"/>
	</form>
	';
	return $html;
}

function fnIBISDiscussionHandler($action, $article){
	global $wgOut,$wgRequest,$wgUser,$wgTitle;
	$user = new UserHandler($wgUser);
	if($action=="newdiscussion"){
		$wgOut->setPageTitle("Add new discussion");
		
		if($wgRequest->wasPosted()){
			if($wgRequest->getCheck('add')){
				$ibis_title = $wgRequest->data['ibis_title'];
				$type = $wgRequest->data['type'];
				$discussionHandler = new DiscussionHandler($ibis_title,$type,$user);
				$title = $discussionHandler->SaveDiscussion();
				$titleObj = Title::newFromText($title);
				$wgOut->redirect($titleObj->getFullUrl());
			}
			if($wgRequest->getCheck('cancel')){
				$title = $article->getTitle();
				$wgOut->redirect($title->getFullUrl());
			}
		}
		else{
			$wgOut->addHTML(fnIBISDiscussionHTML());
		}
		return False;
	}
	elseif($action="editdiscussion"){
		$title = $wgTitle->getText();
		$page = new PageHandler($title,$user);
		$page->LoadCurrentPage();
		if ($page->ibis['user'] == $user->id){
			$wgOut->setPageTitle($title);
			$wgOut->addHTML("To be implemented");
			return False;
		}
		else{
			$wgOut->setPageTitle("Warning!");
			$wgOut->addHTML('<strong style="color:red">Please do not try to edit other user discussion. You can still add responses to it.</strong>');
			return False;
		}
	}
	return True;
}
function fnIBISTabsHandler(&$content_actions){
	global $wgTitle,$wgUser;
	$user = new UserHandler($wgUser);
	if($user->isGuest){
		unset($content_actions['viewsource']);
		return True;
	}
	$content_actions['new_discussion'] = Array(
	'text' => "New Discussion",
	'href' => $wgTitle->getLocalURL("action=newdiscussion"),
	);

	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$display = new DisplayHandler($wgTitle);
		if($display->isConvertionApplicableForThisPage()){
			$content_actions['edit']['text'] = "Add response";
			$page = new PageHandler($wgTitle->getText(),$user);
			$page->LoadCurrentPage();
			if ($page->ibis['user'] == $user->id){
				$content_actions['edit_discussion'] = Array(
					'text' => "Edit Discussion",
					'href' => $wgTitle->getLocalURL("action=editdiscussion"),
				);
			}
		}
		else{
			unset($content_actions['edit']);
		}
	}
	return True;
}

function fnIBISPageRenderer( &$out, &$text ){
	global $wgTitle,$wgScript,$wgOut;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$title = $wgTitle;
		$path = $wgScript;
		$display = new DisplayHandler($title);
		if($display->isConvertionApplicableForThisPage()){
			$text = $display->getPageHTML($path);
		}
		else{
			$wgOut->setPageTitle('Error');
			$text = '<strong style="color:red">Sorry, You cannot add discussions this way. Use "New discussion" tab </strong>';
		}
	}
	return true;
}

function fnIBISEdit( &$editpage)
{	
	global $wgOut,$wgRequest,$wgTitle,$wgUser;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$display = new DisplayHandler($wgTitle);
		if(!$display->isConvertionApplicableForThisPage()){
			$wgOut->setPageTitle('Error');
			$wgOut->addHTML('<strong style="color:red">Sorry, You cannot add discussions this way. Use "New discussion" tab </strong>');
			return False;
		}
		//IBIS User Handler for current user
		$user = new UserHandler($wgUser);
		
		$type_map = array(
			'issue' => 'Issue',
			'position' => 'Position',
			'supporting_argument' => 'Supporting Argument',
			'opposing_argument' => 'Opposing Argument',
		);
		$wgOut->setPageTitle('Add/Edit responses to IBIS Node : '.$editpage->mTitle->getText());
		
		$content = $editpage->mArticle->getContent();		
		
		if ( $wgRequest->wasPosted() ) {		
			if($wgRequest->getCheck('save')){
				fnIBISSaveResponses($wgRequest,$editpage->mTitle->getText(),$user);
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
		}
		// Render Edit form
		$form_handler = new FormHandler($user,$content);
		$wgOut->addHTML	($form_handler->get_edit_form());
		
		return false;
	}
	else{
		return True;
	}
}
function fnIBISSaveResponses($request,$page_title,$user){
	$types = $request->data['type'];
	$titles = $request->data['ibis_title'];
	$nodes = $request->data['node'];
	$users = $request->data['user'];
	
	$page_handler = new PageHandler($page_title,$user);
	$page_handler->LoadCurrentPage();
	for($i=0;$i<count($titles);$i++){
		$title = $titles[$i];
		$type = $types[$i];
		$node = $nodes[$i];
		$user = $users[$i];
		
		//Add the response only if its title is not empty
		if($title!=''){
			$response = fnIBISSaveResponse($type,$title,$node,$user,$page_handler);
			$page_handler->ibis['responses'][] = $response;
		}
		
	}	
	$page_handler->SavePage();
	return;
}

?>