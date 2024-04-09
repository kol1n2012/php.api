<?php

namespace App;

class CollectionFile
{
    /**
     * @param string $fileName
     * @return void
     */
    protected function setCollection(string $fileName = ''): void
    {
        if (!mb_strlen($fileName)) return;

        $collection = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $fileName), true) ?? [];

        foreach ($collection as $item){
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

            foreach ($filter as $key => $f) {
                if ($return) continue;

                $value = '';

                switch ($key){
                    case 'id':
                        $value = $item->getId();
                        break;
                    case 'name':
                        $value = $item->getName();
                        break;
                    case 'email':
                        $value = $item->getEmail();
                        break;
                }

                if ($value == $f || in_array($value, is_array($f) ? $f : [$f])) {
                    $return = true;
                }
            }

            return $return ?? false;
        });

        $collection = array_values($collection ? $collection : []);

        $this->collection = $collection;
    }

    protected function setOrder(mixed $order)
    {
    }

    protected function setSort(mixed $sort)
    {
    }

    public function getCollection(): array
    {
        return $this->collection ?? [];
    }
}