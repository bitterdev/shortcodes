<?php

namespace Bitter\Shortcodes\Shortcode;

class Finder
{
    /**
     * Finds all shortcodes on a page
     *
     * @param string $html
     *
     * @return array
     * @example INPUT:'Copyright [[year]] by [[company]].'
     * @example OUTPUT: ['year', 'company']
     *
     */
    public function find(string $html): array
    {
        preg_match_all('/\[\[(.*?)]]/', $html, $matches);

        return $matches[1];
    }
}
