<?php
/**
 * @team BMA <phpbma@corp.badoo.com>
 * @maintainer Dmitry Semenihin <d.semenihin@corp.badoo.com>
 */
include_once 'MysqlDriverAbstract.php';

class MysqliMysqlDriver extends MysqlDriverAbstract {
    /**
     * @var PDOStatement
     */
    protected $_dbh;

    public function __construct(array $params)
    {
        $this->_dbh = new mysqli($params['host'], $params['user'], $params['password'], $params['database'], $params['port']);
        if ($this->_dbh->connect_errno) {
            throw new Exception('Не удалось подключиться к MySQL: ' . $this->_dbh->connect_error);
        }
    }

    public function query($sql, $fetchType = null)
    {
        /**
         * @var mysqli_result
         */
        $result = $this->_dbh->query($sql);

        if ($this->_dbh->connect_errno) {
            throw new Exception('Mysqli error: ' . $this->_dbh->connect_error. '; Query: [' . $sql . ']');
        }

        if (is_bool($result)) {
            return $result;
        }

        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return $result->fetch_all(MYSQLI_ASSOC);

            case self::FETCH_NUM:
                return $result->fetch_all();

            case self::FETCH_COLUMN:
                return array_map(function ($item) { return $item[0]; }, $result->fetch_all(MYSQLI_NUM));

            default:
                return $result->fetch_all();
        }
    }

    public function beginTransaction()
    {
        return $this->_dbh->begin_transaction();
    }

    public function commit()
    {
        return $this->_dbh->commit();
    }

    public function rollback()
    {
        return $this->_dbh->rollback();
    }

    public function escape($str, $escapeType = self::ESCAPE_STR)
    {
        return "'" . $this->_dbh->real_escape_string($str) . "'";
    }

}
 