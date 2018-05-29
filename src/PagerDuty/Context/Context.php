<?php

namespace PagerDuty\Context;

/**
 * A basic Context
 *
 * @author adil
 */
abstract class Context implements \JsonSerializable
{

    protected $dict;

    protected function __construct(string $type)
    {
        $this->dict['type'] = $type;
    }

    /**
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->dict;
    }

    public function jsonSerialize(): array
    {
        return $this->dict;
    }
}
