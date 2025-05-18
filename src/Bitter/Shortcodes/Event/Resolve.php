<?php

namespace Bitter\Shortcodes\Event;

use Bitter\Shortcodes\Entity\Shortcode;
use Symfony\Component\EventDispatcher\GenericEvent;

class Resolve extends GenericEvent
{
    protected Shortcode $shortcode;
    protected string $replacement;

    /**
     * @return Shortcode
     */
    public function getShortcode(): Shortcode
    {
        return $this->shortcode;
    }

    /**
     * @param Shortcode $shortcode
     */
    public function setShortcode(Shortcode $shortcode): void
    {
        $this->shortcode = $shortcode;
    }

    /**
     * @return string
     */
    public function getReplacement(): string
    {
        return $this->replacement;
    }

    /**
     * @param string $replacement
     */
    public function setReplacement(string $replacement): void
    {
        $this->replacement = $replacement;
    }
}
