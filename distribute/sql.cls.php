<?php
/**
 * Created by Md. Atiqur Rahman
 * Email: atiq.cse.cu0506.su@gmail.com
 * Skype: atiq.cu
 * Date: 10/03/2016
 * Time: 1:04 PM
 *
 * This class is a reviewed version of SQLi with more security in mind.
 */



namespace secureSQL;
use Mysqli;


/**
 * Extended mysqli class to serve frequently needed functionality with more secured way and performance.
 * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
 * @version 1.0.5
 * @package commonSql
 */
class SQLi extends mysqli
{

    public $dbLink ;
    public $lastError = null ;
    public $lastMsg = null ;
    public $lastQuery;
    public $insertId;

    protected $tableName ;
    protected $tablePrefix = '' ;
    private $primaryKey = 'id' ;

    const PRE_START = '<pre>';
    const PRE_END = '</pre>';
    const BR = '<br/>';

    /**
     * Constructor method
     * @param string $user
     * @param string $pass
     * @param string $db
     * @param string $host
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since - 1.0.0
     */
    public function __construct($user, $pass, $db, $host='localhost')
    {
        if (is_object($this->dbLink) && ($this->dbLink instanceof SQLi) ){
            return $this->dbLink ;
        }
        return $this->connectDb($host, $user, $pass, $db) ;

    }

    /**
     * Connecting to database server.
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
     * Change database within this server.
     * @since 1.0.0
     * @param $dbName
     * @return bool
     */
    public function changeDb($dbName){
        return $this->select_db($dbName);
    }

    /**
     * Get current selected database.
     * @since 1.0.0
     * @author  Md. Atiqur Rahman
     * @return bool
     */
    public function getCurrentDbName(){
        $qry = 'SELECT database() as db';
        $dbs = $this->runSelectQuery($qry, false);
        if(!empty($dbs[0])) return $dbs[0]['db'];
        return false;
    }

    /**
     * Get current database name. An alias of getCurrentDbName.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @return bool
     */
    public function getDatabaseName(){
        if ($result = $this->query("SELECT DATABASE()")) {
            $row = $result->fetch_row();
            $result->close();
            return $row[0];
        }
        return false;
    }


    /**
     * Set database table name.
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
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @return bool
     */
    private function resetDomain(){
        $this->tableName = null ;
        $this->lastMsg = 'Domain reset. No domain currently selected.';
        return true;
    }

    /**
     * Alias of setDomain.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param $tableName
     * @return bool
     */
    public function setTable($tableName){

        return $this->setDomain($tableName) ;
    }


    /**
     * Get current table name
     * @author  Md. Atiqur Rahman
     * @since 1.0.0
     * @return mixed
     */
    public function getDomain(){
        return $this->tableName ;
    }

    /**
     * Alias of getDomain method.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @return mixed
     */
    public function getTableName(){
        return $this->tableName ;
    }

    /**
     * Get actual name of the database table name.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @return string
     */
    public function getCurrentDomainName(){
        return $this->tablePrefix.$this->tableName ;
    }


    /**
     * Full table name used in select query
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @return string
     */
    public function getTableWithPrefix(){
        return $this->tablePrefix.$this->tableName ;
    }


    /**
     * Delete a table from database.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @param null $table - name of the table without prefix
     * @param bool $giveMessage
     * @return bool|\mysqli_result
     */
    public function deleteDomain( $table = null, $giveMessage = true){

        $resetFlag = false;
        if(empty($table)){
            $domainName = $this->tablePrefix.$this->tableName ;
            $resetFlag = true;
        }
        else{
            $countLn = strlen($this->tablePrefix);
            if($countLn>0){
                $sb = substr($table, 0, $countLn);
                if($sb !=false) $table = substr($table, $countLn);
            }

            $domainName = $this->tablePrefix.$table ;
            if($table == $this->tableName) $resetFlag = true;
        }

        $qry = 'DROP TABLE `'.$domainName.'`';
        $result = $this->runQuery($qry) ;
        if($giveMessage){
            if($result == true){
                echo 'Domain :'.$domainName.' successfully deleted.';
                if($resetFlag)  $this->resetDomain();
            }
            else echo 'Domain :'.$domainName.' deletion failed. --'.$this->lastError;
        }
        return $result;
    }


    /**
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $domainName - table name with prefix.
     * @param bool $giveMessage
     * @return bool|\mysqli_result
     */
    public function deleteDomainRaw($domainName, $giveMessage = true){

        $qry = 'DROP TABLE `'.$domainName.'`';
        $result = $this->runQuery($qry) ;
        if($giveMessage){
            if($result == true){
                echo 'Domain :'.$domainName.' successfully deleted.';
                if($domainName == $this->tableName) $this->resetDomain();
            }
            else echo 'Domain :'.$domainName.' deletion failed. --'.$this->lastError;
        }
        return $result;
    }


    /**
     * For debugging purpose only.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @param bool $die
     * @return void
     */
    public function debugMsg($die = true){

        echo self::PRE_START;
        echo $this->lastMsg.self::BR;
        echo $this->lastQuery.self::BR;
        echo $this->lastError.self::BR;
        echo self::PRE_END;

        if($die == true) die('from debug info...');
    }

    /**
     * Create a sample database table.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $name
     * @param bool $giveMessage
     * @return bool|\mysqli_result
     */
    public function createDomain($name, $giveMessage = true){

        $domainName = $this->tablePrefix.$name;
        $qry = "CREATE TABLE `".$domainName."`(
				  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				  `active` ENUM('0','1') NOT NULL DEFAULT '1',
				  `order` INT UNSIGNED NOT NULL DEFAULT 1,
				  `created_by` BIGINT UNSIGNED NOT NULL DEFAULT 1,
				  `update_by` BIGINT UNSIGNED,
				  `created` DATETIME NOT NULL DEFAULT NOW(),
				  `updated` DATETIME,
				  PRIMARY KEY (`id`)
				);
				";
        $result = $this->runQuery($qry) ;

        if($giveMessage){
            if($result == true) echo 'Domain :'.$domainName.' created.';
            else echo 'Domain :'.$domainName.' creation failed. --'.$this->lastError;
        }
        return $result;
    }


    /**
     * Alias of createDomain method.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @param $name
     * @param bool $giveMessage
     * @return bool|\mysqli_result
     */
    public function createSampleTable($name, $giveMessage = true){
        return $this->createDomain($name, $giveMessage);
    }


    /**
     * Set/Change table prefix.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param string $str
     * @return void
     */
    public function setTablePrefix($str=''){
        $this->tablePrefix = $str;
    }


    /**
     * Alias of setTablePrefix.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param string $str
     * @return void
     */
    public function setDomainPrefix($str=''){
        $this->setTablePrefix($str);
    }


    /**
     * Get table prefix.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @return string
     */
    public function getTablePrefix(){
        return $this->tablePrefix;
    }

    /**
     * Alias of getTablePrefix.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @return string
     */
    public function getDomainPrefix(){
        return $this->getTablePrefix();
    }

    /**
     * Get current primary key column name.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @return string
     */
    public function getPrimary(){
        return $this->primaryKey ;
    }


    /**
     * Set primary key filed column name.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param string $str
     * @return bool
     */
    public function setPrimary($str = 'id'){
        if(strlen($str)>0 && !is_bool($str)){
            $this->primaryKey = $str ;
            return true;
        }
        return false;
    }


    /**
     * Help for remembering argument format.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param bool $die
     * @return void
     */
    public function helpMe($die = true){

        echo self::PRE_START ;
        echo 'Where Array::'.PHP_EOL;
        echo self::PRE_END ;
        if($die) die('died from help me........in sql class');
    }


    /**
     * Count total rows of current domain.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @lastUpdated 20151125
     * @param string $whereClause - if want to filter like - where user_id=1 ;
     * @return int
     */
    public function countRows($whereClause=''){
        $query = "SELECT COUNT(*) as total FROM ".$this->tablePrefix.$this->tableName." ";
        $query .= ' '.$whereClause ;
        $total = $this->runSelectQuery($query, false);
        return intval($total[0]['total']);
    }

    /**
     * Delete a row by primary key from table.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param $id
     * @return bool|\mysqli_result
     */
    public function delete($id){

        if(!empty($id)){
            $qry = "DELETE FROM ".$this->tablePrefix.$this->tableName." WHERE `$this->primaryKey` = $id ";
            return $this->runQuery($qry) ;
        }
        $this->lastError = 'Invalid Input for delete a row.';
        return false;
    }

    /**
     * Alias of delete method.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $id
     * @return bool|\mysqli_result
     */
    public function deleteById($id){

        return $this->delete($id);
    }


    /**
     * Basic and simple sanitize.
     * @author Md. Atiqur Rahman <atiqur@shaficonsultancy.com, atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $data
     * @param bool $doubleCheck - some times it won't work only htmlentities from copy -pasted text then make it true.
     * @return string
     */
    public function sanitizeSimple($data, $doubleCheck = false){
        if($doubleCheck) $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return htmlentities($data, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Removing non-utf char and double space then making html escape.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @param $data
     * @return mixed|string
     */
    public function sanitizeSmart($data){

        $str = trim($data);
        $str = iconv("UTF-8", "UTF-8//IGNORE", $str); // drop all non utf-8 characters

        // this is some bad utf-8 byte sequence that makes mysql complain - control and formatting i think
        $str = preg_replace('/(?>[\x00-\x1F]|\xC2[\x80-\x9F]|\xE2[\x80-\x8F]{2}|\xE2\x80[\xA4-\xA8]|\xE2\x81[\x9F-\xAF])/', '-', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        $str = $this->sanitizeSimple($str);
        return $str;
    }


    /**
     * Get all rows or single row from database table.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param null $id
     * @param bool $arrayIdx - fetch method i.e true -- fetch_assoc,
     * @param int $limit
     * @param int $offset
     * @param string $orderByClause
     * @return array|bool|int
     */
    public function read($id=null, $arrayIdx= true, $limit=0, $offset=0, $orderByClause=''){
        $query = "SELECT * FROM ".$this->tablePrefix.$this->tableName." ";
        if(!empty($id)) $query .= " WHERE `$this->primaryKey` = $id " ;
        $query .= ' '.$orderByClause ;
        if($limit>0 && $offset>=0)    $query .= " LIMIT $limit OFFSET $offset" ;
        return $this->runSelectQuery($query, $arrayIdx);
    }


    /**
     * Alias of read method
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param null $id
     * @param bool $arrayIdx
     * @param int $limit
     * @param int $offset
     * @param string $orderByClause
     * @return array|bool|int
     */
    public function findById($id=null, $arrayIdx= true, $limit=0, $offset=0, $orderByClause=''){

        return $this->read($id, $arrayIdx, $limit, $offset, $orderByClause);
    }


    /**
     * Alias of read method
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param null $id
     * @param bool $arrayIdx
     * @param int $limit
     * @param int $offset
     * @param string $orderByClause
     * @return array|bool|int
     */
    public function getRow($id=null, $arrayIdx= true, $limit=0, $offset=0, $orderByClause=''){

        return $this->read($id, $arrayIdx, $limit, $offset, $orderByClause);
    }


    /**
     * Return all entry of the table.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @param string $orderByClause
     * @return array|bool|int
     */
    public function getAll($orderByClause=''){

        return $this->read(null, true, 0, 0, $orderByClause);
    }


    /**
     * An alias of findRows method.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param array $fields
     * @param array $where
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param array $groupBy
     * @return string
     */
    public function getRows($fields=array('*'), $where = array(), $orderBy = array(), $limit = 0, $offset = 0, $groupBy = array()){

        return $this->findRows($fields, $where, $orderBy, $limit, $offset, $groupBy);
    }


    /**
     * Get all rows from the domain. improved version of listAll method.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param array|NULL|string $select i.e - array()|'id, name'| array('id', 'name', 'dd'=>'count(myid)')|null
     * @param array $where - i.e - see processArrayForWhere method.
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param array $groupBy
     * @param bool $arrayIdx
     * @return string
     */
    public function findRows($select = array('*'), $where = array(), $orderBy = array(), $limit = 0, $offset = 0, $groupBy = array(), $arrayIdx = false){

        $qry ='SELECT '.($this->processArrayForQuery($select) ? :' * ').' FROM '.$this->tablePrefix.$this->tableName;
        if(is_array($where) && !empty($where)) $qry .= $this->processArrayForWhere($where);
        if(!empty($orderBy)) $qry .= $this->processArrayForOrderBy($orderBy);
        if(!empty($limit) && is_numeric($limit)) $qry .= " LIMIT $limit OFFSET $offset" ;
        if(!empty($groupBy)) $qry .= ' GROUP BY '.$this->processArrayForQuery($groupBy);

        return $this->runSelectQuery($qry, $arrayIdx);
    }

    /**
     * Get all rows from database table.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param array $whereArr
     * @param bool $arrayIdx
     * @param int $limit
     * @param int $offset
     * @param string $otherClause
     * @return array|bool|int
     *
     * @created - forgotten
     * @lastUpdated - 20151125
     */
    public function listAll($whereArr=array(), $arrayIdx= true, $limit=0, $offset=0, $otherClause=''){

        $query = "SELECT * FROM ".$this->tablePrefix.$this->tableName." ";
        if(is_array($whereArr) && !empty($where)) $query .= $this->processArrayForWhere($where);
        $query .= ' '.$otherClause ;
        if(!empty($limit) && is_numeric($limit))    $query .= " LIMIT $limit OFFSET $offset " ;

        return $this->runSelectQuery($query, $arrayIdx);
    }


    /**
     * Update domain.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param $fldArrVal
     * @param $matchingVal
     * @param string $matchingField
     * @return bool
     */
    public function updateFieldsById($fldArrVal, $matchingVal, $matchingField='id'){

        if(!empty($fldArrVal)){
            $qry = "UPDATE ".$this->tablePrefix.$this->tableName." SET ".$this->processArrayForUpdateQuery($fldArrVal)." WHERE $matchingField = '$matchingVal'";
            return $this->update($qry);
        }
        $this->lastError = 'updateFieldsById : Field list empty!';
        return false;
    }


    /**
     * Insert query.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $qry
     * @param bool $returnId
     * @return bool|mixed
     */
    public function create($qry, $returnId=false){
        if($this->runQuery($qry)){
            $this->insertId = $this->insert_id;
            if($returnId) return $this->insertId;
            return true ;
        }
        $this->lastError = 'Create Query failed -('.$qry.') :: '.$this->lastError;
        return false;
    }

    /**
     * Alias of create query.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param $qry
     * @param bool $returnId
     * @return bool|mixed
     */
    public function insert($qry, $returnId=false){

        return $this->create($qry, $returnId);
    }



    /**
     * Run queries that return only boolean.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $qry
     * @return bool
     */
    public function update($qry){
        if($this->runQuery($qry)) return true ;
        $this->lastError = 'Update Query failed -('.$qry.')'.$this->dbLink->error;
        return false;
    }

    /**
     * Run a valid query.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @updated since 1.0.1
     * @param $qry
     * @return bool|\mysqli_result
     */
    private function runQuery($qry){
        $this->lastQuery = $qry;
        try{
            $return = $this->query($qry);
            $this->lastError = $this->error;
        }catch (\Exception $ee){
            $this->lastError .= $ee->getMessage();
            $return = false;
        }
        return $return;
    }

    /**
     * todo : free mysql result...add options for metadata like lastId, affectedRows etc
     * @param $qry
     * @param null $arrayKey
     * @return array|bool|int
     */
    private function runSelectQuery($qry, $arrayKey = null){

        $return = array();
        $result = $this->runQuery($qry);
        if($result === false){
            $this->lastError = 'Select Query failed! --('.$qry.') :: '.$this->lastError;
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
                //array index is a value of a certain column
                while($rows=$result->fetch_assoc()){
                    $return[$rows[$arrayKey]]= $rows ;       // simple numeric index array
                }
            }else{
                while($rows=$result->fetch_assoc()){
                    $return['numeric'][]= $rows ;       // simple numeric index array
                    $return['byPrimary'][$rows[$this->primaryKey]]= $rows ;   // array by primary key....
                }
            }
            return $return ;

        }elseif($result->num_rows==0){
            $this->lastError = 'every thing OK . 0 result found for select query. -'.$qry;
            return $return;

        }else{
            $this->lastError = 'Something error happened on select query : '.$qry ;
            return false ;
        }
    }

    /**
     * todo : free mysql result...add options for metadata like lastId, affectedRows etc
     * @param $qry
     * @param null $arrayKey
     * @return array|bool|int
     */
    public function selectQuery($qry, $arrayKey = null){

        $return = array();
        $result = $this->runQuery($qry);
        if($result === false){
            $this->lastError = 'Select Query failed! --('.$qry.') :: '.$this->lastError;
            return false;
        }
        if($result->num_rows>0){

            while($rows=$result->fetch_assoc()){
                $return[]= $rows ;
            }

            return $return ;

        }elseif($result->num_rows==0){
            $this->lastError = 'every thing OK . 0 result found for select query. -'.$qry;
            return $return;

        }else{
            $this->lastError = 'Something error happened on select query : '.$qry ;
            return false ;
        }
    }


    /**
     * Process an array for building query
     * @usages - for input : array()|'id, name'| array('id', 'name', 'dd'=>'count(myid)')|null
     * @usages - will return false|'id, name'|'id, name, count(myid) as dd' |''
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param $array
     * @return string | boolean
     */
    private function processArrayForQuery($array){

        $tmp = '';
        if(is_array($array)){

            if(count($array) <1) return false;

            $sap = ' ';
            foreach($array as $key=>$val){

                if(is_numeric($key)){
                    $tmp .= $sap.$val;

                }else{
                    $tmp .= $sap.$val.' AS '.$key;
                }
                $sap = ', ';
            }

        }elseif(is_string($array)){
            $tmp = $array;

        }else{
            $tmp = strval($array) ;
        }

        return $tmp;
    }

    /**
     * Process where query string from array....
     * $posted_data[] = array('field' => 'id', 'operator' => 'eq', 'value' => 5, 'logic'=>'AND');
     * $posted_data[] = array('field' => 'myid', 'operator' => 'eq', 'value' => 5, 'logic'=>'AND');
     * $posted_data[] = array('field' => 'name', 'operator' => 'contains', 'value' => 'atq');
     * $posted_data['age'] = 10;
     * $posted_data['height'] = 5.2;
     * $posted_data[] = ' time < NOW()';
     * Output : WHERE id = "5"  AND myid = "5"  AND name LIKE "%atq%"  AND age = 10 AND height = 5.2 AND time < NOW()
     *
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param array $where
     * @param string $defLogic
     * @param bool $withWhere
     * @return string
     *
     * @created - forgotten.
     * @lastUpdate - 20151125
     */
    public function processArrayForWhere(array $where, $defLogic = 'AND', $withWhere = true){

        $return = '';
        if(count($where)>0){
            $return .= $withWhere? 'WHERE ' : '' ;
            $firstLoop = true ;

            foreach($where as $key=>$val){
                if(is_array($val) && isset($val['field']) && isset($val['operator']) && isset($val['value']) ){

                    if(!empty($val['logic'])) $logic = $val['logic'];
                    else $logic = $defLogic;

                    $qry = $this->getOperatorString($val).' ';
                    if($firstLoop) $return .= $qry ;
                    else $return .= $logic.' '.$qry;

                }elseif(!is_numeric($key)){
                    $logic = $defLogic;
                    $qry = $key.' = '.$val.' ';
                    if($firstLoop) $return .= $qry ;
                    else $return .= $logic.' '.$qry;

                }elseif(is_numeric($key)){
                    $logic = $defLogic;
                    $qry = $val.' ';
                    if($firstLoop) $return .= $qry ;
                    else $return .= $logic.' '.$qry;
                }else {
                    $logic = $defLogic;
                    $qry = ' 1 = 1 ';
                    if($firstLoop) $return .= $qry ;
                    else $return .= ' '.$logic.' '.$qry;
                }
                $firstLoop = false;
            }
        }
        return $return;
    }


    /**
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param string|array|null $array - i.e array('id desc', 'name'=>'asc') | 'name , id' | null
     * @param bool $withOrderBy
     * @return string
     *
     * @created - forgotten.
     * @lastUpdated - 20151125
     */
    public function processArrayForOrderBy($array = '', $withOrderBy = true){

        $return = '';
        if(!empty($array)){
            $return .= $withOrderBy? 'ORDER BY ' :'' ;

            if(is_array($array)){
                $sap = '';
                foreach($array as $key=>$val){

                    if(is_numeric($key)){
                        $return .= $sap.$val;

                    }else{
                        $val = (strtolower($val) == 'asc' || strtolower($val) == 'desc') ? strtoupper($val) : '';
                        $return .= $sap.$key.' '.$val;
                    }
                    $sap = ', ';
                }

            }elseif(is_string($array)){
                $return .= $array;

            }else $return = '';
        }

        return strval($return) ;
    }


    /**
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $arr
     * @param string $quote
     * @param string $sep
     * @return string
     */
    private function processArrayForUpdateQuery($arr, $quote="'", $sep=', '){

        $q='';
        $sap = '';
        if(is_array($arr)){
            foreach($arr as $key=>$val){
                $q .=$sap.$key.'='.$quote.$val.$quote;
                $sap = $sep;
            }
        }elseif(is_string($arr)){
            $q .=$arr;

        }else{
            $q .=strval($arr);
        }

        return $q;
    }


    /**
     * Build to serve kendo filter query, for now adapting for my class.
     * -todo : update it for kendo filtering.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param array $arr
     * @return string
     */
    private function getOperatorString($arr = array()){

        $operatorString = '';
        if(!empty($arr)){
            switch ($arr['operator']) {
                case 'eq':
                    $operatorString = $arr['field'] . ' = "' . $arr['value'] . '" ';
                    break;
                case 'neq':
                    $operatorString = $arr['field'] . ' != "' . $arr['value'] . '" ';
                    break;
                case 'startswith':
                    $operatorString = $arr['field'] . ' LIKE "' . $arr['value'] . '%" ';
                    break;
                case 'contains':
                    $operatorString = $arr['field'] . ' LIKE "%' . $arr['value'] . '%" ';
                    break;
                case 'doesnotcontain':
                    $operatorString = $arr['field'] . ' NOT LIKE "%' . $arr['value'] . '%" ';
                    break;
                case 'endswith':
                    $operatorString = $arr['field'] . ' LIKE "%' . $arr['value'] . '" ';
                    break;
                case 'gte':
                    $operatorString = $arr['field'] . ' >=  "' . $arr['value'] . '" ';
                    break;
                case 'gt':
                    $operatorString = $arr['field'] . ' > "' . $arr['value'] . '" ';
                    break;
                case 'lte':
                    $operatorString = $arr['field'] . ' <= "' . $arr['value'] . '" ';
                    break;
                case 'lt':
                    $operatorString = $arr['field'] . ' < "' . $arr['value'] . '" ';
                    break;
                case 'eqy':
                    $operatorString = 'YEAR(vc.cat_date)'.' = "'.$arr['value'].'" ';
                    break;
                case 'eqm':
                    $operatorString = 'date_format(vc.cat_date, "%M")'.' = "'.$arr['value'].'" ';
                    break;
                case 'eqd':
                    $operatorString = 'date_format(vc.cat_date, "%d")'.' = "'.$arr['value'].'" ';
                    break;
                default:
                    $operatorString = $arr['field'] . ' = "' . $arr['value'] . '" ';
                    break;
            }
        }
        return $operatorString;
    }

    //done but QA not complete


    /**
     * Process kendo sort array.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param string|array|null $array - i.e array('id desc', 'name'=>'asc') | 'name , id' | null
     * @param bool $withOrderBy
     * @return string
     */
    public function processKendoSortArray($array = array(), $withOrderBy = true){

        $return = '';
        if(!empty($array)){
            $return .= $withOrderBy? ' ORDER BY ' :'' ;

            if(is_array($array)){
                $sap = '';
                foreach($array as $key=>$conf){

                    $return .= $sap.$conf['field'].' '.$conf['dir'];
                    $sap = ', ';
                }
            }else $return = '';
        }

        return strval($return) ;
    }

    //end of working area


    /**
     * List primary key column name.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
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
     * List all table name of current database.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param bool $forCurrentDb
     * @param bool $onlyTableName
     * @return array|bool|int|string
     *
     * @created - forgotten
     * @lastUpdated - 20151125
     */
    public function getTableList($forCurrentDb = true , $onlyTableName = true ){

        $db= $this->getCurrentDbName();
        $qry = "SELECT TABLE_NAME AS table_name, TABLE_SCHEMA AS database_schema, TABLE_CATALOG AS table_catalog, TABLE_ROWS AS table_rows, COALESCE(AUTO_INCREMENT, 1) AS auto_incr, VERSION AS version FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
        if($forCurrentDb) $qry .= " AND TABLE_SCHEMA='$db'";

        $data = $this->runSelectQuery($qry, false);

        if($onlyTableName && !empty($data)){
            $ret = '';
            foreach($data as $row){
                $ret[$row[ 'database_schema']][] = $row[ 'table_name'];
            }
            return $ret ;
        }
        return $data ;
    }

    /**
     * Get the database view list. todo - need to up-to-date
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param bool $forDb
     * @return array|bool|int
     */
    public function getViewList($forDb = true ){

        $db= $this->getCurrentDbName();
        $qry = "SELECT TABLE_NAME AS table_name, TABLE_SCHEMA AS database_schema, TABLE_CATALOG AS table_catalog, TABLE_ROWS AS table_rows, COALESCE(AUTO_INCREMENT, 1) AS auto_incr, VERSION AS version FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'VIEW'";
        if($forDb) $qry .= " AND TABLE_SCHEMA='$db'";

        return $this->runSelectQuery($qry, false);
    }


    /**
     * List all columns of table(s)
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param string $tableName
     * @param bool $onlyColumn
     * @return array|bool|int
     *
     * @created - forgotten
     * @lastUpdated - 20151125
     */
    function getTableColumnList($tableName='', $onlyColumn = true){

        $db= $this->getCurrentDbName();
        $qry="SELECT COLUMN_NAME, TABLE_NAME, TABLE_SCHEMA, TABLE_CATALOG, IS_NULLABLE, DATA_TYPE, COLUMN_TYPE, PRIVILEGES, EXTRA, ORDINAL_POSITION
                FROM INFORMATION_SCHEMA.COLUMNS COL
                WHERE COL.TABLE_SCHEMA='$db' ORDER BY ORDINAL_POSITION ";
        if(strlen(trim($tableName))>0) $qry .= " AND TABLE_NAME='$tableName'";
        $qry .= " ORDER BY `COL`.`TABLE_NAME` ASC ";

        $data = $this->runSelectQuery($qry, false);

        if($onlyColumn && !empty($data)){
            $ret = '';
            foreach($data as $row){
                $ret[$row[ 'TABLE_NAME']][] = $row[ 'COLUMN_NAME'];
            }
            return $ret ;
        }
        return $data ;
    }

    /**
     * For running a raw query : todo - need to update
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $qry
     * @param bool $arrayIdx
     * @return array|bool|int
     */
    public function readByQuery($qry, $arrayIdx= true){
        return $this->runSelectQuery($qry, $arrayIdx);
    }


    /**
     * Building insert query from array and then run it. todo - need to update.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $fldArr
     * @return bool|mixed
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
     * Build update query then run it. todo - need to up-to-date the code.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
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
     * Creating where condition for query. todo - may be no more needed like this we have another rich one. check and adapt it.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
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
     * Insert query builder.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
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
     * Update query builder.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
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
    public static function dump($var, $die=true, $die_msg=NULL, $varDump=false){

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
     * Dump as many as you want .
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param bool $die
     * @return void
     */
    public static function dumps($die=true){
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
     * output string in pre tag.
     * @author Md. Atiqur Rahman <atiqur@shaficonsultancy.com, atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param $var
     * @param bool $die
     * @param null $die_msg
     * @return void
     */
    public static function dumpInPre($var, $die=false, $die_msg=NULL){

        echo '<pre>'.$var.'</pre>';
        if($die===true)	die('Died from sql helper dumper'.$die_msg);
    }


    /**
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param bool $eol
     * @return void
     */
    public static function printBreak($eol = false){

        if($eol) echo PHP_EOL ;
        else     echo '<br/>';
    }

    /**
     * Alias of printBreak method.
     * @author Md. Atiqur Rahman <atiqur@shaficonsultancy.com, atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param bool $eol
     * @return void
     */
    public static function println($eol = false){
        self::printBreak($eol);
    }

    //========================== in-progress work =============================
    public function insertFromArray($conf){

        $column = $conf['header'] ;
        $values = $conf['values'];
        $sql = 'INSERT INTO '.$this->tablePrefix.$this->tableName.'('.$this->arrayToCsv($column).') VALUES '.$this->makeQry($conf);

        die($sql);

    }

    protected function makeQry($conf){

        $column = $conf['header'] ;
        $values = $conf['values'];
        $ln = count($values);
        $separator = '';
        $sqlInsert ='';

        for($i=0; $i<$ln; $i++){
            $vGlue = '';
            $sap = '';
            foreach($column as $hd){
                $values[$i][$hd] = '';
                $vGlue .= "'".$values[$i][$hd]."'".$sap ;
                $sap = ', ';
            }

            $sqlInsert = $separator."(".$vGlue.")";
            $separator = ', ';
        }

        return $sqlInsert ;
    }

    /**
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param array $arr
     * @param string $separator
     * @param bool $sqlQuoted
     * @param string $quote
     * @return string
     */
    public static function arrayToCsv($arr=array(), $separator=', ', $sqlQuoted = true, $quote = '`'){

        if(is_array($arr) and count($arr)>0){
            $sap='';
            $return='';
            foreach($arr as $r){
                if($sqlQuoted == true)  $return.=$sap.$quote.$r.$quote;
                else  $return.=$sap.$r;
                $sap=$separator;
            }
            return $return;

        }else return '';
    }

    //========================== end of in-progress work =============================

    //=========== very customized functions only project basis : (zend model helper)================

    /**
     * Helper for writing model in zend project.
     * @author Md. Atiqur Rahman <atiqur@shaficonsultancy.com, atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.1
     * @param string $domainName
     * @param bool $dumpToo
     * @return string
     */
    public function getTableColumnAsVariable($domainName = '', $dumpToo = false){
        if(empty($domainName)) $domainName = self::getCurrentDomainName() ;
        if(empty($domainName)) die('Domain name not found.');

        $dbName = self::getCurrentDbName() ;
        $tables = self::getTableList() ;
        $tables = $tables[$dbName] ;

        if(!in_array($domainName,$tables)) die('Domain:'.$domainName.' do not exist in given database.');

        $return =' '.PHP_EOL;
        $cols = self::getTableColumnList($domainName);
        foreach($cols[$domainName] as $col) $return .= 'public $'.$col.' ;'.PHP_EOL ;

        if($dumpToo === true) self::dumpInPre($return);
        return $return ;
    }


    /**
     * Helper for exchange array function in model of zend framework.
     * @author Md. Atiqur Rahman <atiqur@shaficonsultancy.com, atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param string $domainName
     * @param bool $dumpToo
     * @return string
     */
    public function createExchangeArrayFunctionBody($domainName = '', $dumpToo = false){
        if(empty($domainName)) $domainName = self::getCurrentDomainName() ;
        if(empty($domainName)) die('Domain name not found.');

        $dbName = self::getCurrentDbName() ;
        $tables = self::getTableList() ;
        $tables = $tables[$dbName] ;

        if(!in_array($domainName,$tables)) die('Domain:'.$domainName.' do not exist in given database.');

        $return =' '.PHP_EOL;
        $cols = self::getTableColumnList($domainName);
        foreach($cols[$domainName] as $col){

            if($col == 'created' || $col == 'updated')
                $return .= '$this->'.$col.'  = (isset($data[\''.$col.'\']))  ? $data[\''.$col.'\']  : date(\'Y-m-d H:i:s\');'.PHP_EOL ;
            elseif($col == 'created_by' || $col == 'updated_by' || $col == 'order' || $col == 'active' || $col == 'rank')
                $return .= '$this->'.$col.'  = (isset($data[\''.$col.'\']))  ? $data[\''.$col.'\']  : 1;'.PHP_EOL ;
            else $return .= '$this->'.$col.'  = (isset($data[\''.$col.'\']))  ? $data[\''.$col.'\']  : null;'.PHP_EOL ;

        }

        if($dumpToo === true) self::dumpInPre($return);
        return $return ;
    }


    //========== end of project specific functions =================================================

    //Ideas to implementat_role
    /**
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.0
     * @param $columnName
     * @param string $type
     * @param string $length
     * @param bool $notNull
     * @return bool
     */
    public function addColumnInCurrentDomain($columnName, $type= 'varchar', $length='255', $notNull = true){
        // todo - write this
        return false;
    }

    public function alterColumnName(){
        return false;
    }

    public function removeColumn(){
        return false;
    }
    //end of ideas
}

