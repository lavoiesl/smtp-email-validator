<?php

namespace Lavoiesl\Validation\Email\SMTP;

class Validator {

  /**
   * PHP Socket resource to remote MTA
   * @var resource $sock
   */
  private $sock;

  /**
   * Current User being validated
   */
  private $user;
  /**
   * Current domain where user is being validated
   */
  private $domain;
  /**
   * List of domains to validate users on
   */
  private $domains;
  /**
   * SMTP Port
   */
  private $port = 25;
  /**
   * Maximum Connection Time to an MTA
   */
  private $max_conn_time = 30;
  /**
   * Maximum time to read from socket
   */
  private $max_read_time = 5;

  /**
   * username of sender
   */
  private $from_user = 'user';
  /**
   * Host Name of sender
   */
  private $from_domain = 'localhost';

  /**
   * Nameservers to use when make DNS query for MX entries
   * @var Array $nameservers
   */
  private $nameservers = array(
    '8.8.8.8',
    '8.8.4.4',
  );

  public $debug = false;

  /**
   * Initializes the Class
   * @return SMTP_validateEmail Instance
   * @param $email Array[optional] List of Emails to Validate
   * @param $sender String[optional] Email of validator
   */
  public function __construct($sender = false) {
    if ($sender) {
      $this->setSenderEmail($sender);
    }
  }

  private function _parseEmail($email) {
    $parts = explode('@', $email);
    $domain = array_pop($parts);
    $user= implode('@', $parts);
    return array($user, $domain);
  }

  /**
   * Set the Emails to validate
   * @param mixed $emails array of emails or single email
   */
  public function setEmails($emails) {
    if (!is_array($emails)) {
      $emails = array($emails);
    }
    foreach($emails as $email) {
      list($user, $domain) = $this->_parseEmail($email);
      if (!isset($this->domains[$domain])) {
          $this->domains[$domain] = array();
      }
      $this->domains[$domain][] = $user;
    }
  }

  /**
   * Set the Email of the sender/validator
   * @param $email String
   */
  public function setSenderEmail($email) {
    $parts = $this->_parseEmail($email);
    $this->from_user = $parts[0];
    $this->from_domain = $parts[1];
  }

  public function validate($email) {
    $result = $this->batchValidate($email);
    return array_pop($result);
  }

  /**
  * Validate Email Addresses
  * @param mixed $emails Emails to validate (recipient emails)
  * @return Array Associative List of Emails and their validation results
  */
  public function batchValidate($emails = false) {

    $results = array();

    if ($emails) {
      $this->setEmails($emails);
    }

    // query the MTAs on each Domain
    foreach($this->domains as $domain => $users) {

      // retrieve SMTP Server via MX query on domain
      $mxs = $this->queryMX($domain);

      // last fallback is the original domain
      $mxs[$domain] = 100;

      $this->debug(print_r($mxs, 1));

      $timeout = $this->max_conn_time / count($mxs);

      // try each host
      while(list($host) = each($mxs)) {
        // connect to SMTP server
        $this->debug("try $host:$this->port\n");
        if ($this->sock = fsockopen($host, $this->port, $errno, $errstr, (float) $timeout)) {
          stream_set_timeout($this->sock, $this->max_read_time);
          break;
        }
      }

      // did we get a TCP socket
      if ($this->sock) {
        $reply = fread($this->sock, 2082);
        $this->debug("<<<\n$reply");

        preg_match('/^([0-9]{3}) /ims', $reply, $matches);
        $code = isset($matches[1]) ? $matches[1] : '';

        if($code != '220') {
          // MTA gave an error...
          foreach($users as $user) {
            $results[$user.'@'.$domain] = false;
          }
          continue;
        }

        // say helo
        $this->send("HELO ".$this->from_domain);
        // tell of sender
        $this->send("MAIL FROM: <".$this->from_user.'@'.$this->from_domain.">");

        // ask for each recepient on this domain
        foreach($users as $user) {

          // ask of recepient
          $reply = $this->send("RCPT TO: <".$user.'@'.$domain.">");

            // get code and msg from response
          preg_match('/^([0-9]{3}) /ims', $reply, $matches);
          $code = isset($matches[1]) ? $matches[1] : '';

          if ($code == '250') {
            // you received 250 so the email address was accepted
            $results[$user.'@'.$domain] = true;
          } elseif ($code == '451' || $code == '452') {
            // you received 451 so the email address was greylisted (or some temporary error occured on the MTA) - so assume is ok
            $results[$user.'@'.$domain] = true;
          } else {
            $results[$user.'@'.$domain] = false;
          }

        }

        // quit
        $this->send("quit");
        // close socket
        fclose($this->sock);

      }
    }
    return $results;
  }


  private function send($msg) {
    fwrite($this->sock, $msg."\r\n");

    $reply = fread($this->sock, 2082);

    $this->debug(">>>\n$msg\n");
    $this->debug("<<<\n$reply");

    return $reply;
  }

  /**
   * Query DNS server for MX entries
   * @return
   */
  public function queryMX($domain) {
    $hosts = array();
    $mxweights = array();
    if (function_exists('getmxrr')) {
      getmxrr($domain, $hosts, $mxweights);
    } else {
      // windows, we need Net_DNS: http://pear.php.net/package/Net_DNS
      require_once 'Net/DNS.php';

      $resolver = new Net_DNS_Resolver();
      $resolver->debug = $this->debug;
      // nameservers to query
      $resolver->nameservers = $this->nameservers;
      $resp = $resolver->query($domain, 'MX');
      if ($resp) {
        foreach($resp->answer as $answer) {
          $hosts[] = $answer->exchange;
          $mxweights[] = $answer->preference;
        }
      }

    }
    $mxs = array_combine($hosts, $mxweights);
    asort($mxs, SORT_NUMERIC);
    return $mxs;
  }

  private function debug($str) {
    if ($this->debug) {
      echo htmlentities($str);
    }
  }

}
