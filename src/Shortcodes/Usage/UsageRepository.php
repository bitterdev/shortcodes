<?php

namespace A3020\Shortcodes\Usage;

use A3020\Shortcodes\Entity\Shortcode;
use A3020\Shortcodes\Entity\Usage;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class UsageRepository implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityRepository $repository, EntityManager $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Usage[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param int $pageId
     * @param Shortcode $shortcode
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createOrUpdate($pageId, Shortcode $shortcode)
    {
        $usage = $this->repository->findOneBy([
            'pageId' => $pageId,
            'shortcode' => $shortcode,
        ]);

        if (!$usage) {
            $new = true;
            $usage = new Usage();
            $usage->setPageId((int)$pageId);
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteByPage($pageId)
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
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteByShortcode(Shortcode $shortcode)
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
     *
     * @return Usage[]
     */
    public function findByShortcode(Shortcode $shortcode, $limit = 1000)
    {
        return $this->repository->findBy([
            'shortcode' => $shortcode,
        ], null, $limit);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * Get the number of pages this shortcode is on
     *
     * @param $shortcode
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalFor(Shortcode $shortcode)
    {
        $qb = $this->repository
            ->createQueryBuilder('s');

        return (int) $qb->select($qb->expr()->count('s.id'))
            ->where('s.shortcode = :shortcode')
            ->setParameter('shortcode', $shortcode)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
