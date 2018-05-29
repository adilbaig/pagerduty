<?php

namespace PagerDuty\Context;

/**
 * Link
 *
 * @author adil
 */
class LinkContext extends Context
{

    public function __construct(string $href, string $text = null)
    {
        parent::__construct("link");
        $this->dict['href'] = $href;

        if (!empty($text)) {
            $this->dict['text'] = $text;
        }
    }
}
