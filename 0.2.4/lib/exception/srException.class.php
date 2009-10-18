<?php
class srException extends sfException
{
  public function __construct($message = "" , $code = 0)
  {
    srLog::add($this);
    parent::__construct($message, $code);
  }

}