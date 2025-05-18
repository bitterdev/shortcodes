<?php

namespace Bitter\Shortcodes\Usage;

use Bitter\Shortcodes\Entity\Shortcode;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Page\Page;
use Exception;

class UsageTracker
{
    protected Repository $config;
    protected UsageRepository $usageRepository;

    public function __construct(
        Repository      $config,
        UsageRepository $usageRepository
    )
    {
        $this->config = $config;
        $this->usageRepository = $usageRepository;
    }

    /**
     * @param Page $page
     * @param Shortcode[] $shortcodes
     *
     * @return void
     */
    public function track(Page $page, array $shortcodes): void
    {
        // If there are no shortcodes, nothing needs to be tracked
        if (count($shortcodes) === 0) {
            return;
        }

        // Only track usage if a setting allows us to
        if (!$this->config->get('shortcodes.track_usage', false)) {
            return;
        }

        try {
            // Insert / update usage of each shortcode
            foreach ($shortcodes as $shortcode) {
                $this->usageRepository->createOrUpdate(
                    $page->getCollectionID(),
                    $shortcode
                );
            }

            $this->usageRepository->flush();
        } catch (Exception) {
            // Silently fail
        }
    }
}
