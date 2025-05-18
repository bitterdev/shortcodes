<?php

namespace Bitter\Shortcodes\Usage;

use Bitter\Shortcodes\Entity\Shortcode;
use Bitter\Shortcodes\Entity\Usage;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UsageRepository implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityRepository       $repository,
        EntityManagerInterface $entityManager
    )
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Usage[]
     */
    public function all(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param int $pageId
     * @param Shortcode $shortcode
     */
    public function createOrUpdate(int $pageId, Shortcode $shortcode): void
    {
        $usage = $this->repository->findOneBy([
            'pageId' => $pageId,
            'shortcode' => $shortcode,
        ]);

        $new = false;

        if (!$usage instanceof Usage) {
            $new = true;
            $usage = new Usage();
            $usage->setPageId($pageId);
            $usage->setShortcode($shortcode);
        }

        $usage->touch();

        $this->entityManager->persist($usage);

        if ($new) {
            // If records are new, we'll flush them right away
            // otherwise we might get Duplicate entry problems
            // when there are more of the same shortcodes on one page
            $this->flush();
        }
    }

    /**
     * Delete shortcode usage of a concrete5 page
     *
     * @param int $pageId
     */
    public function deleteByPage(int $pageId): void
    {
        foreach ($this->repository->findBy([
            'pageId' => $pageId,
        ]) as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->flush();
    }

    /**
     * Delete all usage records of a certain shortcode
     *
     * @param Shortcode $shortcode
     */
    public function deleteByShortcode(Shortcode $shortcode): void
    {
        foreach ($this->repository->findBy([
            'shortcode' => $shortcode,
        ]) as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->flush();
    }

    /**
     * @param Shortcode $shortcode
     * @param int $limit
     * @return Usage[]
     */
    public function findByShortcode(Shortcode $shortcode, int $limit = 1000): array
    {
        return $this->repository->findBy([
            'shortcode' => $shortcode,
        ], null, $limit);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * Get the number of pages this shortcode is on
     *
     * @param Shortcode $shortcode
     *
     * @return int
     */
    public function getTotalFor(Shortcode $shortcode): int
    {
        $qb = $this->repository
            ->createQueryBuilder('s');

        try {
            return (int)$qb->select($qb->expr()->count('s.id'))
                ->where('s.shortcode = :shortcode')
                ->setParameter('shortcode', $shortcode)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }
}
