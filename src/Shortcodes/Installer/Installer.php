<?php

namespace A3020\Shortcodes\Installer;

use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;
use Doctrine\ORM\EntityManager;

class Installer
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Concrete\Core\Package\Package $pkg
     */
    public function install($pkg)
    {
        $pages = [
            '/dashboard/shortcodes' => t('Shortcodes'),
            '/dashboard/shortcodes/search' => t('Search'),
            '/dashboard/shortcodes/settings' => t('Settings'),
        ];

        // Using for loop because additional pages
        // may be added in the future.
        foreach ($pages as $path => $name) {
            /** @var Page $page */
            $page = Page::getByPath($path);
            if ($page && !$page->isError()) {
                continue;
            }

            $singlePage = Single::add($path, $pkg);
            $singlePage->update([
                'cName' => $name,
            ]);
        }
    }
}
