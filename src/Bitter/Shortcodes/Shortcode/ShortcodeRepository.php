<?php

namespace Bitter\Shortcodes\Shortcode;

use Bitter\Shortcodes\Entity\Shortcode;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Error\UserMessageException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ShortcodeRepository implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Shortcode::class);
    }

    /**
     * Get a Shortcode by id
     *
     * @param int $id
     *
     * @return Shortcode|null
     */
    public function find(int $id): ?Shortcode
    {
        return $this->repository->find($id);
    }

    /**
     * @throws UserMessageException
     */
    public function findOrFail(int $id): Shortcode
    {
        $shortcode = $this->find($id);

        if (!$shortcode) {
            throw new UserMessageException(t("This shortcode doesn't exist (anymore)."));
        }

        return $shortcode;
    }

    /**
     * Get a Shortcode by name / shortcode
     *
     * @param string $shortcode
     *
     * @return Shortcode|null
     */
    public function findByShortcode(string $shortcode): ?Shortcode
    {
        return $this->repository->findOneBy([
            'shortcode' => $shortcode,
        ]);
    }

    /**
     * @return Shortcode[]
     */
    public function all(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param Shortcode $shortcode
     */
    public function store(Shortcode $shortcode): void
    {
        $shortcode->touch();

        $this->entityManager->persist($shortcode);
        $this->entityManager->flush();
    }

    /**
     * @param Shortcode $shortcode
     *
     * @throws Exception
     */
    public function delete(Shortcode $shortcode): void
    {
        $db = $this->entityManager->getConnection();
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $db->executeQuery("DELETE FROM ShortcodesUsage WHERE shortcodeId = ?", [$shortcode->getId()]);
        $this->entityManager->remove($shortcode);
        $this->entityManager->flush();
    }
}
