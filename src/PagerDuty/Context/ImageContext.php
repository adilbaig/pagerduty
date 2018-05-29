<?php

namespace PagerDuty\Context;

/**
 * Image
 *
 * @author adil
 */
class ImageContext extends Context
{

    public function __construct(string $src, string $href = null, string $text = null)
    {
        parent::__construct("image");
        $this->dict['src'] = $src;
        
        if (!empty($href)) {
            $this->dict['href'] = $href;
        }

        if (!empty($text)) {
            $this->dict['text'] = $text;
        }
    }
}
