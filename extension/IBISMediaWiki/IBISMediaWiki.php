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

function fnIBISPageRenderer( &$out, &$text ){
	global $wgTitle,$wgScript;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$title = $wgTitle;
		$path = $wgScript;
		$display = new DisplayHandler($title);
		if($display->isConvertionApplicableForThisPage()){
			$text = $display->getPageHTML($path);
		}
	}
	return true;
}

function fnIBISEdit( &$editpage)
{	
	global $wgOut,$wgRequest,$wgTitle;
	if (preg_match("/^IBIS\s\d+$/",$wgTitle->getText())){
		$display = new DisplayHandler($wgTitle);
		if(!$display->isConvertionApplicableForThisPage()){
			return True;
		}
		$type_map = array(
			'issue' => 'Issue',
			'position' => 'Position',
			'supporting_argument' => 'Supporting Argument',
			'opposing_argument' => 'Opposing Argument',
		);
		$wgOut->setPageTitle('Editing IBIS Node : '.$editpage->mTitle->getText());
		
		$content = $editpage->mArticle->getContent();		
		
		if ( $wgRequest->wasPosted() ) {		
			if($wgRequest->getCheck('save')){
				fnIBISSaveResponses($wgRequest,$editpage->mTitle->getText());
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
			if($wgRequest->getCheck('cancel')){
				$wgOut->redirect($editpage->mTitle->getFullUrl());
			}
		}
		// Render Edit form
		$form_handler = new FormHandler($content);
		$wgOut->addHTML	($form_handler->get_edit_form());
		
		return false;
	}
	else{
		return True;
	}
}

function fnIBISSaveResponses($request,$page_title){
	$page_handler = new PageHandler($page_title);
	
	$types = $request->data['type'];
	$titles = $request->data['ibis_title'];
	$nodes = $request->data['node'];
	for($i=0;$i<count($titles);$i++){
		$title = $titles[$i];
		$type = $types[$i];
		$node = $nodes[$i];
		
		//Add the response only if its title is not empty
		if($title!=''){
			$page_handler->AddResponse($title,$type,$node);
		}
	}	
	$page_handler->SavePage();
	return;
}
?>