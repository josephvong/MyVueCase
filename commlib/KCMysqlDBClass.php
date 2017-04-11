<?php
class MyPDO
{
    protected $dbName = '';
    private $dsn;
    private $dbh;
    private $qswhData;
    private $DBC = FALSE;
    /**
     * Connect
     *
     * @param String $dbHost 主机地址
     * @param String $dbUser 用户名
     * @param String $dbPassword 密码
     * @param String $dbName 数据库名
     * @param String $dbCharset 字符集
     */
    public function connect($dbHost, $dbUser, $dbPassword, $dbName, $dbCharset, $PORT)
    {
        try
        {
            $this->dsn = 'mysql:host='.$dbHost.';dbname='.$dbName.';port='.$PORT;
            $this->dbh = new PDO($this->dsn, $dbUser, $dbPassword);
            $this->dbh->exec("SET character_set_connection=$dbCharset, character_set_results=$dbCharset, character_set_client=binary");
            return TRUE;
        }
        catch (PDOException $e)
        {
            $this->outputError($e->getMessage());
        }
    }
    
    function __construct($dbHost, $dbUser, $dbPassword, $dbName, $PORT='3306')
    {
        if ($this->DBC == FALSE)
        {
            if ($this->connect($dbHost, $dbUser, $dbPassword, $dbName, 'utf8', $PORT) != TRUE)
            {
                return FALSE;
            }
            else
            {
                $this->DBC = TRUE;
                //$this->qswhData = file("../locale/qswhGBK.php");
                return TRUE;
            }
        }
    }
    
    function gbktoutf8($str)
    {
        return hexdec($str)<256?chr(hexdec($str)):"&#x".$str.";";
    }
    function gb2u($gb)
    {
        $ret="";
        for($i=0;$i<strlen($gb);$i++)
        {
            if(($p=ord(substr($gb,$i,1)))>127)
            {
                $q=ord(substr($gb,++$i,1));
                $q=($q-($q>128?65:64))*4;
                $q=substr($this->qswhData[$p-128],$q,4);
            }
            else
                $q=dechex($p);
            $ret.= $this->gbktoutf8($q);
        }
        return $ret;
    }

    /**
     * Query 查询
     *
     * @param String $strSql SQL语句
     * @param String $queryMode 查询方式(All or Row)
     * @param Boolean $debug
     * @return Array
     */
    public function query($strSql, $queryMode = 'All', $debug = false)
    {
        if($debug === true)
        {
            //debug
            $this->debug($strSql);
        }

        $recordset = $this->dbh->query($strSql);
        $errValue = $this->getPDOError();
        if( $errValue !== 0)
        {
            return array("error" => $errValue, "error_code" => 10002);
        }
        
        $result = array();
        //语法没有错误,查询数据库
        if($recordset)
        {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            if($queryMode == 'All')
            {
                $result = $recordset->fetchAll();
            }
            else if($queryMode == 'Row')
            {
                $result = $recordset->fetch();
            }
        }

        return $result;
    }
    
    /**
     * Query 查询
     *
     * @param String $strSql SQL语句
     * @param String $queryMode 查询方式(All or Row)
     * @param Boolean $debug
     * @return Array
     */
    public function DDquery($strSql, $queryMode = 'All')
    {
        $recordset = $this->dbh->query($strSql);
        $errValue = $this->getPDOError();
        if( $errValue !== 0)
        {
            return array("status" => 10001, "description" => $errValue);
        }

        $result = array();
        //语法没有错误,查询数据库
        if($recordset)
        {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            if($queryMode == 'All')
            {
                $result = $recordset->fetchAll();
            }
            else if($queryMode == 'Row')
            {
                $result = $recordset->fetch();
            }
        }

        if( !is_array($result) )
        {
            return array("status" => 30003, "description" => "数据库读失败");
        }

        // 返回结果
        return array("status" => 0, "description" => "ok", "data" => $result);
    }
    
    public function gridquery($strSql,$debug = false)
    {
        $sData = "";
        if($debug === true)
        {
            //debug
            $this->debug($strSql);
        }
        $recordset = $this->dbh->query($strSql);
        $this->getPDOError();
        if($recordset)
        {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            $iNumRows = $recordset->rowCount();
            if ($iNumRows == 0) return null;
            $aRow = $recordset->fetchAll();
            //id,name,share,public,createdate,packageset,summary
            for ( $i = 0; $i < $iNumRows; $i++ )
            {
                if( $i > 0 )
                {
                    $sData .= ",";
                }
                $sData .= "[";
                $sData .= $aRow[$i]['id'];
                $sData .= ",";
                $sData .= "'".$aRow[$i]['name']."'";
                $sData .= ",";
                if($aRow[$i]['share'] == 1)
                {
                    $sData .= "'已共享'";
                }
                else
                {
                    $sData .= "'未共享'";
                }
                $sData .= ",";
                if ($aRow[$i]['public'] == 1 )
                {
                    $sData .= "'已发布'";
                }
                else
                {
                    $sData .= "'未发布'";
                }
                $sData .= ",";
                $sData .= "'".$aRow[$i]['createdate']."'";
                $sData .= ",";
                $sData .= "'".$aRow[$i]['packageset']."'";
                $sData .= ",";
                $sData .= "'".$aRow[$i]['summary']."'";
                $sData .= "]";

            }
        }
        else
        {
            return null;
        }
        return $sData;
    }
    
    public function gridjsonquery($strSql,$debug = false)
    {
        if($debug === true)
        {
            //debug
            $this->debug($strSql);
        }
        $recordset = $this->dbh->query($strSql);
        $this->getPDOError();
        if($recordset)
        {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            $iNumRows = $recordset->rowCount();
            if ($iNumRows == 0) return null;
            $rows = Array();
            while($row = $recordset->fetch())
            {
                array_push($rows, $row);
            }
            $a_utf8_json_encode = json_encode(Array(
                "total"=>$iNumRows,
                "data"=>$rows));
            return $a_utf8_json_encode;
        } 
    }
    /**
     * Update 更新
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param String $where 条件
     * @param Boolean $debug
     * @return Int
     */
    public function update($table, $arrayDataValue, $where = '', $debug = false)
    {
        $ret_str = $this->checkFields($table, $arrayDataValue);
        if( $ret_str !== 0)
        {
            return array("error" => $ret_str, "error_code" => 10002);
        }
        
        if($where)
        {
            $strSql = '';
            foreach($arrayDataValue as $key => $value)
            {
                $strSql .= ", `$key`='$value'";
            }
            $strSql = substr($strSql, 1);
            $strSql = "UPDATE `$table` SET $strSql WHERE $where";
        }
        else
        {
            $strSql = "REPLACE INTO `$table`(`".implode('`,`', array_keys($arrayDataValue))."`) VALUES('".implode("','", $arrayDataValue)."')";
        }
        if($debug === true)
        {
            $this->debug($strSql);
        }

        $ret_result = $this->execSql($strSql);
        if( $ret_result === false )
        {
            //echo $this->getPDOError() . "\n";
            //echo "sqlStr:" . $strSql . "\n";
        }
        return $ret_result;
    }
    
    /**
     * Update 更新
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param String $where 条件
     * @return array
     */
    public function DDupdate($table, $arrayDataValue, $where = '')
    {
        $arr_ret = $this->DDcheckFields($table, $arrayDataValue);
        if( $arr_ret['status'] != 0)
        {
            return $arr_ret;
        }
        
        if($where)
        {
            $strSql = '';
            foreach($arrayDataValue as $key => $value)
            {
                $strSql .= ", `$key`='$value'";
            }
            $strSql = substr($strSql, 1);
            $strSql = "UPDATE `$table` SET $strSql WHERE $where";
        }
        else
        {
            $strSql = "REPLACE INTO `$table`(`".implode('`,`', array_keys($arrayDataValue))."`) VALUES('".implode("','", $arrayDataValue)."')";
        }

        return $this->DDexecSql($strSql);
    }
    
    /**
     * Insert 插入
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param Boolean $debug
     * @return Int
     */
    public function insert($table, $arrayDataValue, $debug = false)
    {
        $ret_str = $this->checkFields($table, $arrayDataValue);
        if( $ret_str !== 0)
        {
            return array("error" => $ret_str, "error_code" => 10002);
        }
        $strSql = '';
        foreach($arrayDataValue as $key => $value)
        {
            $strSql .= ", `$key`=\"$value\"";
        }
        $strSql = substr($strSql, 1);
        $strSql = "INSERT INTO `$table` SET $strSql";
        if($debug === true)
        {
            $this->debug($strSql);
        }
        
        return $this->execSql($strSql);
    }
    
    /**
     * DDinsert 插入
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param Boolean $debug
     * @return Int
     */
    public function DDinsert($table, $arrayDataValue)
    {
        $arr_ret = $this->DDcheckFields($table, $arrayDataValue);
        if( $arr_ret['status'] != 0 )
        {
            return $arr_ret;
        }

        $strSql = '';
        foreach($arrayDataValue as $key => $value)
        {
            $strSql .= ", `$key`=\"$value\"";
        }
        $strSql = substr($strSql, 1);
        $strSql = "INSERT INTO `$table` SET $strSql";

        return $this->DDexecSql($strSql);
    }
    
    /*
    生成SQL语句
    model:支持的语句方式,insert:插入;update:刷新;delete:删除
    table:表名.
    arrayDataValue:数据表的key/value，以数组方式保存.
    where:条件字段.
    */
    public function gen_sqlStr($model, $table, $arrayDataValue, $where ='')
    {
        $strSql = '';
        $md = strtolower($model);
        
        if( $md == 'insert' )
        {
            $ret_str = $this->checkFields($table, $arrayDataValue);
            if( $ret_str !== 0)
            {
                return array("error" => $ret_str, "error_code" => 10002);
            }
            $strSql = '';
            foreach($arrayDataValue as $key => $value)
            {
                $strSql .= ", `$key`=\"$value\"";
            }
            $strSql = substr($strSql, 1);
            $strSql = "INSERT INTO `$table` SET $strSql";
        }
        else if( $md == 'replace' )
        {
            $ret_str = $this->checkFields($table, $arrayDataValue);
            if( $ret_str !== 0)
            {
                return array("error" => $ret_str, "error_code" => 10002);
            }
            $strSql = '';
            foreach($arrayDataValue as $key => $value)
            {
                $strSql .= ", `$key`=\"$value\"";
            }
            $strSql = substr($strSql, 1);
            $strSql = "REPLACE INTO `$table` SET $strSql";
        }
        else if( $md == 'delete' )
        {
            if($where == '')
            {
                return array("error" => "缺少必要条件", "error_code" => 10002);
            }
            
            $strSql = "DELETE FROM `$table` WHERE $where";
        }
        else if( $md == 'update' )
        {
            $ret_str = $this->checkFields($table, $arrayDataValue);
            if( $ret_str !== 0)
            {
                return array("error" => $ret_str, "error_code" => 10002);
            }

            if($where == '')
            {
                return array("error" => "缺少必要条件", "error_code" => 10002);
            }
            else
            {
                $strSql = '';
                foreach($arrayDataValue as $key => $value)
                {
                    $strSql .= ", `$key`='$value'";
                }
                $strSql = substr($strSql, 1);
                $strSql = "UPDATE `$table` SET $strSql WHERE $where";
            }
        }
        else
        {
            return array("error" => "unsurport", "error_code" => 10002);
        }
        
        return array("error" => $strSql, "error_code" => 0);
    }
    
    /**
     * Replace 覆盖方式插入
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param Boolean $debug
     * @return Int
     */
    public function replace($table, $arrayDataValue, $debug = false)
    {
        $this->checkFields($table, $arrayDataValue);
        $strSql = '';
        foreach($arrayDataValue as $key => $value)
        {
            $strSql .= ", `$key`='$value'";
        }
        $strSql = substr($strSql, 1);
        $strSql = "REPLACE INTO `$table` SET $strSql";
        if($debug === true)
        {
            $this->debug($strSql);
        }
        
        return $this->execSql($strSql);
    }
    
    /**
     * Delete 删除
     *
     * @param String $table 表名
     * @param String $where 条件
     * @param Boolean $debug
     * @return Int
     */
    public function delete($table, $where = '', $debug = false)
    {
        if($where == '')
        {
            return array("error" => "缺少必要条件", "error_code" => 10002);
        }
        else
        {
            $strSql = "DELETE FROM `$table` WHERE $where";
            if($debug === true)
            {
                $this->debug($strSql);
            }
            
            return $this->execSql($strSql);
        }
    }
    
    /**
     * execSql 执行SQL语句
     *
     * @param String $strSql
     * @param Boolean $debug
     * @return Int
     */
    public function execSql($strSql, $debug = false)
    {
        if($debug === true)
        {
            $this->debug($strSql);
        }
        $result = $this->dbh->exec($strSql);
        $this->getPDOError();
        return $result;
    }
    
    /**
     * DDexecSql 执行SQL语句
     *
     * @param String $strSql
     * @param Boolean $debug
     * @return array()
     */
    public function DDexecSql($strSql)
    {
        $result = $this->dbh->exec($strSql);
        if( $result === false )
        {
            $errmsg = $this->getPDOError();
            return array("status" => 10001,"description" => $errmsg);
        }

        return array("status" => 0,"description" => 'ok');
    }
    
    /**
     * 获取表引擎
     * 
     * @param String $dbName 库名
     * @param String $tableName 表名
     * @param Boolean $debug
     * @return String
     */
    public function getTableEngine($dbName, $tableName)
    {
        $strSql = "SHOW TABLE STATUS FROM $dbName WHERE Name='".$tableName."'";
        $arrayTableInfo = $this->query($strSql);
        $this->getPDOError();
        return $arrayTableInfo[0]['Engine'];
    }
    
    /**
     * beginTransaction 事务开始
     */
    public function beginTransaction()
    {
        $this->dbh->beginTransaction();
    }
    
    /**
     * commit 事务提交
     */
    public function commit()
    {
        $this->dbh->commit();
    }
    
    /**
     * rollback 事务回滚
     */
    public function rollback()
    {
        $this->dbh->rollback();
    }
    
    /**
     * transaction 通过事务处理多条SQL语句
     * 调用前需通过getTableEngine判断表引擎是否支持事务
     *
     * @param array $arraySql
     * @return Boolean
     */
    public static function execTransaction($arraySql)
    {
        $retval = 1;
        $this->beginTransaction();
        foreach($arraySql as $strSql)
        {
            if($this->execSql($strSql) === false)
            {
                $retval = 0;
            }
        }
        if($retval == 0)
        {
            $this->rollback();
            return false;
        }
        else
        {
            $this->commit();
            return true;
        }
    }

    /**
     * checkFields 检查指定字段是否在指定数据表中存在
     *
     * @param String $table
     * @param array $arrayField
     */
    private function checkFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
        foreach($arrayFields as $key => $value)
        {
            if(!in_array($key, $fields))
            {
                return "Unknown column " . $key . " in field list.";
                //$this->outputError("Unknown column `$key` in field list.");
            }
        }
        
        return 0;
    }
    
    /**
     * DDcheckFields 检查指定字段是否在指定数据表中存在
     *
     * @param String $table
     * @param array $arrayField
     */
    private function DDcheckFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
        foreach($arrayFields as $key => $value)
        {
            if(!in_array($key, $fields))
            {
                return array("status" => 10001,"description" => "Unknown column " . $key . " in field list");
            }
        }

        return array("status" => 0, "description" => 'ok');
    }
    
    /**
     * getFields 获取指定数据表中的全部字段名
     *
     * @param String $table 表名
     * @return array
     */
    private function getFields($table)
    {
        $fields = array();
        $recordset = $this->dbh->query("SHOW COLUMNS FROM $table");
        $this->getPDOError();
        $recordset->setFetchMode(PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach($result as $rows)
        {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }
    
    /**
     * getPDOError 捕获PDO错误信息
     */
    private function getPDOError()
    {
        if($this->dbh->errorCode() != '00000')
        {
            $arrayError = $this->dbh->errorInfo();
            return $arrayError[2];
            //$this->outputError($arrayError[2]);
        }
        
        return 0;
    }
    
    /**
     * debug
     * 
     * @param mixed $debuginfo
     */
    private function debug($debuginfo)
    {
        print_r($debuginfo);
        exit();
    }
    
    /**
     * 输出错误信息
     * 
     * @param String $strErrMsg
     */
    private function outputError($strErrMsg)
    {
        exit('MySQL Error: '.$strErrMsg);
    }
    
    /**
     * Destruct 关闭数据库连接
     */
    public function Destruct()
    {
        $this->dbh = null;
    }
    
    //获取UUID
    public function db_select_uuid()
    {
        $ret_arr = $this->query('select uuid();','Row');
        if( $ret_arr == false )
        {
            return false;
        }
        
        if( !is_array($ret_arr) )
        {
            return false;
        }
        
        return $ret_arr['uuid()'];
    }
}

///////////////////PDO end//////////////////////////////////

?>
