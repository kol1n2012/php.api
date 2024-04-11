<?php

namespace App\Model\Sourse;

class File
{
    private string $fileName;

    /**
     * @param string $fileName
     * @return void
     */
    protected function setCollection(string $fileName = ''): void
    {
        if (!mb_strlen($fileName)) return;

        $this->fileName = $fileName;

        $collection = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $fileName), true) ?? [];

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
     * @return array
     */
    public function getCollection(): array
    {
        return $this->collection ?? [];
    }

    /**
     * @return void
     */
    public function __save(): void
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $this->fileName, "$this");
    }
}