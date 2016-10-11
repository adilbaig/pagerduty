<?php

namespace PagerDuty\Event\Context;

/**
 * Image
 *
 * @author adil
 */
class ImageContext extends Context
{

    public function __construct($src, $href = null, $text = null)
    {
        parent::__construct("link");
        $this->dict['src'] = (string) $src;
        
        if (!empty($href)) {
            $this->dict['href'] = (string) $href;
        }

        if (!empty($text)) {
            $this->dict['text'] = (string) $text;
        }
    }
}
