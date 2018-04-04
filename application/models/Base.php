<?php

abstract class BaseModel 
{
    protected $db = 'database name';    
    protected $pk = 'primary key name';
    protected $table = 'table name';
    

    protected $conn = NULL;
    private $config = NULL; #array

    public function __construct($db = NULL)
    {
        $this->config = \Yaf\Application::app()->getConfig()->mysql->toArray();
        $this->conn = $this->connect();
    }

    /**
     * 预处理参数
     * @param array $parameters
     * @return unknown
     */
    abstract public function prepareData($parameters);

    private function connect()
    {
        $conn = new mysqli($this->config['host'], $this->config['user'], $this->config['pass'], $this->config['name'], $this->config['port']);

        if ($conn->connect_errno) {
            $this->halt($conn->connect_errno, $conn->connect_error);
            return false;
        } 

        return $conn; 
    }


    final public function query($sql)
    {
        $result = $this->conn->query($sql);

        if ($this->conn->errno) {
            $this->halt($this->conn->errno, $this->conn->error);
            return false;
        }
	    return $result;
    }


    private function halt($errno, $error)
    {
        $msg = sprintf("Time:s%\tHost:%s\tPort:%d\tDatabase:%s\tErrno:%d\tError:%s", date("Y-m-d H:i:s"), $this->config['host'], $this->config['port'], $this->config['name'], $errno, $error) . PHP_EOL;
        $file = $this->config['log_save_path'] . date("Ymd").".log";
        error_log($msg, 3,  $file);
        if ($this->config['debug']) {
            echo $msg;
        }
    }

    
    final public function getRow($id, $columns = "*")
    {
        $sql = "select $columns from $this->table where $this->pk = $id";
        $result = $this->query($sql);
        if (!$result) return false;
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    }



    final public function getColumn($id, $column) 
    {        
        $sql = "select $column from $this->table where $this->pk = $id";
        $result = $this->query($sql);        
        if (!$result) return false;
        $row = $result->fetch_assoc();
        if ($row) return $row[$column];
        return false;
    }

    final public function getAll($columns, $where, $order)
    {
        $sql = "select $columns from $this->table where $where order by $order";
        $result = $this->query($sql);        
        if (!$result) return false;

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $result->free();
        return $rows;
    }

    final public function scalar($columns, $where, $order)
    {
        $sql = "select $columns from $this->table where $where order by $order limit 1";
        $result = $this->query($sql);     

        if (!$result) return false;

        return $result->fetch_assoc();
    }

    final public function getLimit($columns, $where, $order, $start, $limit)
    {
        $sql = "select $columns from $this->table where $where order by $order limit $start, $limit";
        $result = $this->query($sql);     

        if (!$result) return false;

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $result->free();
        return $rows;
    }


    final public function getCount($where)
    {
        $sql = "select count(id) as total from $this->table where $where";
        $result = $this->query($sql);
        if (!$result) return false;
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    final public function insert($data)
    {
    }


    final public function update($id, $data)
    {
    }

}
