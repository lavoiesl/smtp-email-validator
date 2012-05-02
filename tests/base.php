<?php

require_once dirname(__DIR__) . '/src/smtp-email-validator.php';
require_once 'PHPUnit/Autoload.php';

abstract class SMTP_Email_ValidatorTestBase extends PHPUnit_Framework_TestCase {
  protected $instance;
  protected $from = 'me@localhost';

  protected function setUp() {
    $this->instance = new SMTP_Email_Validator($this->from);
  }

  protected function tearDown() {
    unset($this->instance);
  }
}