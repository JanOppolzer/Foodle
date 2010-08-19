<?php

require_once('_include.php');

$config = SimpleSAML_Configuration::getInstance('foodle');

$fullURI = $_SERVER['PATH_INFO'];
$script = $_SERVER['SCRIPT_NAME'];

$path = substr($fullURI, strlen($script) + 1, strlen($fullURI) - strlen($script) - 1);
$parameters = explode('/', $path);

#echo '<pre>'; print_r($parameters); exit;

try {
	
	$action = array_shift($parameters);
	
	switch($action) {

		case '':
			$page = new Pages_PageFront($config, $parameters);
			$page->show();
			break;
	
		case 'foodle':
			$page = new Pages_PageFoodle($config, $parameters);
			$page->show();
			break;

		case 'create':
			$page = new Pages_PageCreate($config, $parameters);
			$page->show();
			break;

		case 'edit':
			$page = new Pages_PageEdit($config, $parameters);
			$page->show();
			break;

		case 'preview':
			$page = new Pages_PagePreview($config, $parameters);
			$page->show();
			break;

		case 'test':
			require_once('test-calendar.php');
			break;

		// Redirecting user if using old 
		case 'foodle.php':
			header('Location: /foodle/' . $_REQUEST['id']);
			break;
		
		// No page found.
		default:
			throw new Exception('404: Page not found [' . $action . '].');
	
	}


} catch(Exception $e) {

	$t = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$t->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$t->data['message'] = $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>';	
	$t->show();

}