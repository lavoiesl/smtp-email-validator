<?php

namespace Lavoiesl\Validation\Email\SMTP\Test;
use Lavoiesl\Validation\Email\SMTP\Validator;
use PHPUnit_Framework_TestCase;

abstract class Base extends PHPUnit_Framework_TestCase {
  protected $instance;
  protected $from = 'me@localhost';

  protected function setUp() {
    $this->instance = new Validator($this->from);
  }

  protected function tearDown() {
    unset($this->instance);
  }
}