<?php

namespace App\Model\Sourse;

class MySQL
{
    /**
     * @var \mysqli
     */
    private $handler;

    /**
     * @var string
     */
    private string $sourse;

    /**
     * @var string
     */
    private string $sql;

    /**
     * @param string $sourse
     * @return void
     */
    protected function setCollection(string $sourse = ''): void
    {
        if (!mb_strlen($sourse)) return;

        $this->sourse = $sourse;

        $this->sql = "SELECT * FROM $sourse";
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
    protected function setFilter(array $filter = []): void
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
        $this->collection = [];

        if($collection = ($this->getHandler())->query($this->sql)){
            foreach ($collection as $item) {
                $this->collection[] = $this->__convert($item);
            }
        }

        return $this->collection ?? [];
    }


    /**
     * @param $data
     * @return void
     */
    protected function __add($data): void
    {
        $data = $data->getValidData();

        if(isset($data['id'])){
            unset($data['id']);
        }

        $sql = sprintf("INSERT INTO %s ", $this->sourse);

        $keys = implode(',',array_keys($data));
        $values = implode("','",array_values($data));
        $values = "'$values'";

        $sql .= "($keys) VALUES ($values)";

        ($this->getHandler())->query($sql);
    }

    /**
     * @param int $id
     * @return void
     */
    protected function __delete(int $id = 0): void
    {
        if(!$id) return;

        $sql = sprintf("DELETE FROM %s WHERE id = %d", $this->sourse, $id);

        ($this->getHandler())->query($sql);
    }
}