<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Core\DataSource;


class RepositoryDataSource extends AbstractDataSource
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * RepositoryDataSource constructor.
     *
     * @param \Doctrine\ORM\EntityRepository $repository
     * @param array $findByOptions
     */
    protected $findByOptions;

    public function __construct(\Doctrine\ORM\EntityRepository $repository, $findByOptions = [])
    {
        $this->setRepository($repository);
        $this->setFindByOptions($findByOptions);
    }

    public function getCollection()
    {
        return $this->getRepository()->findAll();
    }

    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        return $this->getRepository()->findBy($this->findByOptions, null, $chunkSize, $offset);
    }

    public function getRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->repository;
    }

    public function setRepository(\Doctrine\ORM\EntityRepository $repository): RepositoryDataSource
    {
        $this->repository = $repository;
        return $this;
    }

    public function getFindByOptions(): array
    {
        return $this->findByOptions;
    }

    public function setFindByOptions(array $findByOptions): RepositoryDataSource
    {
        $this->findByOptions = $findByOptions;
        return $this;
    }
}