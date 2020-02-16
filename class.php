<?php

/**
  класс модуля
 * */

namespace Nyos\mod;

if (!defined('IN_NYOS_PROJECT'))
    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class FolderAdmin {

    /**
     * переменная для конекта к БД
     * @var type 
     */
    public static $db = false;


    public static function dbConect() {

        if (!extension_loaded('PDO')) {
            throw new \Exception(' pdo bd не доступен ');
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/sites/db.sqllite.sl3')) {
            $SqlLiteFile = $_SERVER['DOCUMENT_ROOT'] . '/sites/db.sqllite.sl3';
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/site/db.sqllite.sl3')) {
            $SqlLiteFile = $_SERVER['DOCUMENT_ROOT'] . '/site/db.sqllite.sl3';
        } else {
            throw new \Exception(' не определена папка важная ');
        }

//echo $SqlLiteFile;

        self::$db = new \PDO('sqlite:' . $SqlLiteFile, null, null, array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ));
//$db->exec('PRAGMA journal_mode=WAL;');

    }

    public static function domainUnlink(string $domain) {

        if( self::$db === false )
        self::dbConect();

        $ff = self::$db->prepare('UPDATE `2domain` SET `folder` = NULL WHERE `domain` = :domain ;');
        $ff->execute([':domain' => $domain]);

        return true;
    }

    public static function domainLink(string $domain, string $folder) {

        if( self::$db === false )
        self::dbConect();

        $ff = self::$db->prepare('UPDATE `2domain` SET `folder` = :folder WHERE `domain` = :domain ;');
        $ff->execute([':domain' => $domain, ':folder' => $folder]);

        return true;
    }

    // public static $dir_img_server = false;

    public static function getList() {


        try {

        if( self::$db === false )
        self::dbConect();

            $ff = self::$db->prepare('SELECT * FROM `2domain` ');
            $ff->execute();

            //$f = $ff->fetchAll();
            $f = [];
            while ($f2 = $ff->fetch()) {

                if (strpos($f2['domain'], 'xn--') !== false) {

                    $Punycode = new \TrueBV\Punycode();
                    //var_dump($Punycode->encode('renangonçalves.com'));
                    //// outputs: xn--renangonalves-pgb.com
                    $f[$f2['folder']][$f2['domain']] = $Punycode->decode($f2['domain']);
                } else {
                    $f[$f2['folder']][$f2['domain']] = 1;
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
                self::$db->exec('CREATE TABLE `2domain` ( ' .
                        ' `domain` varchar(150) NOT NULL, ' .
                        ' `folder` varchar(150) DEFAULT NULL ' .
                        ' );');

                $ff = self::$db->prepare('INSERT INTO  `2domain` (domain) VALUES (?)');
                $ff->execute([$domain]);
                // unset($ff);

            } else {
                throw new \NyosEx('непонятная ошибка DB (выбираем папку по домену): ' . $ex->getMessage());
            }
        }
    }

}
