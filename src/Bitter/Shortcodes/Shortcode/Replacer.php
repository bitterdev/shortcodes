<?php

namespace Bitter\Shortcodes\Shortcode;

use Bitter\Shortcodes\Entity\Shortcode;

class Replacer
{
    protected Finder $finder;
    protected ShortcodeRepository $shortcodeRepository;
    protected array $resolved = [];
    protected Resolver $shortcodeResolver;

    public function __construct(
        Finder              $finder,
        ShortcodeRepository $shortcodeRepository,
        Resolver            $shortcodeResolver
    )
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
    public function findAndReplace(string $html): string
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
    public function getResolved(): array
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
    private function replace(string $html, string $shortcode): string
    {
        $replacement = $this->getReplacement($shortcode);
        if ($replacement === null) {
            return $html;
        }

        return str_replace('[[' . $shortcode . ']]', $replacement, $html);
    }

    /**
     * @param string $shortcode
     *
     * @return string|null
     */
    private function getReplacement(string $shortcode): ?string
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
