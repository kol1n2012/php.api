<?php

namespace App\Model\Sourse;

final class File
{

    /**
     * @var string
     */
    private string $entity;

    public function __construct(string $entity = '')
    {
        $this->setEntity($entity);

        $this->setCollection();
    }

    /**
     * @return void
     */
    protected function setCollection(): void
    {
        $entity = $this->getEntity();

        if (!mb_strlen($entity)) return;

        $collection = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $entity.'.json'), true) ?? [];

        $entity = 'App\\Model\\' . \ucwords($entity);

        foreach ($collection as $item) {
            $this->collection[] = $entity::convert($item);
        }
    }

    /**
     * @param string $entity
     * @return void
     */
    private function setEntity(string $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param array $filter
     * @return void
     */
    public function setFilter(array $filter = []): void
    {
        $collection = $this->getCollection();

        $collection = array_filter($collection, function ($item) use ($filter) {
            $symbolList = ['not' => '!'];

            $return = false;

            foreach ($filter as $key => $f) {
                if ($return) continue;

                $symbol = '';

                if (str_contains($key, $symbolList['not'])) {
                    $symbol = $symbolList['not'];
                }

                $value = null;

                switch ($key) {
                    case $symbol . 'id':
                        $value = $item->getId();
                        break;
                    case $symbol . 'name':
                        $value = $item->getName();
                        break;
                    case $symbol . 'email':
                        $value = $item->getEmail();
                        break;
                }

                if ($symbol === $symbolList['not']) {
                    if ($value !== $f || !in_array($value, is_array($f) ? $f : [$f])) {
                        $return = true;
                    }
                } else {
                    if ($value == $f || in_array($value, is_array($f) ? $f : [$f])) {
                        $return = true;
                    }
                }

            }

            return $return ?? false;
        });

        $collection = array_values($collection ? $collection : []);

        $this->collection = $collection;
    }

    /**
     * @param mixed $order
     * @return void
     */
    public function setOrder(mixed $order)
    {
    }

    /**
     * @param mixed $sort
     * @return void
     */
    public function setSort(mixed $sort)
    {
    }

    /**
     * @param int $limit
     * @return void
     */
    public function setLimit(int $limit)
    {
    }

    /**
     * @return array
     */
    public function getCollection(): array
    {
        return $this->collection ?? [];
    }

    /**
     * @return string
     */
    private function getEntity(): string
    {
        return $this->entity ?? "";
    }

    /**
     * @param $item
     * @return void
     */
    public function add($item): void
    {
        $this->collection[] = $item;

        $this->save();
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id = 0): void
    {
        $this->setFilter(["!id" => $id]);

        $this->save();
    }

    /**
     * @return void
     */
    private function save(): void
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $this->getEntity().'.json', "$this");
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