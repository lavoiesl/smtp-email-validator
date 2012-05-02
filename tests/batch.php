<?php

require_once __DIR__ . '/base.php';

class SMTP_Email_ValidatorBatchTest extends SMTP_Email_ValidatorTestBase {

  private function batch($emails) {
    $results = $this->instance->batchValidate(array_keys($emails));

    $this->assertEquals(count($emails), count($results));

    foreach ($emails as $email => $is_valid) {
      $this->assertEquals($results[$email], $is_valid);
    }
  }

  public function testValidEmails() {
    $emails = array(
      'training@github.com' => true,
      'support@github.com' => true,
    );

    $this->batch($emails);
  }

  public function testSomeValidEmails() {
    $emails = array(
      'non-existant-email@github.com' => false,
      'support@github.com' => true,
    );

    $this->batch($emails);
  }

  public function testNoValidEmails() {
    $emails = array(
      'non-existant-email@github.com' => false,
      'other-non-existant-email@github.com' => false,
    );

    $this->batch($emails);
  }
}