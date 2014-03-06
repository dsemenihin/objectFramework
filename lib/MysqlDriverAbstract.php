<?php
/**
 * Created by PhpStorm.
 * User: dsemenihin
 * Date: 05.03.14
 * Time: 20:46
 */

abstract class MysqlDriverAbstract {
    const FETCH_ALL = 0;
    const FETCH_NUM = 1;
    const FETCH_ASSOC = 2;
    const FETCH_COLUMN = 3;

    const ESCAPE_STR = 0;
    const ESCAPE_INT = 1;

    public abstract function __construct(array $params);
    public abstract function query($sql, $fetchType = null);
    public abstract function beginTransaction();
    public abstract function commit();
    public abstract function rollback();
    public abstract function escape($str, $escapeType = self::ESCAPE_STR);
}