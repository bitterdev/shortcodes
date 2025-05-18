<?php

namespace Concrete\Package\Shortcodes\Controller\SinglePage\Dashboard\Shortcodes;

use Bitter\Shortcodes\Entity\Shortcode;
use Bitter\Shortcodes\Shortcode\ShortcodeRepository;
use Bitter\Shortcodes\Usage\UsageRepository;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Page;
use Concrete\Core\User\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Response;

final class Search extends DashboardPageController
{
    public function view()
    {
        /** @var Repository $config */
        /** @noinspection PhpUnhandledExceptionInspection */
        $config = $this->app->make(Repository::class);
        $this->set('trackUsage', (bool)$config->get('shortcodes.track_usage', false));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->set('shortcodes', $this->getShortcodes());
    }

    public function add(): ?Response
    {
        $this->set('pageTitle', t('Add shortcode'));
        $this->set('shortcode', new Shortcode());
        $this->set('resolveStrategyOptions', $this->getResolveStrategyOptions());
        $this->render('/dashboard/shortcodes/search/edit');

        return null;
    }

    public function edit($id = 0): ?Response
    {
        $this->set('pageTitle', t('Edit shortcode'));
        /** @var ShortcodeRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(ShortcodeRepository::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->set('shortcode', $repository->findOrFail((int)$id));
        $this->set('resolveStrategyOptions', $this->getResolveStrategyOptions());
        $this->render('/dashboard/shortcodes/search/edit');

        return null;
    }

    public function usage($id = 0)
    {
        /** @var ShortcodeRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(ShortcodeRepository::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $shortcode = $repository->findOrFail((int)$id);

        /** @var UsageRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
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

        $this->render('/dashboard/shortcodes/search/usage');
    }

    /** @noinspection PhpUnused */
    public function clear_usage($id = null): Response
    {
        /** @var ResponseFactoryInterface $responseFactory */
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseFactory = $this->app->make(ResponseFactoryInterface::class);
        /** @var ShortcodeRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(ShortcodeRepository::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $shortcode = $repository->findOrFail((int)$id);

        /** @var UsageRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(UsageRepository::class);
        $repository->deleteByShortcode($shortcode);

        $this->flash('success', t('Usage for the shortcode has been cleared.'));

        return $responseFactory->redirect('/dashboard/shortcodes/search', Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * @throws UserMessageException
     * @throws BindingResolutionException
     */
    public function save(): ?Response
    {
        /** @var ResponseFactoryInterface $responseFactory */
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseFactory = $this->app->make(ResponseFactoryInterface::class);

        if (!$this->token->validate('save_shortcut')) {
            throw new UserMessageException($this->token->getErrorMessage());
        }

        /** @var ShortcodeRepository $repository */
        $repository = $this->app->make(ShortcodeRepository::class);

        if ($this->post('id')) {
            $shortcode = $repository->findOrFail($this->post('id'));
        } else {
            $shortcode = new Shortcode();
        }

        $shortcode->setIsActive((bool)$this->post('isActive'));
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
        } catch (ORMException|OptimisticLockException) {
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

        return $responseFactory->redirect('/dashboard/shortcodes/search', Response::HTTP_TEMPORARY_REDIRECT);
    }

    public function delete($id = null): Response
    {
        /** @var ShortcodeRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(ShortcodeRepository::class);
        /** @var ResponseFactoryInterface $responseFactory */
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseFactory = $this->app->make(ResponseFactoryInterface::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $shortcode = $repository->findOrFail($id);
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->delete($shortcode);

        $this->flash('success', t('Shortcode has been deleted successfully.'));

        return $responseFactory->redirect('/dashboard/shortcodes/search', Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * @return Shortcode[]|array
     * @throws BindingResolutionException
     */
    private function getShortcodes(): array
    {
        /** @var ShortcodeRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(ShortcodeRepository::class);

        /** @var Repository $config */
        /** @noinspection PhpUnhandledExceptionInspection */
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
                'updated_at' => $shortcode->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            if ((bool)$config->get('shortcodes.track_usage', false) === true) {
                $data['usage'] = $this->getUsageFor($shortcode);
            }

            $shortcodes[] = $data;
        }

        return $shortcodes;
    }

    /**
     * @return array
     */
    #[ArrayShape([Shortcode::RESOLVE_BY_STRING => "string", Shortcode::RESOLVE_BY_EVENT => "string", Shortcode::RESOLVE_BY_PHP => "string"])] protected function getResolveStrategyOptions(): array
    {
        $options = [
            Shortcode::RESOLVE_BY_STRING => t('Text value'),
            Shortcode::RESOLVE_BY_EVENT => t("The outcome of the '%s' event", 'on_shortcode_resolve'),
        ];

        // The PHP snippet strategy is only available for super admins
        $user = new User();

        if ($user->isSuperUser()) {
            $options[Shortcode::RESOLVE_BY_PHP] = t('The outcome of a PHP snippet');
        }

        return $options;
    }

    /**
     * Make sure the shortcode is always in a neat format
     *
     * @param string $shortcode
     *
     * @return string
     */
    protected function sanitizeShortcodeInput(string $shortcode): string
    {
        return str_replace(['[', ']',], '', str_replace(' ', '_', strtolower(trim($shortcode))));
    }

    private function getValueFromPost(): ?string
    {
        if ($this->post('resolveStrategy') === Shortcode::RESOLVE_BY_EVENT) {
            return null;
        }

        if ($this->post('resolveStrategy') === Shortcode::RESOLVE_BY_PHP) {
            return $this->post('phpValue');
        }

        return $this->post('stringValue');
    }

    protected function getUsageFor(Shortcode $shortcode): int
    {
        /** @var UsageRepository $repository */
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository = $this->app->make(UsageRepository::class);
        return $repository->getTotalFor($shortcode);
    }
}
