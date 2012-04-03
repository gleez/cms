#Captcha for Kohana 3.x

This is the Captcha library ported from Kohana 2.3.x to 3.x. Very little has changed API-wise, although there have been a few changes.

##Getting Started

Instantiate a captcha:

> $captcha = Captcha::instance();

Instantiate using your own config group (other than 'default'):

> $captcha = Captcha::instance('myconfig');

Render a captcha:

> $captcha->render();

or just:

> $captcha;

Validate the captcha:

> Captcha::valid($_POST['captcha']);

By default image-based captchas are rendered with HTML, the HTML is a very simple <img> tag. If you want to handle your own rendering of the captcha simply set the first parameter for render() to FALSE:

> $captcha->render(FALSE);

##Captcha Styles

* alpha
* basic
* black
* math
* riddle
* word
