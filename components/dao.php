<?php

/*
 *    Responsavel pela comunicacao direta com o banco de dados.
 *    Eh utilizado por todas as classes do model
 *
 *    Serve basicamente como interface para o pdo. Para utilizar outra abstracao
 *    de acesso ao banco de dados, crie uma extensao da classe abstrata Dao_library
 */

class Dao
{
    static private $instance;
    static function init($dsn, $user="", $pass="")
    {
        try {
            self::$instance = new PDO($dsn, $user, $pass);
        } catch (Exception $e){
            // criar classe para exibir na tela de forma bonita
            echo $e->getMessage();
        }
    }

    static function instance()
    {
        if ( self::$instance )
            return self::$instance;
        else
            throw new Exception('instancia nao iniciada');
    }

    static function insert($table, $args)
    {
        $handler = self::instance();
        $fields = implode(', ', array_keys($args));
        $values = "'".implode("', '",$args)."'";

        $query = "insert into {$table} ({$fields}) values ({$values})";
        $stmt = $handler->prepare($query);
        if ( !$stmt ){
            throw new Exception('Argumentos incorretos');
            return false;
        }

        if ( !$stmt->execute() ){
            throw new Exception('Problemas com execucao');
            return false;
        }

        $stmt->closeCursor();
        return $handler->lastInesrtId();
    }

    static function update($table, $args, $conds)
    {
        $handler = self::instance();
        $fields = array();
        foreach( $args as $field => $value ){
            $fields[] = $field." = '{$value}'";
        }
        $fields = implode(', ', $fields);

        if ( $conds ){
            $where = "where $conds";
        } else {
            $where = '';
        }

        $query = "update {$table} set {$fields} {$where}";
        $stmt = $handler->prepare($query);
        if ( !$stmt ){
            throw new Exception('Argumentos incorretos');
            return false;
        }

        if ( !$stmt->execute() ){
            throw new Exception('Problemas com execucao');
            return false;
        }

        $rows = $stmt->rowCount();
        $stmt->closeCursor();
        return $rows;
    }

    static function delete($table, $conds)
    {
        $handler = self::instance();
        if ( $conds )
            $where = 'where '.$conds;
        else
            $where = '';

        $query = "delete from {$table} {$where}";
        $stmt = $handler->prepare($query);

        if ( !$stmt ){
            throw new Exception('Argumentos incorretos');
            return false;
        }

        if ( !$stmt->execute() ){
            throw new Exception('Problemas na execucao');
            return false;
        }

        $rows = $stmt->rowCount();
        $stmt->closeCursor();
        return $rows;
    }

    static function query($query)
    {
        $handler = self::instance();
        $stmt = $handler->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    static function describe($table)
    {
        $handler = self::instance();
        $query = 'select * from '.$table.' limit 1';
        $stmt = $handler->query($query);
        $fields = array();
        for ($i=0; $i < $stmt->columnCount(); $i++){
            $field = $stmt->getColumnMeta($i);
            $fields[$field['name']] = $field['native_type'];
        }
        return $fields;
    }

    static function execute($query)
    {
        $handler = self::instance();
        return $handler->exec($query);
    }

    static function beginTransaction()
    {
        $handler = self::instance();
        return $handler->beginTransaction();
    }
    static function commit()
    {
        $handler = self::instance();
        return $handler->commit();
    }
    static function rollback()
    {
        $handler = self::instance();
        return $handler->rollback();
    }
}

