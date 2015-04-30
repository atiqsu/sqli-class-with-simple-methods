<?php
/**
 * Created by Md. Atiqur Rahman
 * Email: atiq.cse.cu0506.su@gmail.com
 * Skype: atiq.cu
 * Date: 4/23/2015
 * Time: 1:53 AM
 * Last Modified : 23rd April 2015
 */

/*
ini_set('display_errors',1);
ini_set('E_ERROR',1);
error_reporting(-1);
*/

require_once 'cls.sqli.php';

    $dbName = 'local_db';
    $user = 'atiqur';
    $pass = '@secret';
//    $user = 'root';
//    $pass = '';
    $db = new \commonSql\SQLi($user, $pass, $dbName);

    $test = array();
    $db->dump($db->arrayToQueryString($test));

//    $db->setDomain('user');
//    $userId = 5;
//    $db->delete($userId);
//    $db->dump($db->read(), 'end of example');

//$db->dump($db, false);