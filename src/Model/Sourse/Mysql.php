<?php

namespace App\Model\Sourse;

final class Mysql
{
    /**
     * @var \mysqli
     */
    private $handler;

    /**
     * @var string
     */
    private string $entity;

    /**
     * @var string
     */
    private string $sql;

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

        $this->sql = "SELECT * FROM $entity";
    }

    /**
     * @param string $entity
     * @return void
     */
    private function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return \mysqli
     */
    private function getHandler(): \mysqli
    {
        if(!$this->handler){
            $MYSQL_HOST = @getenv('MYSQL_HOST');
            $MYSQL_DB = @getenv('MYSQL_DB');
            $MYSQL_LOGIN = @getenv('MYSQL_LOGIN');
            $MYSQL_PASSWORD = @getenv('MYSQL_PASSWORD');

            $this->handler = new \mysqli($MYSQL_HOST, $MYSQL_LOGIN, $MYSQL_PASSWORD, $MYSQL_DB);
        }

        return $this->handler;
    }

    /**
     * @param array $filter
     * @return void
     */
    public function setFilter(array $filter = []): void
    {
        if(count($filter)){

            $sql = $this->sql;

            $symbolList = ['not' => '!'];

            $sql .= ' WHERE 1=1';

            foreach ($filter as $key => $f) {

                $f = is_array($f) ? $f : [$f];

                $symbol = '';

                if (str_contains($key, $symbolList['not'])) {
                    $symbol = ' NOT';
                    $key = str_replace($symbolList['not'], '', $key);
                }

                $sql .= " and $key$symbol IN (".\implode(',', $f).")";
            }

            $this->sql = $sql;
        }
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
        $this->collection = [];

        $entity = $this->getEntity();
        $entity = 'App\\Model\\' . \ucwords($entity);

        if($collection = ($this->getHandler())->query($this->sql)){
            foreach ($collection as $item) {
                $this->collection[] = is_array($item) ? $entity::convert($item): $item;
            }
        }

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
     * @param $data
     * @return void
     */
    public function add($data): void
    {
        $data = $data->getValidData();

        if(isset($data['id'])) unset($data['id']);

        $keys = implode(',',array_keys($data));

        $values = implode("','",array_values($data));

        $sql = sprintf("INSERT INTO %s (%s) VALUES ('%s')", $this->entity, $keys, $values);

        ($this->getHandler())->query($sql);
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id = 0): void
    {
        if(!$id) return;

        $sql = sprintf("DELETE FROM %s WHERE id = %d", $this->entity, $id);

        ($this->getHandler())->query($sql);
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
