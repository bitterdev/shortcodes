<?php

namespace A3020\Shortcodes\Shortcode;

use A3020\Shortcodes\Entity\Shortcode;

class Replacer
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var ShortcodeRepository
     */
    private $shortcodeRepository;

    private $resolved = [];

    /**
     * @var Resolver
     */
    private $shortcodeResolver;

    public function __construct(Finder $finder, ShortcodeRepository $shortcodeRepository, Resolver $shortcodeResolver)
    {
        $this->finder = $finder;
        $this->shortcodeRepository = $shortcodeRepository;
        $this->shortcodeResolver = $shortcodeResolver;
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function findAndReplace($html)
    {
        foreach ($this->finder->find($html) as $shortcode) {
            $html = $this->replace($html, $shortcode);
        }

        return $html;
    }

    /**
     * Get a list of resolved shortcodes
     *
     * @return Shortcode[]
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    /**
     * Replace a shortcode in the body text
     *
     * @param string $html
     * @param string $shortcode
     *
     * @return string
     */
    private function replace($html, $shortcode)
    {
        $replacement = $this->getReplacement($shortcode);
        if ($replacement === null) {
            return $html;
        }

        $html = str_replace('[['.$shortcode.']]', $replacement, $html);

        return $html;
    }

    /**
     * @param string $shortcode
     *
     * @return string|null
     */
    private function getReplacement($shortcode)
    {
        // Get shortcode from the database
        $shortcodeEntity = $this->shortcodeRepository->findByShortcode($shortcode);

        if (!$shortcodeEntity) {
            return null;
        }

        if (!$shortcodeEntity->isActive()) {
            return null;
        }

        $replacement = $this->shortcodeResolver->resolve($shortcodeEntity);
        if ($replacement === null) {
            return null;
        }

        $this->resolved[] = $shortcodeEntity;

        return $replacement;
    }
}
