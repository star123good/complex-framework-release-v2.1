<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Model Class
 *
 *      Model object is parent of all models.
 *      CRUD Operation - create, read, update, delete
 *      Model has to contain schema.
 *      factory, where, select, limit, join, etc
 *
**********************************************************************************************/


use Library\Database as Database;
use Library\Log as Log;


/*
 *      Model Class
 */
class Model {

    protected   $db = null,                             // database instance
                $tableName = null,                      // full table name
                $schema = array(),                      // schema array
                $data = array(),                        // attribute data array
                $arrayData = array(),                   // array to show attribute data
                $flagChangeData = array(),              // data changed flags array
                $foreignData = array(),                 // foreign data array
                $virtualTables = array(),               // virtual table hash array according to foreign
                $query = null,                          // static query
                $flagExist = null,                      // boolean value if exist in db
                $primaryKey = 'id';                     // primary key in table

    protected static $table = null;                     // table name


    public function __construct()
    {
        // database
        $this->db = Database::getInstance();

        // init
        $this->init();
    }

    /**
     *      to string
     *      @return string
     */
    public function __toString()
    {
        return json_encode($this->getArrayData());
    }

    /**
     *      init all data
     *      @return Model
     */
    public function init()
    {
        // table name
        $this->tableName = static::getTable();

        // query
        $this->query = array(
            'method' => null,
            'where' => null,
            'keys' => null,
            'orderBy' => null,
            'groupBy' => null,
            'limit' => null,
            'join' => null,
            'set' => null,
        );

        // flag exist
        $this->flagExist = null;

        // data
        $this->data = array();
        $this->flagChangeData = array();
        $this->foreignData = array();
        $this->virtualTables = array();
        foreach ($this->schema as $field) {
            // schema
            if ($this->validateTye($field)) {
                $this->data[$field['name']] = (isset($field['default'])) ? $field['default'] : null;
                $this->flagChangeData[$field['name']] = false;
                $this->foreignData[$field['name']] = null;
                
                // foreign keys
                if (isset($field['model'])
                    && isset($field['foreign'])
                    && isset($field['virtual'])) {
                        $foreignModel = new $field['model'];
                        if (is_object($foreignModel) && !array_key_exists($field['virtual'], $this->data)) {
                            // foreign model
                            $this->data[$field['virtual']] = $foreignModel;
                            $this->flagChangeData[$field['virtual']] = false;

                            $this->foreignData[$field['virtual']] = array(
                                'to' => $field['name'],
                                'table' => $foreignModel->tableName,
                                'key' => $field['foreign'],
                            );
                            $this->foreignData[$field['name']] = array(
                                'to' => $field['virtual'],
                            );

                            $this->virtualTables[$foreignModel->tableName] = $field['virtual'];
                        }
                }
            }
        }

        return $this;
    }

    /**
     *      validate type of schema
     *      @param  array   $field
     *      @return bool
     */
    private function validateTye($field)
    {
        if (isset($field['name']) && isset($field['type']) && in_array($field['type'], array(
            'tinyblob', 
            'mediumblob', 
            'blob', 
            'longblob', 
            'int', 
            'smallint', 
            'tinyint', 
            'mediumint',
            'bigint',
            'float',
            'decimal',
            'double',
            'real',
            'string',
            'varchar',
            'enum',
            'set',
            'char',
            'text',
            'tinytext',
            'mediumtext',
            'longtext',
            'date',
            'datetime',
            'year',
            'time',
            'timestamp',
        ))) {
            return true;
        }
        return false;
    }

    /**
     *      get where query
     *      @param  bool    $flagSelect
     *      @return string
     */
    private function getWhereQuery($flagSelect=false)
    {
        $qry = null;

        if (is_null($this->query['where']) && $this->flagExist === true) {
            $qry = "`" . $this->primaryKey . "` = '" . $this->data[$this->primaryKey] . "'";
        }
        else if (!is_null($this->query['where'])) {
            $qry = $this->query['where'];

            if ($flagSelect) {
                if (!is_null($this->query['groupBy']) && !empty($this->query['groupBy'])) $qry .= " GROUP BY " . implode(", ", $this->query['groupBy']);
                if (!is_null($this->query['orderBy']) && !empty($this->query['orderBy'])) $qry .= " ORDER BY " . implode(", ", $this->query['orderBy']);
                if (!is_null($this->query['limit'])) $qry .= " LIMIT " . $this->query['limit'];

                if (!is_null($this->query['join']) && !empty($this->query['join'])) {
                    // join
                    foreach ($this->schema as $field) {
                        // replace 
                        $qry = str_replace("`".$field['name']."`", "t.`".$field['name']."`", $qry);
                    }
                }
            }
        }
        
        return $qry;
    }

    /**
     *      process query
     *      @param  string  $callFromMethod
     *      @return bool
     */
    private function processQuery($callFromMethod=null)
    {
        $return = false;

        if ($this->query['method'] == "select") {
            // select
            $this->addJoinQuery();

            $result = $this->db->select($this->tableName, $this->getWhereQuery(true), $this->query['join'], $this->query['keys']);

            if ($result && is_array($result) && !empty($result)) {
                if ($callFromMethod == "find" || $callFromMethod == "get") {
                    // find, get
                    $this->update($result[0]);
                    
                    $return = true;
                }
                else if($callFromMethod == "getAll") {
                    // getAll
                    $return = $result;
                }
            }
        }
        else if ($this->query['method'] == "insert" && $this->query['set'] != "") {
            // insert
            $result = $this->db->insert($this->tableName, $this->query['set']);

            if ($result > 0) {
                $this->data[$this->primaryKey] = $result;
                $this->flagExist = true;

                $return = true;
            }
        }
        else if ($this->query['method'] == "update" && $this->query['set'] != "") {
            // update
            $result = $this->db->update($this->tableName, $this->query['set'], $this->getWhereQuery());

            if ($result > 0) {
                $return = true;
            }
        }
        else if ($this->query['method'] == "delete") {
            // delete
            $result = $this->db->delete($this->tableName, $this->getWhereQuery());

            if ($result > 0) {
                $this->flagExist = false;

                $return = true;
            }
        }

        $this->query = array_map(function($n) { return null; }, $this->query);
        $this->flagChangeData = array_map(function($n) { return false; }, $this->flagChangeData);

        return $return;
    }

    /**
     *      get
     *      @return string
     */
    public static function getTable()
    {
        return TABLE_PREFIX . static::$table . TABLE_SUFFIX;
    }
    
    /**
     *      get Attribute
     *      @param  string  $key
     *      @return mixed
     */
    public function getAttribute($key=null)
    {
        if (is_null($key)) return $this->data;
        else return (isset($this->data[$key])) ? $this->data[$key] : null;
    }

    /**
     *      get array data from attribute
     *      @return array
     */
    public function getArrayData()
    {
        $this->arrayData = $this->data;

        array_walk_recursive($this->arrayData, function(&$item,$key){
            if ($item instanceof Model) {
                $item = $item->getArrayData();
            }
        });

        return $this->arrayData;
    }

    /**
     *      set attribute
     *      @param  string  $key
     *      @param  mixed   $value
     *      @return void
     */
    protected function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->data)) {
            // directly
            $this->data[$key] = $value;
            $this->flagChangeData[$key] = true;

            // foreign
            if (!is_null($this->foreignData[$key]) && is_array($this->foreignData[$key]) && !empty($this->foreignData[$key])) {
                $temp_field = $this->foreignData[$key]['to'];

                if (array_key_exists($temp_field, $this->data)) {
                    if (isset($this->foreignData[$key]['table'])) {
                        // from virtual
                        $temp_value = $this->data[$key]->getAttribute($this->foreignData[$key]['key']);
                        
                        if ($temp_value != $this->data[$temp_field]) {
                            $this->data[$temp_field] = $temp_value;
                            $this->flagChangeData[$temp_field] = true;
                        }
                    }
                    else {
                        // to virtual
                        $temp_value = $this->data[$temp_field]->getAttribute($this->foreignData[$temp_field]['key']);
                        if ($temp_value != $value) $this->data[$temp_field]->init();
                    }
                }
            }
        }
        else {
            // indirectly
            foreach ($this->virtualTables as $temp => $field) {
                if (strpos($key, $temp) === 0 && is_object($this->data[$field])){
                    $virtualKey = str_replace($temp."_", "", $key);

                    if (array_key_exists($virtualKey, $this->data[$field]->data)) {
                        $this->data[$field]->data[$virtualKey] = $value;
                        // Log::addLog($key . ", " . $temp . " => " . $virtualKey);
                    }
                }
            }
        }
    }

    /**
     *      create new instance
     *      @param  array  $data
     *      @return Model
     *      @example    Model::create($array)
     */
    public static function create($data=array())
	{
        $static = new static();
        $static->update($data);
        $static->flagExist = false;

        return $static;
    }

    /**
     *      update
     *      @param  string|array   $data
     *      @param  string  $value
     *      @return Model
     *      @example    $model->update($key, $value) | $model->update($array)
     */
    public function update($data, $value=null)
	{
        if (is_null($value) && is_array($data)) {
            // array
            foreach ($data as $key => $val) {
                $this->setAttribute($key, $val);
            }
        }
        else if (!is_null($value) && is_string($data)) {
            // string
            $this->setAttribute($data, $value);
        }

        $this->flagExist = true;

        return $this;
    }

    /**
     *      read one using primary key
     *      @param  array  $id
     *      @return Model
     *      @example    Model::find($id)
     */
    public static function find($id)
	{
        $static = new static();
        $static->query['method'] = "select";
        $static->query['where'] = "`" . $static->primaryKey . "` = '" . $id . "'";
        $static->flagExist = $static->processQuery("find");

        return $static;
    }

    /**
     *      where query
     *      @param  string  $qry
     *      @return Model
     *      @example    Model::where('query condition')
     */
    public static function where($qry)
	{
        $static = new static();
        $static->query['method'] = "select";
        $static->query['where'] = $qry;

        return $static;
    }

    /**
     *      and where query
     *      @param  string  $qry
     *      @return Model
     */
    public function andWhere($qry)
	{
        if ($this->query['method'] == "select") {
            if (is_null($this->query['where']) || $this->query['where'] == "") $this->query['where'] = "1";
            $this->query['where'] .= " AND " . $qry;
        }

        return $this;
    }

    /**
     *      or where query
     *      @param  string  $qry
     *      @return Model
     */
    public function orWhere($qry)
	{
        if ($this->query['method'] == "select") {
            if (is_null($this->query['where']) || $this->query['where'] == "") $this->query['where'] = "1";
            $this->query['where'] .= " OR " . $qry;
        }

        return $this;
    }

    /**
     *      order by query
     *      @param  string  $key
     *      @param  bool    $asc
     *      @return Model
     */
    public function orderBy($key, $asc=true)
	{
        if (array_key_exists($key, $this->data) && $this->query['method'] == "select") {
            if (is_null($this->query['orderBy'])) $this->query['orderBy'] = array();

            if ($asc) $this->query['orderBy'][] = " `" . $key . "` ASC ";
            else $this->query['orderBy'][] = " `" . $key . "` DESC ";
        }

        return $this;
    }

    /**
     *      group by query
     *      @param  string  $key
     *      @return Model
     */
    public function groupBy($key)
	{
        if (array_key_exists($key, $this->data) && $this->query['method'] == "select") {
            if (is_null($this->query['groupBy'])) $this->query['groupBy'] = array();

            $this->query['groupBy'][] = " `" . $key . "` ";
        }

        return $this;
    }

    /**
     *      limit query
     *      @param  int     $start
     *      @param  int     $num
     *      @return Model
     */
    public function limit($start, $num=null)
	{
        if ($start >= 0 && $this->query['method'] == "select" && !is_null($num) && $num >= 0) {
            $this->query['limit'] = " " . $start . ", " . $num . " ";
        }
        else if ($start >= 0 && $this->query['method'] == "select" && is_null($num)) {
            $this->query['limit'] = " " . $start . " ";
        }

        return $this;
    }

    /**
     *      join table query
     *      @param  string  $key
     *      @param  string  $foreignTable
     *      @param  string  $foreignKey
     *      @param  array   $foreignColumns
     *      @return Model
     */
    public function joinTable($key, $foreignTable, $foreignKey, $foreignColumns=null)
	{
        if (array_key_exists($key, $this->data) && $this->query['method'] == "select") {
            if (is_null($this->query['join'])) {
                $this->query['join'] = array();
                $this->query['keys'] = " t.* ";
            }

            $tableAsName = "t" . (count($this->query['join']) + 1);

            // join foreign table
            $this->query['join'][] = " `" . $foreignTable . "` AS " . $tableAsName . " 
                ON t.`" . $key . "` = " . $tableAsName . ".`" . $foreignKey . "` ";

            // foreign table column names
            if (is_null($foreignColumns)) $this->query['keys'] .= " , " . $tableAsName . ".* ";
            else if (is_array($foreignColumns) && !empty($foreignColumns)) {
                foreach ($foreignColumns as $column) {
                    $this->query['keys'] .= " , " . $tableAsName . ".`" . $column . "` AS " . $foreignTable . "_" . $column . " ";
                }
            }
        }

        return $this;
    }

    /**
     *      add join query automatically using foreign keys
     *      @return bool
     */
    protected function addJoinQuery()
    {
        $result = false;

        foreach ($this->foreignData as $key => $field) {
            if (!is_null($field) && is_array($field) && !empty($field) && isset($field['table'])) {
                $columns = array_keys($this->data[$key]->data);

                $this->joinTable($field['to'], $field['table'], $field['key'], $columns);
                $result = true;
            }
        }

        return $result;
    }

    /**
     *      read one using where query
     *      @return Model
     */
    public function get()
	{
        if ($this->query['method'] == "select") {
            $this->flagExist = $this->processQuery("get");
        }

        return $this;
    }

    /**
     *      read multiple
     *      @return array<Model>
     */
    public function getAll()
	{
        $list = array();

        if ($this->query['method'] == "select") {
            $result = $this->processQuery("getAll");

            if ($result && !empty($result)) {
                foreach ($result as $temp) {
                    $tmp = new static();
                    $tmp->update($temp);
                    $tmp->flagExist = true;
                    $list[] = $tmp;
                }
            }
        }

        return $list;
    }
    
    /**
     *      save to db
     *      @return bool
     */
    public function save()
	{
        if ($this->flagExist === true) {
            $this->query['method'] = "update";
        }
        else {
            $this->query['method'] = "insert";
        }

        $temp = array();
        foreach ($this->data as $key => $val) {
            if ($this->flagChangeData[$key] 
                && (is_null($this->foreignData[$key]) 
                    || (is_array($this->foreignData[$key]) 
                        && !isset($this->foreignData[$key]['table'])))) {
                            $temp[] = "`" . $key . "` = '" . $val . "'";
                        }
        }
        $this->query['set'] = implode(", ", $temp);

        return $this->processQuery();
	}

    /**
     *      delete from db
     *      @return bool
     */
    public function delete()
	{
        $this->query['method'] = "delete";

        return $this->processQuery();
    }

    /**
     *      check exist
     *      @return bool
     */
    public function isExist()
    {
        return $this->flagExist;
    }
    
}