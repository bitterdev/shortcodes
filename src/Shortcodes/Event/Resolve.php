<?php

namespace A3020\Shortcodes\Event;

use A3020\Shortcodes\Entity\Shortcode;
use Symfony\Component\EventDispatcher\Event;

class Resolve extends Event
{
    /** @var Shortcode */
    protected $shortcode;

    /** @var string */
    protected $replacement;

    public function setShortcode(Shortcode $shortcodeEntity)
    {
        $this->shortcode = $shortcodeEntity;
    }

    /**
     * @param string $replacement
     */
    public function setReplacement($replacement)
    {
        $this->replacement = (string) $replacement;
    }

    /**
     * @return Shortcode
     */
    public function getShortcode()
    {
        return $this->shortcode;
    }

    public function getReplacement()
    {
        return $this->replacement;
    }
}
