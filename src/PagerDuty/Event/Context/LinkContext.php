<?php

namespace PagerDuty\Event\Context;

/**
 * Link
 *
 * @author adil
 */
class LinkContext extends Context
{

    public function __construct($href, $text = null)
    {
        parent::__construct("link");
        $this->dict['href'] = (string) $href;

        if (!empty($text)) {
            $this->dict['text'] = (string) $text;
        }
    }
}
