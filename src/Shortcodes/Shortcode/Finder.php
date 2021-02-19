<?php

namespace A3020\Shortcodes\Shortcode;

class Finder
{
    /**
     * Finds all shortcodes on a page
     *
     * Example shortcode: [[year]]
     *
     * @param string $html
     *
     * @return array
     */
    public function find($html)
    {
        preg_match_all('/\[\[(.*)\]\]/', $html, $matches);

        return $matches[1];
    }
}
