<?php

namespace PagerDuty\Exceptions;

/**
 * PagerDutyException
 *
 * @author adil
 */
class PagerDutyException extends \Exception
{

    protected $errors;

    public function __construct($message, array $errors)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
