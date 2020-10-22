<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Database Handler
 *
 *      Database connection
 *
**********************************************************************************************/


use \PDO;
use \Config;
use Library\Log as Log;


/*
 *      Database Class
 */
class Database {

    private $pdo,                           // PDO instance
            $hostname,                      // host name
            $port,                          // port
            $username,                      // username
            $password,                      // password
            $database,                      // database
            $options;                       // options array

    private static $instance;               // THE only instance of the class
 
    
    public function __construct()
    {
        // init variables
        $this->pdo = null;
        $this->port = null;
        $this->options = [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // connect
        $this->connect();
    }

    /**
     *      get instance
     *      @return     Database
     *      @example    Database::getInstance()
     */
    public static function getInstance()
    {
        if ( !isset(self::$instance))
        {
            self::$instance = new self;
        }
       
        return self::$instance;
    }

    /**
     *      connect to mysql
     */
    private function connect()
    {
        // get parameters from config
        $this->hostname = Config::getConfig('SQL_HOST');
        $this->username = Config::getConfig('SQL_USER');
        $this->password = Config::getConfig('SQL_PASSWORD');
        $this->database = Config::getConfig('SQL_DB');

        // check port
        if(is_null($this->port)) $dsn = "mysql:host=".$this->hostname.";dbname=".$this->database.";charset=".CHARSET;
        else $dsn = "mysql:host=".$this->hostname.";dbname=".$this->database.";port=".$this->port.";charset=".CHARSET;

        // check options
        if(is_null($this->options)) $this->pdo = new PDO($dsn, $this->username, $this->password);
        else $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
        Log::addLog("mysql connected.");
    }

    /**
     *      disconnect
     */
    public function disconnect()
    {
        $this->pdo = null;
        Log::addLog("mysql disconnected.");
    }

    /**
     *      query
     *      @param  string  $qry
     *      @return bool
     */
    public function query($qry)
    {
        $stmt = $this->pdo->prepare($qry);
        Log::addLog($qry);
        return $stmt->execute();
    }

    /**
     *      select rows
     *      @param  string  $table
     *      @param  string  $where
     *      @param  array   $join
     *      @param  array   $keys
     *      @return array
     */
    public function select($table, $where=null, $join=null, $keys=null)
    {
        if (is_null($keys)) $keys = "*";
        $qry = "SELECT " . $keys . " FROM `" . $table . "` ";
        
        if (!is_null($join) && !empty($join)) {
            $qry = "SELECT " . $keys . " FROM `" . $table . "` AS t LEFT JOIN " . implode(" LEFT JOIN ", $join);
        }

        if (!is_null($where)) $qry .= " WHERE " . $where;

        $stmt = $this->pdo->query($qry);
        Log::addLog($qry);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *      insert a row
     *      @param  string  $table
     *      @param  string  $set
     *      @return int
     */
    public function insert($table, $set)
    {
        $qry = "INSERT INTO `" . $table . "` SET " . $set;
        $this->pdo->exec($qry);
        Log::addLog($qry);
        return $this->pdo->lastInsertId();
    }

    /**
     *      update rows
     *      @param  string  $table
     *      @param  string  $set
     *      @param  string  $where
     *      @return int
     */
    public function update($table, $set, $where=null)
    {
        $qry = "UPDATE `" . $table . "` SET " . $set . " ";
        if (!is_null($where)) $qry .= " WHERE " . $where;
        Log::addLog($qry);
        return $this->pdo->exec($qry);
    }

    /**
     *      delete rows
     *      @param  string  $table
     *      @param  string  $where
     *      @return int
     */
    public function delete($table, $where=null)
    {
        $qry = "DELETE FROM `" . $table . "` ";
        if (!is_null($where)) $qry .= " WHERE " . $where;
        Log::addLog($qry);
        return $this->pdo->exec($qry);
    }

    /**
     *      create a table
     *      @param  string  $table
     *      @param  string  $set
     *      @return int
     */
    public function create($table, $set)
    {
        $sql = "CREATE TABLE `" . $table . "`( " . $set . " )";
        Log::addLog($qry);
        return $this->pdo->exec($qry);
    }
    
}