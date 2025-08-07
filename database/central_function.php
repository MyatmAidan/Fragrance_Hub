<?php

function select_data($table, $mysql, $select = '*', $where = '', $order = '')
{
    $sql = "SELECT $select FROM `$table` $where $order ";
    return $mysql->query($sql);
}

function deleteData($table, $mysql, $where)
{
    $sql = "DELETE FROM `$table` WHERE $where";
    return $mysql->query($sql);
}

function updateData($table, $mysql, $data, $where)
{
    $sql = "UPDATE `$table` SET ";
    $updates = [];
    foreach ($data as $key => $value) {
        $updates[] = "`$key` = '$value'";
    }
    $sql .= implode(", ", $updates);
    $wheres = [];
    $sql .= " WHERE ";
    foreach ($where as $key => $value) {
        $wheres[] = "`$key` = '$value'";
    }
    $sql .= implode(" AND ", $wheres);
    return $mysql->query($sql);
}

function insertData($table, $mysql, $data)
{
    $columns = [];
    $values = [];

    foreach ($data as $key => $value) {
        $columns[] = "`$key`";
        $values[] = "'$value'";
    }

    $sql = "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";

    return $mysql->query($sql);
}

function getDataWithOffset($table, $mysql, $offset, $limit)
{
    $sql = "SELECT * FROM `$table` LIMIT $limit OFFSET $offset";
    return $mysql->query($sql);
}
