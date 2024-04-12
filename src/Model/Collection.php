<?php

namespace App\Model;

use App\Model\Sourse\Mysql;
use App\Model\Sourse\File;

abstract class Collection
{
    /**
     * @var array
     */
    protected array $filter = [];

    /**
     * @var array
     */
    protected array $sort = [];

    /**
     * @var array
     */
    protected array $order = [];

    /**
     * @var array
     */
    protected array $collection = [];

    /**
     * @var string
     */
    protected string $sourse = '';

    /**
     * @var string
     */
    protected string $entity = '';

    /**
     * @var File|Mysql
     */
    protected File|Mysql $storage;

    /**
     * @param array $query
     */
    public function __construct(array $query = [])
    {
        $this->setStorage();

        if ($filter = @$query['filter']) {
            $this->getStorage()->setFilter($filter);
        }

        //TODO select collection
        if ($select = @$query['select']) {
            $this->getStorage()->setSelect($select);
        }

        //TODO sort collection
        if ($sort = @$query['sort']) {
            $this->getStorage()->setSort($sort);
        }

        //TODO limit collection
        if ($limit = @$query['limit']) {
            $this->getStorage()->setLimit($limit);
        }

        $collection = $this->getStorage()->getCollection();

        $this->setCollection($collection);
    }

    /**
     * @param array $collection
     * @return void
     */
    private function setCollection(array $collection = []): void
    {
        $this->collection = $collection;
    }

    /**
     * @param string $sourse
     * @return void
     */
    protected function setSourse(string $sourse = ''): void
    {
        if (!mb_strlen($sourse)) return;

        $sourse = 'App\\Model\\Sourse\\' . \ucwords($sourse);

        $this->sourse = $sourse;
    }

    /**
     * @param string $entity
     * @return void
     */
    protected function setEntity(string $entity = ''): void
    {
        $this->entity = $entity;
    }

    /**
     * @return void
     */
    private function setStorage(): void
    {
        $sourse = $this->getSourse();

        $entity = $this->getEntity();

        if (!mb_strlen($sourse)) return;

        $this->storage = new $sourse($entity);
    }

    /**
     * @return string
     */
    private function getSourse(): string
    {
        return $this->sourse ?? "";
    }

    /**
     * @return string
     */
    private function getEntity(): string
    {
        return $this->entity ?? "";
    }

    /**
     * @return File|Mysql
     */
    protected function getStorage(): File|Mysql
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function getCollection(): array
    {
        return $this->collection ?? [];
    }

    /**
     * @param $data
     * @return void
     */
    public function add($data): void
    {
        $this->getStorage()->add($data);
    }

    /**
     * @param $data
     * @return void
     */
    public function delete($data): void
    {
        $this->getStorage()->delete($data->getId());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $collection = $this->getCollection();

        $collection = implode(',', $collection);

        return "[$collection]";
    }
}