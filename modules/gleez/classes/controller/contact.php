<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Gleez Contact Controller
 *
 * @package    Gleez\Controller
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
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
		if($config->get('use_captcha', FALSE))
		{
			$captcha = Captcha::instance();
			$view->set('captcha', $captcha);
		}

		$form = array('name' => '', 'email' => '', 'subject' => '', 'category' => '', 'body' => '');
		// Create validation object
		$post = Validation::factory(empty($_POST) ? $form : $_POST)
			->rule('name', 'not_empty')
			->rule('name',  'min_length', array(':value', 4))
			->rule('mail', 'not_empty')
			->rule('mail', 'min_length', array(':value', 4))
			->rule('mail', 'max_length', array(':value', 254))
			->rule('mail', 'email')
			->rule('mail', 'email_domain')
			->rule('subject', 'not_empty')
			->rule('subject', 'max_length', array(':value', $config->subject_length))
			->rule('category', 'not_empty')
			->rule('body', 'not_empty')
			->rule('body', 'max_length', array(':value', $config->body_length));

		if ($this->valid_post('contact'))
		{

			if ($post->check())
			{
				// Create the email subject
				$subject = __('[:category] :subject', array(
					':category' => $types[$post['category']],
					':subject'  => Text::plain($post['subject'])
				));

				// Create the email body
				$body = View::factory('email/contact')
						->set('name', $post['name'])
						->set('type', $types[$post['category']])
						->set('body', $post['body'])
						->render();

				// Create an email message
				$email = Email::factory()
						->subject($subject)
						->from(Text::plain($post['mail']), Text::plain($post['name']))
						->reply_to(Text::plain($post['mail']), Text::plain($post['name']))
						->to($this->_config->get('site_email', 'webmaster@gleezcms.org'), __('Webmaster :site', array(':site' => $this->_config->get('site_name', 'Gleez CMS'))))
						->message($body);

				// Send the message
				$email->send();

				Message::success(__('Your message has been sent.'));
				Kohana::$log->add(LOG::INFO, ':name sent an e-mail regarding :category', array(
					':name'     => Text::plain($post['name']),
					':category' => $types[$post['category']])
				);

				// Always redirect after a successful POST to prevent refresh warnings
				if ( ! $this->_internal)
				{
					$this->request->redirect(Route::get('contact')->uri(), 200);
				}
			}
			else
			{
				$this->_errors = $post->errors('contact', TRUE);
			}
		}

		$this->response->body($view);
	}
}