<?php

/**
 *
 * @author DA SILVA
 */

interface ORM_Interface {   

    function connect();

    function disconnect();

    function query($query);

    function fetch();

    function select($table, $conditions = '', $fields = '*', $order = '', $limit = null, $offset = null);

    function insert($table, array $data);

    function update($table, array $data, $conditions);

    function delete($table, $conditions);

    function getInsertId();

    function countRows();

    function getAffectedRows();
}


?>
