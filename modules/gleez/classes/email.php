<?php
/**
 * Email message building and sending
 *
 * @package    Gleez\Email
 * @author     Gleez Team
 * @version    1.2.0
 * @copyright  (c) 2011-2015 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 * @link       https://github.com/Synchro/PHPMailer
 */
class Email {

	/**
	 * Mail queue bool
	 */
	protected $queue = FALSE;

	/**
	 * Mail object
	 * @var PHPMailer
	 */
	protected $_mail;

	/**
	 * Create a new email message
	 *
	 * @param   boolean  $exceptions  PHPMailer should throw external exceptions? [Optional]
	 * @return  Email
	 */
	public static function factory($exceptions = TRUE)
	{
		return new Email($exceptions);
	}

	/**
	 * Class constructor
	 *
	 * @param   boolean  $exceptions  PHPMailer should throw external exceptions? [Optional]
	 */
	public function __construct($exceptions = TRUE)
	{
		require_once Kohana::find_file('vendor/PHPMailer', 'PHPMailerAutoload');

		// Create phpmailer object
		$this->_mail = new PHPMailer($exceptions);

		// Set some defaults
		$this->_mail->setFrom(Config::get('site.site_email','webmaster@example.com'), Template::getSiteName());
		$this->_mail->WordWrap = 70;
		$this->_mail->CharSet  = Kohana::$charset;
		$this->_mail->XMailer  = Gleez::getVersion(FALSE, TRUE);
		$this->_mail->setLanguage(I18n::$lang);
		$this->_mail->Debugoutput = 'error_log';
	}

	/**
	 * Set the message subject
	 *
	 * @param   string  $subject  New subject
	 * @return  Email
	 */
	public function subject($subject)
	{
		// Change the subject
		$this->_mail->Subject = $subject;

		return $this;
	}

	/**
	 * Set the message body
	 *
	 * Multiple bodies with different types can be added by calling this method
	 * multiple times. Every email is required to have a "plain" message body.
	 *
	 * @param   string  $body  New message body
	 * @param   string  $type  Mime type: text/html, text/plain [Optional]
	 * @return  Email
	 */
	public function message($body, $type = NULL)
	{
		if ( ! $type OR $type === 'text/plain')
		{
			// Set the main text/plain body
			$this->_mail->Body = $body;
		}
		else
		{
			// Add a custom mime type
			$this->_mail->msgHTML($body);
		}

		return $this;
	}

	/**
	 * Add one or more email recipients
	 *
	 * Example:
	 * ~~~
	 * // A single recipient
	 * $email->to('john.doe@domain.com', 'John Doe');
	 * ~~~
	 *
	 * @param   string  $email  Single email address
	 * @param   string  $name   Full name [Optional]
	 * @return  Email
	 */
	public function to($email, $name = NULL)
	{
		$this->_mail->addAddress($email, $name);

		return $this;
	}

	/**
	 * Add a "carbon copy" email recipient
	 *
	 * @param   string  $email  Email address
	 * @param   string  $name   Full name [Optional]
	 * @return  Email
	 */
	public function cc($email, $name = NULL)
	{
		$this->_mail->addCC($email, $name);

		return $this;
	}

	/**
	 * Add a "blind carbon copy" email recipient
	 *
	 * @param   string  $email  Email address
	 * @param   string  $name   Full name [Optional]
	 * @return  Email
	 */
	public function bcc($email, $name = NULL)
	{
		$this->_mail->addBCC($email, $name);

		return $this;
	}

	/**
	 * Add email senders
	 *
	 * @param   string  $email  Email address
	 * @param   string  $name   Full name [Optional]
	 * @return  Email
	 */
	public function from($email, $name = NULL )
	{
		$this->_mail->setFrom($email, $name);

		return $this;
	}

	/**
	 * Add "reply to" email sender
	 *
	 * @param   string  $email  Email address
	 * @param   string  $name   Full name [Optional]
	 * @return  Email
	 */
	public function reply_to($email, $name = NULL)
	{
		$this->_mail->addReplyTo($email, $name);

		return $this;
	}

	/**
	 * Set the return path for bounce messages
	 *
	 * @param   string  $email  Email address
	 * @return  Email
	 */
	public function return_path($email)
	{
		$this->_mail->Sender = $email;

		return $this;
	}

	/**
	 * Queue the email for future delivery
	 *
	 * @param   int     $timestamp  Email delivery Timestamp (ex: After One hour)
	 * @param   bool    $unique     Ignore duplicate mails for queuing
	 * @param   array   $params     Additional params for unique
	 * @return  Email
	 */
	public function queue($timestamp = NULL, $unique = FALSE, $params = NULL)
	{
		try
		{
			//@todo insert into mailqueue table
			$this->queue = TRUE;
		}
		catch(Exception $e)
		{
			Log::error('Error queuing mail error: :e', array(':e' => $e->getMessage()));
		}

		return $this;
	}

	/**
	 * Sends the email
	 *
	 * @return  boolean
	 */
	public function send()
	{
		try
		{
			// Send mail if its not queued
			if($this->queue == FALSE)
			{
				$this->_mail->send();
			}
	
			return TRUE;
		}
		catch(Exception $e)
		{
			Log::error('Error sending mail error: :e', array(':e' => $e->getMessage()));
			return FALSE;
		}
	}

	/**
	 * Mail object of the instance
	 *
	 * @return  PHPMailer
	 */
	public function mail()
	{
		return $this->_mail;
	}

}
