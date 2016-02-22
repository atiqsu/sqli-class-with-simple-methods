<?php
/**
 * File Author: Md. Atiqur Rahman <atiqur@shaficonsultancy.com, atiq.cse.cu0506.su@gmail.com> .
 * Created: 2016-02-22 11:22 AM
 * Last update: 2016-02-22
 * Version: 1.0.0
 */


//auto determine the tpe of query........
private function verife($requet)
{
    $splited_query=explode(" ", $requet);
    $type_query=strtoupper($splited_query[0]);
    switch ($type_query) {
        case "SELECT":
            $this->select_query($requet);
            //echo $requet."**";
            break;
        case 'INSERT':
            $this->insert_query($requet);
            break;
        case 'DELETE':
            $this->delete_query($requet);
            break;
        case "UPDATE":
            $this->update_query($requet);
            break;

        default:
            echo "erreur dans la requet";
            break;
    }
}