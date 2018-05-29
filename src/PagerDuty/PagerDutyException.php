<?php

namespace PagerDuty;

/**
 * PagerDutyException
 *
 * @author adil
 */
class PagerDutyException extends \Exception
{

    protected $errors;

    public function __construct(string $message, array $errors)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
