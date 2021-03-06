<?php

class Pages_PageEdit extends Pages_PageFoodle {
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->timezone = new TimeZone();
		
		$this->foodle->acl($this->user, 'write');
	}
	
	
	protected function sendMail() {
	
		if (!$this->user->notification('newfoodle', FALSE)) {
			error_log('Foodle was updated, but mail notification was not sent because of users preferences');
			return;
		}
		error_log('Foodle was updated, sending notification!');
	
	
		$profileurl = FoodleUtils::getUrl() . 'profile/';
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$name = $this->foodle->name;
		$to = $this->user->email;
		
		$mail = '
		
Hi, your response to the Foodle named <i>' . htmlspecialchars($name) . '</i> was successfully updated.</p>

You may re-enter the Foodle link below to update your response, and view other responses:

* [Edit your Foodle response](' . $url . ')
* [View responses of other participants](' . $url . '#responses)

### Did you know

You may also create new Foodles on your own, and invite others to respond.

* [Go to Foodl.org to create a new Foodle.](http://foodl.org)

		';

		$mailer = new Foodle_EMail($to, 'Updated foodle: ' . htmlspecialchars($name), 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($mail);
		$mailer->send();
		
		#echo '<pre>'; print_r($mail); exit;

	}
	

	protected function saveChanges() {

		$this->foodle->updateFromPost($this->user);
#		echo '<pre>'; print_r($_REQUEST); exit; print_r($this->foodle); exit;
		$this->foodle->acl($this->user, 'write');
		$this->foodle->save();
		
		if (isset($this->user->email)) {
			$this->sendMail();
		}
		
		$newurl = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier . '#distribute';
		SimpleSAML_Utilities::redirect($newurl);
		exit;
	}

	protected function presentInTimeZone() {
	}
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['save'])) $this->saveChanges();

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodlecreate.php', 'foodle_foodle');

		$t->data['authenticated'] = $this->auth->isAuth();
		
		$t->data['user'] = $this->user;
		
		$t->data['edit'] = TRUE;
		
		$t->data['timezone'] = $this->timezone;
		$t->data['ftimezone'] = $this->foodle->timezone;
		
		$t->data['mygroups'] = $this->fdb->getContactlists($this->user);

		$t->data['name'] = $this->foodle->name;
		$t->data['identifier'] = $this->foodle->identifier;
		$t->data['descr'] = $this->foodle->descr;
		$t->data['expire'] = $this->foodle->expire;
		$t->data['anon'] = $this->foodle->allowanonymous;
		
		$t->data['maxcol'] = $this->foodle->maxcolumn;
		$t->data['maxnum'] = $this->foodle->maxentries;
		
		$t->data['columntype'] = $this->foodle->columntype;
		$t->data['responsetype'] = $this->foodle->responsetype;
		
		$t->data['extrafields'] = $this->foodle->getExtraFields();
		
		$t->data['allowChangeColumn'] = $this->foodle->allowChangeColumn();
		
		$t->data['columns'] = $this->foodle->columns;
		
		$t->data['isDates'] = $this->foodle->onlyDateColumns();
		
		$t->data['foodle'] = $this->foodle;

		$t->data['today'] = date('Y-m-d');
		$t->data['tomorrow'] = date('Y-m-d', time() + 60*60*24 );

		$t->data['expire'] = $this->foodle->getExpireTextField();

		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
			array('title' => 'bc_edit')
		);
		$t->show();


	}
	
}

