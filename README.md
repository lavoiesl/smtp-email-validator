# smtp-email-validator

PHP email validator using SMTP

[![Build Status](https://secure.travis-ci.org/lavoiesl/smtp-email-validator.png)](http://travis-ci.org/lavoiesl/smtp-email-validator)

## Requirements

### Windows

1. [Net_DNS](http://pear.php.net/package/Net_DNS)

## Basic usage

```php
<?php
  $validator = new SMTP_Email_Validator('me@example.com');
  if (!$validator->validate('test-client@gmail.com'))) {
    echo 'Invalid email';
  }
?>
```

## Authors

### Original author
gabe@fijiwebdesign.com

http://www.webdigi.co.uk/blog/2009/how-to-check-if-an-email-address-exists-without-sending-an-email/

### GitHub author
Sebastien Lavoie <sebastien@lavoie.sl>

## License
http://creativecommons.org/licenses/by/2.0/
