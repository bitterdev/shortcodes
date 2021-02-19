<?php

namespace Concrete\Package\Shortcodes\Controller\SinglePage\Dashboard\Shortcodes;

use A3020\Shortcodes\Entity\Shortcode;
use A3020\Shortcodes\Shortcode\ShortcodeRepository;
use A3020\Shortcodes\Usage\UsageRepository;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Page;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\User\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;

final class Search extends DashboardPageController
{
    public function on_before_render()
    {
        parent::on_before_render();

        $al = AssetList::getInstance();

        $al->register('javascript', 'shortcodes/datatables', 'js/datatables.min.js', [], 'shortcodes');
        $this->requireAsset('javascript', 'shortcodes/datatables');

        $al->register('css', 'shortcodes/datatables', 'css/datatables.css', [], 'shortcodes');
        $this->requireAsset('css', 'shortcodes/datatables');
    }

    public function view()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $this->set('trackUsage', (bool) $config->get('shortcodes.track_usage', false));
        $this->set('shortcodes', $this->getShortcodes());
    }
    
    public function add()
    {
        $this->set('pageTitle', t('Add shortcode'));
        $this->set('shortcode', new Shortcode());

        $this->addEdit();
    }

    public function edit($id = 0)
    {
        $this->set('pageTitle', t('Edit shortcode'));

        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);
        $this->set('shortcode', $repository->findOrFail($id));

        $this->addEdit();
    }

    public function usage($id = 0)
    {
        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);
        $shortcode = $repository->findOrFail($id);

        /** @var UsageRepository $repository */
        $repository = $this->app->make(UsageRepository::class);

        $pages = [];
        foreach ($repository->findByShortcode($shortcode) as $usage) {
            $page = Page::getByID($usage->getPageId());
            if (!is_object($page) || $page->isError()) {
                continue;
            }

            $pages[] = [
                'page_link' => $page->getCollectionLink(),
                'page_name' => $page->getCollectionName(),
                'found_at' => $usage->getActiveAt()
                    ->format('Y-m-d H:i:s'),
            ];
        }

        $this->set('pageTitle', t("Usage for shortcode '%s'", $shortcode->getShortcode()));
        $this->set('shortcode', $shortcode);
        $this->set('pages', $pages);

        $this->render('/dashboard/shortcodes/usage');
    }

    public function clearUsage($id = null)
    {
        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);
        $shortcode = $repository->findOrFail($id);

        /** @var UsageRepository $repository */
        $repository = $this->app->make(UsageRepository::class);
        $repository->deleteByShortcode($shortcode);

        $this->flash('success', t('Usage for the shortcode has been cleared.'));

        return Redirect::to('/dashboard/shortcodes/search');
    }

    protected function addEdit()
    {
        $this->render('/dashboard/shortcodes/add_edit');
        $this->set('resolveStrategyOptions', $this->getResolveStrategyOptions());
    }

    public function save()
    {
        if (!$this->token->validate('a3020.shortcodes.add_edit')) {
            throw new UserMessageException($this->token->getErrorMessage());
        }

        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);

        if ($this->post('id')) {
            $shortcode = $repository->findOrFail($this->post('id'));
        } else {
            $shortcode = new Shortcode();
        }

        $shortcode->setIsActive((bool) $this->post('isActive'));
        $shortcode->setShortcode($this->sanitizeShortcodeInput($this->post('shortcode')));
        $shortcode->setValue($this->getValueFromPost());

        // Make sure a valid resolve strategy is chosen
        if (array_key_exists($this->post('resolveStrategy'), $this->getResolveStrategyOptions())) {
            $shortcode->setResolveStrategy($this->post('resolveStrategy'));
        } else {
            // Non-super admins are not allowed to add / edit PHP snippets, for example.
            $this->flash('error', t('You are not allowed to edit this shortcode.'));

            return $this->post('id') ? $this->edit() : $this->add();
        }

        try {
            $repository->store($shortcode);
        } catch (UniqueConstraintViolationException $e) {
            $this->flash('error', t('This shortcode already exists! The shortcode needs to be unique.'));

            return $this->post('id') ? $this->edit() : $this->add();
        } catch (Exception $e) {
            $this->flash('error', t('Something went wrong: %s', $e->getMessage()));

            return $this->post('id') ? $this->edit() : $this->add();
        }

        if ($this->post('id')) {
            $this->flash('success', t('Shortcode has been saved successfully.'));
        } else {
            $this->flash('success', t('Shortcode has been added successfully.'));
        }

        return Redirect::to('/dashboard/shortcodes/search');
    }

    public function delete($id = null)
    {
        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);
        $shortcode = $repository->findOrFail($id);

        $repository->delete($shortcode);

        $this->flash('success', t('Shortcode has been deleted successfully.'));

        return Redirect::to('/dashboard/shortcodes/search');
    }
    
    /**
     * @return array
     */
    private function getShortcodes()
    {
        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);

        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $user = new User();

        $shortcodes = [];
        foreach ($repository->all() as $shortcode) {
            $canEdit = true;
            if ($shortcode->getResolveStrategy() === Shortcode::RESOLVE_BY_PHP && !$user->isSuperUser()) {
                $canEdit = false;
            }

            $data = [
                'id' => $shortcode->getId(),
                'can_edit' => $canEdit,
                'shortcode' => $shortcode->getDisplayShortcode(),
                'strategy' => $shortcode->getResolveStrategy(),
                'value' => $shortcode->getDisplayValue(),
                'is_active' => $shortcode->isActive(),
                'updated_at' => $shortcode->getUpdatedAt()
                    ->format('Y-m-d H:i:s'),
            ];

            if ((bool) $config->get('shortcodes.track_usage', false) === true) {
                $data['usage'] = $this->getUsageFor($shortcode);
            }

            $shortcodes[] = $data;
        }

        return $shortcodes;
    }

    /**
     * @return array
     */
    private function getResolveStrategyOptions()
    {
        $options = [
            Shortcode::RESOLVE_BY_STRING => t('Text value'),
            Shortcode::RESOLVE_BY_EVENT => t("The outcome of the '%s' event", 'on_shortcode_resolve'),
        ];

        // The PHP snippet strategy is only available for super admins
        $user = new User();
        if ($user->isSuperUser()) {
            $options[Shortcode::RESOLVE_BY_PHP] = t('The outcome of a PHP snippet');
        };

        return $options;
    }

    /**
     * Make sure the shortcode is always in a neat format
     *
     * @param string $shortcode
     *
     * @return string
     */
    private function sanitizeShortcodeInput($shortcode)
    {
        $shortcode = strtolower(trim($shortcode));
        $shortcode = str_replace(' ', '_', $shortcode);
        $shortcode = str_replace([
            '[',
            ']',
        ], '', $shortcode);

        return $shortcode;
    }

    private function getValueFromPost()
    {
        // Shortcodes that are resolved via an event, don't have a value
        if ($this->post('resolveStrategy') === Shortcode::RESOLVE_BY_EVENT) {
            return null;
        }

        if ($this->post('resolveStrategy') === Shortcode::RESOLVE_BY_PHP) {
            return $this->post('phpValue');
        }

        return $this->post('stringValue');
    }

    private function getUsageFor(Shortcode $shortcode)
    {
        /** @var UsageRepository $repository */
        $repository = $this->app->make(UsageRepository::class);

        return $repository->getTotalFor($shortcode);
    }
}
