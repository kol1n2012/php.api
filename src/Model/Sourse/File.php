<?php

namespace App\Model\Sourse;

class File
{
    /**
     * @var string
     */
    private string $sourse;

    /**
     * @param string $sourse
     * @return void
     */
    protected function setCollection(string $sourse = ''): void
    {
        if (!mb_strlen($sourse)) return;

        $this->sourse = $sourse;

        $collection = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $sourse.'.json'), true) ?? [];

        foreach ($collection as $item) {
            $this->collection[] = $this->__convert($item);
        }

    }

    /**
     * @param array $filter
     * @return void
     */
    protected function setFilter(array $filter = []): void
    {
        $collection = $this->collection;

        $collection = array_filter($collection, function ($item) use ($filter) {

            $return = false;

            $symbolList = ['not' => '!'];

            foreach ($filter as $key => $f) {
                if ($return) continue;

                $symbol = '';

                if (str_contains($key, $symbolList['not'])) {
                    $symbol = $symbolList['not'];
                }

                $value = '';

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
    protected function setOrder(mixed $order)
    {
    }

    /**
     * @param mixed $sort
     * @return void
     */
    protected function setSort(mixed $sort)
    {
    }

    /**
     * @param int $limit
     * @return void
     */
    protected function setLimit(int $limit)
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
     * @param $item
     * @return void
     */
    protected function __add($item): void
    {
        $this->collection[] = $item;

        $this->__save();
    }

    /**
     * @param int $id
     * @return void
     */
    protected function __delete(int $id = 0): void
    {
        $this->setFilter(['!id' => $id]);

        $this->__save();
    }

    /**
     * @return void
     */
    private function __save(): void
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $this->sourse.'.json', "$this");
    }
}