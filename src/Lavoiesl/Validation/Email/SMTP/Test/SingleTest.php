<?php

namespace Lavoiesl\Validation\Email\SMTP\Test;

class SingleTest extends Base {

  public function testValidEmail() {
    $is_valid = $this->instance->validate('support@github.com');
    $this->assertTrue($is_valid);
  }

  public function testInvalidEmail() {
    $is_valid = $this->instance->validate('non-existant-email@github.com');
    $this->assertFalse($is_valid);
  }
}