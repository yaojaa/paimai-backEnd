<?php

abstract class BaseModel 
{
    protected $db = 'database name';    
    protected $pk = 'primary key name';
    protected $table = 'table name';
    protected $conn = NULL;
    private $config = NULL; #array

    public function __construct()
    {
		$ini = new Yaf_Config_Ini(CONFIG_PATH . "/mysql.ini", $this->db); 
        $this->config = $ini->toArray();
        $this->conn = $this->connect();
    }

    public static function escape ( $str ) {
		return $str;
    }

    /**
     * 预处理参数
     * @param array $parameters
     * @return unknown
     */
    abstract public function prepareData($parameters);

    private function connect()
    {
        $conn = new mysqli($this->config['host'], $this->config['user'], $this->config['pass'], $this->config['db'], $this->config['port']);

        if ($conn->connect_errno) {
            $this->halt($conn->connect_errno, $conn->connect_error);
            return false;
        } 


		$conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);

        return $conn; 
    }

	public function getQuery($sql)
	{
		$result = $this->query($sql);
        if (!$result) return false;

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $result->free();
        return $rows;
	}

    public function query($sql)
    {
        $result = $this->conn->query($sql);

        if ($this->conn->errno) {
            $this->halt($sql, $this->conn->errno, $this->conn->error);
            return false;
        }
		return $result;
    }


    private function halt($sql, $errno, $error)
    {
        $msg = sprintf("Time:%s\tHost:%s\tPort:%d\tDatabase:%s\tErrno:%d\tError:%s\tSQL:%s", date("Y-m-d H:i:s"), $this->config['host'], $this->config['port'], $this->config['db'], $errno, $error, $sql) . PHP_EOL;
        $file = sprintf($this->config['log'],date("Ymd"));
        error_log($msg, 3,  $file);
        if ($this->config['debug']) {
            echo $msg;
        }
    }

    
    public function getRow($id, $columns = "*")
    {
        $sql = "select $columns from $this->table where $this->pk = $id";
        $result = $this->query($sql);
        if (!$result) return false;
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    }



    public function getColumn($id, $column) 
    {        
        $sql = "select $column from $this->table where $this->pk = $id";
        $result = $this->query($sql);        
        if (!$result) return false;
        $row = $result->fetch_assoc();
        if ($row) return $row[$column];
        return false;
    }

    public function getAll($columns, $where, $order)
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

    public function scalar($columns, $where, $order)
    {
        $sql = "select $columns from $this->table where $where order by $order limit 1";
        $result = $this->query($sql);     

        if (!$result) return false;

        return $result->fetch_assoc();
    }

    public function getLimit($columns, $where, $order, $page, $pagesize)
    {
		$page = $page < 1 ? 1 : $page;
		$start = ($page-1)*$pagesize;
        $sql = "select $columns from $this->table where $where order by $order limit $start, $pagesize";
        $result = $this->query($sql);     

        if (!$result) return false;

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $result->free();
        return $rows;
    }


    public function getCount($where)
    {
        $sql = "select count(id) as total from $this->table where $where";
        $result = $this->query($sql);
        if (!$result) return false;
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function insert($data)
    {
        $columns = ""; 
        $values = ""; 

        foreach( $data as $k=>$v )
        {   
            $columns .= "`{$k}`,";
            $values .= "'" . self::escape( $v ) . "',";
        }   

        $columns = trim( $columns, ',' );
        $values = trim( $values, ',' );
        $sql = "insert into {$this->table}({$columns}) values ({$values})";
		$rs = $this->query($sql);
		if (false !== $rs) return $this->conn->insert_id;
		return false;
    }


    public function update($id, $data)
    {
        $set = "";     
        foreach( $data as $k=>$v )
        {
            $set .= "`{$k}` = '" . self::escape( $v ) . "',";
        }
        $set = trim( $set, ',' );
        $sql = "update {$this->table} set {$set} where `{$this->pk}`='{$id}'";
		$rs = $this->query($sql);
		if (false !== $rs) return $this->conn->affected_rows;
		return false;

    }

	public function incr($id, $data)
	{
        $set = "";     
        foreach( $data as $k=>$v )
        {
            $set .= "`{$k}` = `{$k}` + (" . $v . "),";
        }
        $set = trim( $set, ',' );
        $sql = "update {$this->table} set {$set} where `{$this->pk}`='{$id}'";
		$rs = $this->query($sql);
		if (false !== $rs) return $this->conn->affected_rows;
		return false;
	}

	public function delete($id)
	{ 
		$sql = "delete from {$this->table} where `{$this->pk}`={$id}";
		return $this->query($sql);
	}

}
