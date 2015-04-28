<?php
/**
 * Created by Atiq.
 * Contact: atiq.cse.cu0506.su@gmail.com (skype : atiq.cu)
 * Date: 4/7/2015
 * Time: 11:16 AM
 */

//check this out http://php.net/manual/en/mysqli.prepare.php

namespace commonSql;
use Mysqli;

class SQLi extends mysqli
{

    public $dbLink ;
    protected $tableName ;
    private $primaryKey = 'id' ;
    public $lastError = null ;
    public $lastMsg = null ;
    public $lastQuery;
    public $insertId;

    public function __construct($user, $pass, $db, $host='localhost')
    {
        if (is_object($this->dbLink) && ($this->dbLink instanceof SQLi) ){
            return $this->dbLink ;
        }
        return $this->connectDb($host, $user, $pass, $db) ;

    }

    /**
     * @param $host
     * @param $user
     * @param $pass
     * @param $db
     * @return SQLi
     */
    private function connectDb($host, $user, $pass, $db){

        parent::__construct($host, $user, $pass, $db);
        if (mysqli_connect_error()) {
            die('Connect Error : ' . mysqli_connect_errno() . '--' . mysqli_connect_error());
        }
        $this->dbLink = $this ;
        return $this->dbLink;
    }

    /**
     * @param $tableName
     * @return bool
     */
    public function setDomain($tableName){
        if(strlen($tableName)>0){
            $this->tableName = $tableName ;
            return true;
        }
        return false ;
    }


    /**
     * @param $dbName
     * @return bool
     */
    public function changeDb($dbName){
        return $this->select_db($dbName);
        //return $this->dbLink->select_db($dbName);
    }

    /**
     * Run a valid query.... sanitize :todo
     * @param $qry
     * @return mixed
     */
    private function runQuery($qry){
        $this->lastQuery = $qry;
        return $this->query($qry);
    }


    /**
     * @param null $id
     * @param bool $arrayIdx
     * @param int $limit
     * @param int $offset
     * @return array|bool|int
     */
    public function read($id=null, $arrayIdx= true, $limit=0, $offset=0){
        $query = "SELECT * FROM $this->tableName ";
        if($id!=NULL and $id>0) $query .= " WHERE `$this->primaryKey` = $id " ;
        if($limit>0 && $offset>=0)    $query .= " LIMIT $limit OFFSET $offset" ;
        return $this->runSelectQuery($query, $arrayIdx);
    }

    /**
     * @param array $whereArr
     * @param bool $arrayIdx
     * @param int $limit
     * @param int $offset
     * @return array|bool|int
     */
    public function listAll($whereArr=array(), $arrayIdx= true, $limit=0, $offset=0){
        $query = "SELECT * FROM $this->tableName ";
        if(is_array($whereArr) && count($whereArr)>0){
            $query .= 'WHERE '.$this->createWhere($whereArr);
        }
        if($limit>0 && $offset>=0)    $query .= " LIMIT $limit OFFSET $offset " ;
        return $this->runSelectQuery($query, $arrayIdx);
    }

    /**
     * @param array $whereArr
     * @param bool $arrayIdx
     * @param bool $onlyZeroIndex
     * @param string $alias
     * @return array|bool|int
     */
    public function count($whereArr=array(), $arrayIdx=false, $onlyZeroIndex=false, $alias ='count'){
        $query = "SELECT COUNT(*) AS $alias FROM $this->tableName ";
        if(is_array($whereArr) && count($whereArr)>0){
            $query .= 'WHERE '.$this->createWhere($whereArr);
        }
        if(true === $onlyZeroIndex){
            $result = $this->runSelectQuery($query, $arrayIdx);
            if(is_array($result) && count($result)>0) return $result[0];
            return $result;
        }
        return $this->runSelectQuery($query, $arrayIdx);
    }


    /**
     * @param string $clause
     * @param bool $arrayIdx
     * @param bool $onlyZeroIndex
     * @param string $alias
     * @return array|bool|int
     */
    public function countWithClause($clause='', $arrayIdx=false, $onlyZeroIndex=true, $alias ='count'){
        $query = "SELECT COUNT(*) AS $alias FROM $this->tableName ";
        if(strlen($clause)>5) $query .= $clause ;
        if(true === $onlyZeroIndex){
            $result = $this->runSelectQuery($query, $arrayIdx);
            if(is_array($result) && count($result)>0) return $result[0];
            return $result;
        }
        return $this->runSelectQuery($query, $arrayIdx);
    }


    /**
     * @param $id
     * @return bool
     */
    public function delete($id){
        if($id!=NULL and $id>0){
            $qry = "DELETE FROM $this->tableName WHERE `$this->primaryKey` = $id ";
            if($this->runQuery($qry)) return true ;
            $this->lastError = 'Query failed -('.$qry.')'.$this->dbLink->error;
            return false;
        }
        $this->lastError = 'Invalid Input';
        return false;
    }




    /**
     * todo : free mysql result...
     * @param $qry
     * @param null $arrayKey
     * @return array|bool|int
     */
    private function runSelectQuery($qry, $arrayKey = null){

        $return = array();
        $result = $this->runQuery($qry);
        if($result === false){
            $this->lastError = 'Query run failed! --('.$qry.')'.$this->dbLink->error;
            return false;
        }
        if($result->num_rows>0){

            if($arrayKey ===true){
                while($rows=$result->fetch_assoc()){
                    $return[$rows[$this->primaryKey]]= $rows ;   // array index by primary key....
                }
            }elseif($arrayKey===false){
                while($rows=$result->fetch_assoc()){
                    $return[]= $rows ;       // simple numeric index array
                }
            }elseif(strlen($arrayKey)>=2){
                while($rows=$result->fetch_assoc()){
                    $return[$rows[$arrayKey]]= $rows ;       // simple numeric index array
                }
            }else{
                while($rows=$result->fetch_assoc()){
                    $return[0][]= $rows ;       // simple numeric index array
                    $return[1][$rows[$this->primaryKey]]= $rows ;   // array by primary key....
                }
            }
            return $return ;

        }elseif($result->num_rows==0){
            $this->lastError = 'every thing OK . 0 result found for select query. -'.$qry;
            return 0;

        }else{
            $this->lastError = 'Something error happened on select query : '.$qry ;
            return false ;
        }
    }



    /**
     * @param $qry
     * @param $returnId
     * @return bool|mixed
     */
    public function create($qry, $returnId=false){
        if($this->runQuery($qry)){
            $this->insertId = $this->insert_id;
            if($returnId) return $this->insertId;
            return true ;
        }
        $this->lastError = 'Create Query failed -('.$qry.')'.$this->dbLink->error;
        return false;
    }

    /**
     * @param $qry
     * @return bool
     */
    public function update($qry){
        if($this->runQuery($qry)) return true ;
        $this->lastError = 'Update Query failed -('.$qry.')'.$this->dbLink->error;
        return false;
    }

    //---------- less used -------
    /**
     * @return mixed
     */
    public function getDomain(){
        return $this->tableName ;
    }


    /**
     * @return string
     */
    public function getPrimary(){
        return $this->primaryKey ;
    }

    /**
     * @param $str
     * @return bool
     */
    public function setPrimary($str){
        if(strlen($str)>0){
            $this->primaryKey = $str ;
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public function getCurrentDbName(){
        //if ($result = $this->dbLink->query("SELECT DATABASE()"))
        if ($result = $this->query("SELECT DATABASE()")) {
            $row = $result->fetch_row();
            $result->close();
            return $row[0];
        }
        return false;
    }



    /**
     * @param null $tbl
     * @return array|bool|int
     */
    public function listPrimaryKeyColumn($tbl=null){

        $db= $this->getCurrentDbName();

        $qry = "SELECT Col.Column_Name AS primary_key_column, Col.Table_Name AS table_name, Tab.TABLE_SCHEMA AS database_schema
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS Tab, INFORMATION_SCHEMA.KEY_COLUMN_USAGE Col
                WHERE Col.Constraint_Name = Tab.Constraint_Name
                  AND Col.Table_Name = Tab.Table_Name
                  AND Constraint_Type = 'PRIMARY KEY' AND Tab.TABLE_SCHEMA = '$db'" ;

        if($tbl!=null) $qry .= " AND Col.Table_Name = '$tbl'" ;
        return $this->runSelectQuery($qry, false);
    }

    /**
     * @param bool $forDb
     * @return array|bool|int
     */
    public function getTableList($forDb = true ){

        $db= $this->getCurrentDbName();
        $qry = "SELECT TABLE_NAME AS table_name, TABLE_SCHEMA AS database_schema, TABLE_CATALOG AS table_catalog, TABLE_ROWS AS table_rows, COALESCE(AUTO_INCREMENT, 1) AS auto_incr, VERSION AS version FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
        if($forDb) $qry .= " AND TABLE_SCHEMA='$db'";

        return $this->runSelectQuery($qry, false);
    }

    //-------- new testing function---------


    /**
     * @param string $tableName
     * @return array|bool|int
     */
    function getTableColumnList($tableName=''){

        $db= $this->getCurrentDbName();
        $qry="SELECT COLUMN_NAME, TABLE_NAME, TABLE_SCHEMA, TABLE_CATALOG, IS_NULLABLE, DATA_TYPE, COLUMN_TYPE, PRIVILEGES, EXTRA
                FROM INFORMATION_SCHEMA.COLUMNS COL
                WHERE COL.TABLE_SCHEMA='$db' ";
        if(strlen(trim($tableName))>0) $qry .= " AND TABLE_NAME='$tableName'";
        $qry .= " ORDER BY `COL`.`TABLE_NAME` ASC ";

        return $this->runSelectQuery($qry, false);
    }

    //-------------------------


    /**
     * @param $qry
     * @param bool $arrayIdx
     * @return array|bool|int
     */
    public function readByQuery($qry, $arrayIdx= true){
        return $this->runSelectQuery($qry, $arrayIdx);
    }


    /**
     * @param $fldArr
     * @return bool
     */
    public function createFromArray($fldArr){
        if(is_array($fldArr)) {
            $query = $this->helpCreate($fldArr);
            return $this->create($query);
        }
        $this->lastError = 'For update array first arg should be array. Not array given!' ;
        return false;
    }

    /**
     * @param $fldArr
     * @param array $whereArr
     * @return bool
     */
    public function updateFromArray($fldArr, $whereArr=array()){
        if(is_array($fldArr)){
            $query = $this->helpUpdate($fldArr);
            if(is_array($whereArr) && count($whereArr)>0){
                $query .= ' WHERE '.$this->createWhere($whereArr);
            }
            return $this->update($query);
        }
        $this->lastError = 'For update array first arg should be array. Not array given!' ;
        return false;
    }

    /**
     * @param $arr
     * @return bool|string
     */
    public function createWhere($arr){
        if(!is_array($arr)) return false;
        $ret='';
        $sap= '';
        foreach($arr as $key=>$val){
            $ret .= $sap." `$key` = '".$this->dbLink->real_escape_string($val)."' ";
            $sap = ' AND ';
        }
        return $ret ;
    }

    /**
     * @param $array
     * @return string
     */
    public function helpCreate($array){
        if(!is_array($array)) return false;
        $qry = "INSERT INTO $this->tableName (";
        $vl='';
        $sap = '';
        foreach($array as $fld=>$val){
            $qry .= "$sap `$fld`" ;
            $vl .= "$sap '".$this->dbLink->real_escape_string($val)."'" ;
            $sap= ',';
        }

        $query = $qry . ") values (" . $vl . " )" ;
        return $query ;
    }

    /**
     * @param $arr
     * @return string
     */
    public function helpUpdate ($arr){
        //UPDATE table_name SET column1=value1,column2=value2,...  WHERE some_column=some_value;
        if(!is_array($arr)) return false;
        $qry = "UPDATE $this->tableName SET ";
        $vl='';
        $sap = '';
        foreach($arr as $fld=>$val){
            $vl .= "$sap `$fld`= '".$this->dbLink->real_escape_string($val)."'" ;
            $sap= ',';
        }
        $query = $qry . " " . $vl ;
        return $query ;
    }


    /**
     * @param $var
     * @param bool $die
     * @param null $die_msg
     * @param bool $varDump
     */
    public function dump($var, $die=true, $die_msg=NULL, $varDump=false){

        if('comment'===$die){   echo '<!-- <pre>';}
        else{ echo '<pre>';}

        if($varDump===true)  var_dump($var);
        elseif(is_array($var) && count($var)>0) print_r($var);
        elseif(is_object($var)) print_r($var);
        else var_dump($var);

        if($die==='comment') echo '-->';
        else echo '</pre>';
        if($die===true)	die('Died from dump helper...'.$die_msg);

    }

    /**
     * @param bool $die
     */
    public function dumps($die=true){
        echo '<pre>';
        $args = func_get_args();
        foreach ($args as $arg){
            if(is_array($arg) && count($arg)>0) print_r($arg);
            elseif(is_object($arg)) print_r($arg);
            else var_dump($arg);

        }
        echo "</pre>";
        if($die===true)	die('Died from dumps helper...');
    }

    /**
     * printing html break
     */
    public static function printBreak(){
        echo '<br/>';
    }

    /**
     * Under construction :todo
     * update for boolean field
     * @param $id
     * @param $fieldName
     * @return bool
     */
    public function  changeBoolField($id, $fieldName){
        if($id!=NULL and $id>0){
            $qry = "delete from $this->tableName where `$this->primaryKey` = $id ";
            if($this->runQuery($qry)) return true ;
            $this->lastError = 'Query failed -('.$qry.')'.$this->dbLink->error;
            return false;
        }
        $this->lastError = 'Invalid Input';
        return false;
    }
}