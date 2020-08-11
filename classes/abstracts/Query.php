<?php
declare(strict_types = 1);
namespace abstracts;

include './classes/interfaces/DatabaseWrapper.php';

abstract class Query implements \interfaces\DatabaseWrapper
{
    /**
     * @var \SQLite3 объект базы данных
     */
    protected $base;
    /**
     * @var string $table имя класса = имя таблицы
     */
    protected $table;

    public function __construct() {
        $classarray = explode('\\', get_class($this));
        $this->table = lcfirst(end($classarray));
        $this->base = new \SQLite3(DB_NAME);
    }
    /**
     * @description вставляет новую запись в таблицу, возвращает полученный объект как массив
     * @param array $tableColumns
     * @param array $values
     * @return array
     */

    public function insert(array $tableColumns, array $values): array
    {
        $tableCol = implode(', ', array_map( function (&$elemArr1)
        {
            return "'$elemArr1'";
        }, $tableColumns));

        $valuesInsert = implode(', ', array_map( function (&$elemArr1)
        {
            return gettype($elemArr1) === 'string' ? "'$elemArr1'" : $elemArr1;
        }, $values));

        $query = "INSERT INTO $this->table ($tableCol) VALUES ($valuesInsert)";
        $prepare = $this->base->prepare($query);
        $result = $prepare->execute();

        return array($result); // вот тут не совсем понял, что конкретно нужно вернуть
    }

    /**
     * @description редактирует строку под конкретным id, возвращает результат после изменения
     * @param int $id
     * @param array $values
     * @return array
     */
    public function update(int $id, array $values): array
    {
        $querySelect = "SELECT * FROM $this->table";
        $valueOfArray = $this->base
            ->prepare($querySelect)
            ->execute()
            ->fetchArray();
        $arrayWithNameColumn = array();
        foreach($valueOfArray as $key => $value) {
            if (gettype($key) === "string") $arrayWithNameColumn[] = $key;
        }
        $resultMap = implode(', ',
            array_map(
                function(&$keyArr1, $keyArr2) {
                    if(gettype($keyArr2) === 'string') {
                        return "$keyArr1 = '$keyArr2'";
                    }
                    return "$keyArr1 = $keyArr2";
                }, $arrayWithNameColumn, $values)
        );
        $query = "UPDATE $this->table SET $resultMap WHERE id = $id";
        $this->base
            ->prepare($query)
            ->execute();
        return $this->base
            ->prepare("SELECT * FROM $this->table WHERE id=$values[0]")
            ->execute()
            ->fetchArray();
    }

    /**
     * @description поиск по id
     * @param int $id
     * @return array
     */
    public function find(int $id): array
    {
        $query = "SELECT * FROM $this->table WHERE id=$id";
        return [$this->base
            ->prepare($query)
            ->execute()
            ->fetchArray()];
    }

    /**
     * @description удаляет по id
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $validate = $this->find($id);
        if(!$validate[0]) {
            throw new \Exception("id = $id в таблице $this->table не существует!");
        };
        $query = "DELETE FROM $this->table WHERE id=$id";
        $this->base
            ->prepare($query)
            ->execute();
        return true;
    }

    /**
     * нижние методы писал для практики.
     */


    /**
     * @names Есть ли указанная таблица
     * @param string $tableName
     * @return bool
     */
    public function tableIsExist( string $tableName ): bool
    {
        $query = "PRAGMA table_info($tableName)";
        $prepare = $this->base->prepare($query)->execute();
        if(!$prepare->fetchArray()) return false;
        return true;
    }

    /**
     * @names есть ли указанный столбец в таблице
     * @param string $tableName
     * @param string $colName
     * @return bool
     */
    public function columnIsExist(string $tableName, string $colName )
    {
        $query = "SELECT * FROM $tableName";
        $prepare = $this->base->prepare($query)->execute();
        return array_key_exists($colName, $prepare->fetchArray());
    }
}