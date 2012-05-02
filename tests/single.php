<?php

require_once __DIR__ . '/base.php';

class SMTP_Email_ValidatorSingleTest extends SMTP_Email_ValidatorTestBase {

  public function testValidEmail() {
    $is_valid = $this->instance->validate('support@github.com');
    $this->assertTrue($is_valid);
  }

  public function testInvalidEmail() {
    $is_valid = $this->instance->validate('non-existant-email@github.com');
    $this->assertFalse($is_valid);
  }
}