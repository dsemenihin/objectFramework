<?php
/**
 * @team BMA <phpbma@corp.badoo.com>
 * @maintainer Dmitry Semenihin <d.semenihin@corp.badoo.com>
 */
include_once 'MysqlDriverAbstract.php';

class PdoMysqlDriver extends MysqlDriverAbstract {
    /**
     * @var PDOStatement
     */
    protected $_dbh;

    public function __construct(array $params)
    {
        $dsn = 'mysql:dbname='.$params['database'].';host='.$params['host'].';port='.$params['port'];
        $user = $params['user'];
        $password = $params['password'];
        $this->_dbh = new PDO($dsn, $user, $password);
    }

    public function query($sql, $fetchType = null)
    {
        try {
            /**
             * @var PDORow
             */
            $result = $this->_dbh->query($sql);
        } catch (PDOException $e) {
            throw new Exception('PDO error: '.$e->getMessage());
        }

        if ($this->_dbh->errorCode() !== PDO::ERR_NONE) {
            $info = $this->_dbh->errorInfo();
            throw new Exception('Mysql error: '.$info[2] . '; Query: [' . $sql . ']');
        }

        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return $result->fetchAll(PDO::FETCH_ASSOC);

            case self::FETCH_NUM:
                return $result->fetchAll(PDO::FETCH_NUM);

            case self::FETCH_COLUMN:
                return $result->fetchAll(PDO::FETCH_COLUMN, 0);

            default:
                return $result->fetchAll();
        }
    }

    public function beginTransaction()
    {
        return $this->_dbh->beginTransaction();
    }

    public function commit()
    {
        return $this->_dbh->commit();
    }

    public function rollback()
    {
        return $this->_dbh->rollBack();
    }

    public function escape($str, $escapeType = self::ESCAPE_STR)
    {
        $map = array(
            self::ESCAPE_STR => PDO::PARAM_STR,
            self::ESCAPE_INT => PDO::PARAM_INT,
        );

        return $this->_dbh->quote($str, $map[$escapeType]);
    }

}
 