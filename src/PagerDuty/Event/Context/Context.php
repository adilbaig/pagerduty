<?php

namespace PagerDuty\Event\Context;

/**
 * A basic Context
 *
 * @author adil
 */
abstract class Context implements \JsonSerializable
{

    protected $dict;

    protected function __construct($type)
    {
        $this->dict['type'] = $type;
    }

    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->dict;
    }

    public function jsonSerialize()
    {
        return $this->dict;
    }
}
