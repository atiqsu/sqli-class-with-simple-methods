<?php
/**
 * Created by Md. Atiqur Rahman
 * Email: atiq.cse.cu0506.su@gmail.com
 * Skype: atiq.cu
 * Date: 31/03/2016
 * Time: 5:15 PM
 *
 *
 */


trait ongoingTrait {

    #todo - IDEA - rewrite the whole thing using interface and trait and prepared statement; even with pdo ###

    //========================== in-progress work =============================
    public function insertFromArray($conf){

        $column = $conf['header'] ;

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

    #========================== end of in-progress work =============================

    #New Ideas to implement
    public function Connect(){}
    public function Disconnect(){}
    public function GetTable(){}
    public function GetData(){}
    public function GetRow(){}

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

    #lets organize all query in an more organized way... todo
    //auto determine the tpe of query........
    function verify($request)
    {
        $splited_query=explode(" ", $request);
        $type_query=strtoupper($splited_query[0]);
        switch ($type_query) {
            case "SELECT":
                $this->select_query($request);
                //echo $requet."**";
                break;
            case 'INSERT':
                $this->insert_query($request);
                break;
            case 'DELETE':
                $this->delete_query($request);
                break;
            case "UPDATE":
                $this->update_query($request);
                break;

            default:
                echo "erreur dans la requet";
                break;
        }
    }
    #end of ideas

}