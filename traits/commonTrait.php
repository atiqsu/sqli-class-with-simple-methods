<?php
/**
 * Created by Md. Atiqur Rahman
 * Email: atiq.cse.cu0506.su@gmail.com
 * Skype: atiq.cu
 * Date: 31/03/2016
 * Time: 5:15 PM
 */

namespace commonFunction ;


trait commonFunction {


    /**
     * Basic and simple sanitize.
     * @author Md. Atiqur Rahman <atiq.cse.cu0506.su@gmail.com>
     * @since 1.0.5
     * @param $data
     * @return string
     */
    public function sanitizeSimple($data){
        return htmlentities($data, ENT_QUOTES, 'UTF-8');
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

}