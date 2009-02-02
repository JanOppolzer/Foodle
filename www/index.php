<?php

require_once('_include.php');



$config = SimpleSAML_Configuration::getInstance('foodle');

// Starting sessions.
session_start();



try {


	$foodleauth = new FoodleAuth();
	
	$foodleauth->requireAuth(TRUE);	
	
// 	if (array_key_exists('foodleSession', $_COOKIE) || array_key_exists('sessionBootstrap', $_REQUEST)) {
// 		$foodleauth->requireAuth(TRUE);
// 	} else {
// 		$foodleauth->requireAuth(FALSE);	
// 	}

	$email = $foodleauth->getMail();
	$userid = $foodleauth->getUserID();
	$displayname = $foodleauth->getDisplayName();

	// If anonymous, create a login link.
	$loginurl = NULL;
	if (!$foodleauth->isAuth()) {
		$sspconfig = SimpleSAML_Configuration::getInstance();
		$loginurl = '/' . $sspconfig->getValue('baseurlpath') . 'saml2/sp/initSSO.php?RelayState=' . urlencode(SimpleSAML_Utilities::selfURL());
	}


	
	if (!isset($_SESSION['foodle_cache'])) {
		$_SESSION['foodle_cache'] = array();
	}
	
	
	$link = mysql_connect(
		$config->getValue('db.host', 'localhost'), 
		$config->getValue('db.user'),
		$config->getValue('db.pass'));
	if(!$link){
		throw new Exception('Could not connect to database: '.mysql_error());
	}
	mysql_select_db($config->getValue('db.name','feidefoodle'));
	
	
	
	

	
	$fl = new FoodleListings($userid, $link);
	$entries = $fl->getYourEntries();
	
	$allentries = null;
	if (in_array($userid, array('andreas@rnd.feide.no', 'andreas@uninett.no')))
		$allentries = $fl->getAllEntries(25);
		
	$ownerentries = $fl->getOwnerEntries($userid, 10);	

	/*
	echo 'entries:<pre>';
	print_r($entries);
	exit;
	*/
	
	/*
	Array
(
    [0] => Array
        (
            [id] => tkgnpz3m
            [foodleid] => tkgnpz3m
            [userid] => andreas@rnd.feide.no
            [username] => Andreas Solberg
            [response] => 1,1,0
            [name] => test 2
            [descr] => sdfsdf
            [columns] => Thu 26. Jun|Fri 27. Jun|Sat 28. Jun
        )

    [1] => Array
        (
            [id] => hvcm1j8s
            [foodleid] => hvcm1j8s
            [userid] => andreas@rnd.feide.no
            [username] => Andreas Solberg
            [response] => 0,0
            [name] => Publishers meeting
            [descr] => Meeting with Dutch Publishers in Utrecht
            [columns] => Tue 24. Jun(I will attend lunch,I will attend drink)
        )

)
*/
	
	


	$et = new SimpleSAML_XHTML_Template($config, 'foodlefront.php', 'foodle_foodle');
	$et->data['yourentries'] = $entries;
	$et->data['allentries'] = $allentries;
	$et->data['ownerentries'] = $ownerentries;
	$et->data['userid'] = $userid;
	$et->data['displayname'] = $displayname;
	$et->data['bread'] = array(array('title' => 'bc_frontpage'));
	$et->data['authenticated'] = $foodleauth->isAuth();
	$et->data['loginurl'] = $loginurl;
	$et->show();

} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/', 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();
	$et->show();

}


?>