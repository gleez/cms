# Gleez Gravatar

## Introduction

[Gravatar's](http://gravatar.com) are universal avatars available to all web sites and services. Users must register their email addresses with Gravatar before their avatars will be usable in Gleez.

The __Gleez Gravatar__ component provides an easy way to retrieve a user's profile image from [Gravatar](https://gravatar.com) based on a given email address.
If the email address cannot be matched with a Gravatar account, an alternative will be returned based on the `default_image` setting.

Users with gravatars can have a default image of your selection.

## Usage

Gravatar images may be requested just like a normal image, using an <strong>&lt;img&gt;</strong> tag. To get an image specific to a user, first you must create an <span class="classname">Gravatar</span> instance:
~~~
$avatar = Gravatar::instance('username@site.com');
~~~

If at this time directly print this variable you will get the most basic image request URL:
~~~
echo $avatar;
// this will return  an URL of image
// http://www.gravatar.com/avatar/b6b1f9e2e403e0907d9a64aaca64fb1c?s=250&r=G
~~~

Display a Gravatar (using default settings):

~~~
echo Gravatar::instance('username@site.com')->getImage();
~~~

Display 64x64 Gravatar (only PG images and using the `monsterid` default):

~~~
echo Gravatar::instance('username@site.com')
		->setSize(64)
		->setRating('PG')
		->setDefaultImage('monsterid')
		->getImage();
~~~

[!!] __Note__: Avatar size must be within 0 pixels and 2048 pixels. With using 0 size, images from [Gravatar](http://gravatar.com) will be returned as 80x80 px

Display Gravatar using HTTPS connection:

~~~
echo Gravatar::instance('username@site.com')
		->enableSecureURL()
		->getImage();
~~~

[!!] __Note__: Secure connection for <span class="classname">Gravatar</span> instance will always be used if you are using a secure connection for your website regardless of the setting of this parameter with using the [Gravatar::enableSecureURL]

Display a default Gravatar image:

~~~
echo Gravatar::instance('username@site.com')
		->setDefaultImage('retro')
		->setForceDefault()
		->getImage();
~~~

Download gravatar (to the current picture directory):

~~~
// get an image specific to a user
$avatar = Gravatar::instance('username@site.com');

// download gravatar
$result = $avatar->download();

// print result
echo __('Gravatar saved to :loc, file size: :len', array(
     ':loc' => $result->location,
    ':len' => $result->length
));
~~~

Set custom location for downloading gravatars:

~~~
// get an image specific to a user
$avatar = Gravatar::instance('username@site.com')->setStoreLocation('media');

// download gravatar
$result = $avatar->download();

// print result
echo __('Gravatar saved to :loc, file size: :len', array(
    ':loc' => $result->location,
    ':len' => $result->length
));
~~~

## Configuration

~~~
$avatar = Gravatar::instance('username@site.com');
~~~


### size

The size of the returned gravatar (width and height) in pixels, e.g.:
~~~
$avatar->setSize(64);
~~~


### default_image

The default image if gravatar is not found, FALSE uses gravatar default.
Possible values:

+ __404__ &mdash; do not load any image if none is associated with the email, instead return an HTTP 404 (File Not Found) response
+ __mm__ &mdash; (mystery-man) a simple, cartoon-style silhouetted outline of a person (does not vary by email)
+ __identicon__ &mdash; a geometric pattern based on an email
+ __monsterid__ &mdash; a generated 'monster' with different colors, faces, etc
+ __wavatar__ &mdash; generated faces with differing features and backgrounds
+ __retro__ &mdash; awesome generated, 8-bit arcade-style pixelated faces
+ __blank__ &mdash; a transparent PNG image
+ Your image URL

Example:
~~~
$avatar->setDefaultImage('identicon');
$avatar->setDefaultImage("http://example.com/your-default-image.png");
~~~

There are a few conditions which must be met for default image URL:

+ __MUST__ be publicly available (e.g. cannot be on an intranet, on a local development machine, behind HTTP Auth or some other firewall etc). Default images are passed through a security scan to avoid malicious content
+ __MUST__ be accessible via HTTP or HTTPS on the standard ports, 80 and 443, respectively
+ __MUST__ have a recognizable image extension (jpg, jpeg, gif, png)
+ __MUST NOT__ include a query string (if it does, it will be ignored)


### secure_url

Should we use the secure (HTTPS) URL base? E.g.:

~~~
// Enable
$avatar->enableSecureURL();
// Disable
$avatar->disableSecureURL();
~~~


### rating

The maximum rating to allow for the avatar.
Possible values:

+ __G__ &mdash; suitable for display on all websites with any audience type
+ __PG__ &mdash; may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence
+ __R__ &mdash; may contain such things as harsh profanity, intense violence, nudity, or hard drug use
+ __X__ &mdash; may contain hardcore sexual imagery or extremely disturbing violence

Example:
~~~
$avatar->setRating('PG');
~~~

[!!] __Note__: The `rating` options is not case sensitive


### force_default

If for some reason you wanted to force the default image to always load, you can set it to TRUE

Example:
~~~
$avatar->setForceDefault();
// Is the same as
$avatar->setForceDefault(TRUE);

// Disable
$avatar->setForceDefault(FALSE);
~~~

### valid_formats

An array of valid picture formats for downloading

[!!] Note: There is no apparent reason to change the default settings of `valid_formats`

Example:
~~~
$avatar->setValidFormats(array('jpg', 'png'));
~~~

### store_location

Set store location for downloading pictures

[!!] Note: If `store_location` is NULL, or not string, or not param exists, by default use `'media/pictures'`
     from APPPATH. If dir not exists and fails create it used sys_get_temp_dir().
     See [sys_get_temp_dir](http://www.php.net/manual/en/function.sys-get-temp-dir.php)

Example:
~~~
$avatar->setStoreLocation('media');
~~~
