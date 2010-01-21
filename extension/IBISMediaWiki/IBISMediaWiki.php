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
require_once('C:/wamp/bin/php/php5.3.0/PEAR/FirePHPCore/fb.php');

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


function fnIBISDiscussionHandler($action, $article){
	global $wgOut,$wgRequest,$wgUser,$wgTitle;
	$current_title = $article->getTitle();
	$user = new UserHandler($wgUser);
	if($action=="discussion"){
		$op = $wgRequest->data['op'];
		if($wgRequest->wasPosted()){
			if($wgRequest->getCheck('save')){
				if(isset($_SESSION['ibis'])){
					$ibis = unserialize($_SESSION['ibis']);
					unset($_SESSION['ibis']);
				}
				else{
					$ibis = array();
				}
				$ibis['title'] = $wgRequest->data['ibis_title'];
				$ibis['type'] = $wgRequest->data['type'];
				$ibis['user'] = $wgRequest->data['user'];
				$discussionHandler = new DiscussionHandler($ibis);
				if($op=="new"){
					$title = $discussionHandler->AddDiscussion();
					$titleObj = Title::newFromText($title);
					$wgOut->redirect($titleObj->getFullUrl());
				}
				elseif($op=="edit"){
					$discussionHandler->ModifyDiscussion($current_title->getText());
					$wgOut->redirect($current_title->getFullUrl());
				}
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($current_title->getFullUrl());
			}
		}
		else{
			if($op=="new"){
				$wgOut->setPageTitle("Add new discussion");
				$form = new FormHandler($user,array());
				$wgOut->addHTML($form->get_discussion_form());
			}
			elseif($op=="edit"){
				$title = $wgTitle->getText();
				$page = new PageHandler($title,$user);
				$page->LoadCurrentPage(False);
				if (($page->ibis['user'] == $user->id) or $user->isAdminUser){
					$_SESSION['ibis'] = serialize($page->ibis);
					FB::info(print_r($_SESSION,true));
					$wgOut->setPageTitle($title);
					$form = new FormHandler($user,$page->ibis);
					$wgOut->addHTML($form->get_discussion_form());
				}
				else{
					$wgOut->setPageTitle("Warning!");
					$wgOut->addHTML('<strong style="color:red">Please do not try to edit other user discussion. You can still add responses to it.</strong>');
				}
			}
		}
		return false;
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
	'href' => $wgTitle->getLocalURL("action=discussion&op=new"),
	);

	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$display = new DisplayHandler($wgTitle);
		if($display->isConvertionApplicableForThisPage()){
			$content_actions['edit']['text'] = "Add response";
			$page = new PageHandler($wgTitle->getText(),$user);
			$page->LoadCurrentPage(False);
			if (($page->ibis['user'] == $user->id) or $user->isAdminUser){
				$content_actions['edit_discussion'] = Array(
					'text' => "Edit Discussion",
					'href' => $wgTitle->getLocalURL("action=discussion&op=edit"),
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