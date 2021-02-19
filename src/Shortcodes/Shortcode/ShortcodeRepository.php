<?php

namespace A3020\Shortcodes\Shortcode;

use A3020\Shortcodes\Entity\Shortcode;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Error\UserMessageException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ShortcodeRepository implements ApplicationAwareInterface
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
     * Get a Shortcode by id
     *
     * @param int $id
     *
     * @return Shortcode|null
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findOrFail($id)
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
    public function findByShortcode($shortcode)
    {
        return $this->repository->findOneBy([
            'shortcode' => $shortcode,
        ]);
    }

    /**
     * @return Shortcode[]
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param Shortcode $shortcode
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function store(Shortcode $shortcode)
    {
        $shortcode->touch();

        $this->entityManager->persist($shortcode);
        $this->entityManager->flush();
    }

    /**
     * @param Shortcode $shortcode
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Shortcode $shortcode)
    {
        $this->entityManager->remove($shortcode);
        $this->entityManager->flush();
    }
}
