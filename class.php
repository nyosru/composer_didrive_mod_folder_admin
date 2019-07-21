<?php

/**
  класс модуля
 * */

namespace Nyos\mod;

if (!defined('IN_NYOS_PROJECT'))
    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class FolderAdmin {

    // public static $dir_img_server = false;

    public static function getList() {


        if (!extension_loaded('PDO')) {
            throw new \Exception(' pdo bd не доступен ');
        }

        if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/sites')) {
            $SqlLiteFile = $_SERVER['DOCUMENT_ROOT'] . '/sites/db.sqllite.sl3';
        } elseif (is_dir($_SERVER['DOCUMENT_ROOT'] . '/site')) {
            $SqlLiteFile = $_SERVER['DOCUMENT_ROOT'] . '/site/db.sqllite.sl3';
        } else {
            throw new \Exception(' не определена папка важная ');
        }

//echo $SqlLiteFile;

        $db = new \PDO('sqlite:' . $SqlLiteFile, null, null, array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ));
//$db->exec('PRAGMA journal_mode=WAL;');

        try {

            $ff = $db->prepare('SELECT * FROM `2domain` ');
            $ff->execute();

            //$f = $ff->fetchAll();
            $f = [];
            while( $f2 = $ff->fetch() ){
                
                if (strpos($f2['domain'], 'xn--') !== false) {

                    $Punycode = new \TrueBV\Punycode();
                    //var_dump($Punycode->encode('renangonçalves.com'));
                    //// outputs: xn--renangonalves-pgb.com
                    $f[$f2['folder']][ $f2['domain'] ] = $Punycode->decode($f2['domain']);
                } else {
                    $f[$f2['folder']][ $f2['domain'] ] = 1;
                }
                
            }
            return $f;
            
        }
//
        catch (\PDOException $ex) {

            echo '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
            . PHP_EOL . $ex->getTraceAsString()
            . '</pre>';

// не найдена таблица, создаём значит её
            if (strpos($ex->getMessage(), 'no such table')) {

// echo '<Br/>ошибка DB:' . $e->getMessage();
                $db->exec('CREATE TABLE `2domain` ( ' .
                        ' `domain` varchar(150) NOT NULL, ' .
                        ' `folder` varchar(150) DEFAULT NULL ' .
                        ' );');

                $ff = $db->prepare('INSERT INTO  `2domain` (domain) VALUES (?)');
                $ff->execute([$domain]);
                unset($ff);
            } else {

                throw new \NyosEx('непонятная ошибка DB (выбираем папку по домену): ' . $ex->getMessage());
            }
        }
    }

}
