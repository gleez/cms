<?php
/**
 * Gleez Contact Controller
 *
 * @package    Gleez\Controller
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Contact extends Template {

	/**
	 * The before() method is called before controller action
	 *
	 * @uses  ACL::required
	 */
	public function before()
	{
		ACL::required('sending mail');

		parent::before();
	}

	/**
	 * Sending mails
	 *
	 * @uses  Request::query
	 * @uses  Route::get
	 * @uses  Route::uri
	 * @uses  URL::query
	 * @uses  URL::site
	 * @uses  Validation::rule
	 */
	public function action_mail()
	{
		$this->title = __('Contact us');
		$config = Kohana::$config->load('contact');

		// Set form destination
		$destination = ( ! is_null($this->request->query('destination'))) ? array('destination' => $this->request->query('destination')) : array();

		// Set form action
		$action = Route::get('contact')->uri(array('action' => $this->request->action())).URL::query($destination);

		// Get user
		$user = User::active_user();

		// Set mail types
		$types = $config->get('types', array());

		$view = View::factory('contact/form')
					->set('destination', $destination)
					->set('action',      $action)
					->set('config',      $config)
					->set('types',       $types)
					->set('user',        $user)
					->bind('post',       $post)
					->bind('errors',     $this->_errors);

		// Initiate Captcha
		if($config->get('use_captcha', FALSE) AND ! $this->_auth->logged_in())
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		if ($this->valid_post('contact'))
		{
			$post = Validation_Contact::factory($this->request->post());

			if ($post->check())
			{
				// Create the email subject
				$subject = __('[:category] :subject', array(
					':category' => $types[$post['category']],
					':subject'  => Text::plain($post['subject'])
				));

				// Create the email body
				$body = View::factory('email/contact')
						->set('name',   $post['name'])
						->set('body',   $post['body'])
						->set('config', Kohana::$config->load('site'))
						->render();

				// Create an email message
				$email = Email::factory()
						->to(Text::plain($this->_config->get('site_email', 'webmaster@gleezcms.org')), __('Webmaster :site', array(':site' => Template::getSiteName())))
						->subject($subject)
						->from($post['email'], Text::plain($post['name']))
						->message($body, 'text/html'); // @todo message type should be configurable

				// Send the message
				$email->send();

				Log::info(':name sent an e-mail regarding :cat',
					array(':name' => Text::plain($post['name']), ':cat' => $types[$post['category']])
				);
				Message::success(__('Your message has been sent.'));

				// Always redirect after a successful POST to prevent refresh warnings
				$this->request->redirect(Route::get('contact')->uri(), 200);
			}
			else
			{
				$this->_errors = $post->errors('contact', TRUE);
			}
		}

		$this->response->body($view);
	}
}