<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbHandler
 *
 * @author damith
 */
class DbHandler {
    
    static $dsn = 'mysql:host=localhost;port=3306;dbname=accrawler';
    static $username = 'root';
    static $passwd = '123';

    /**
     *
     * @var PDO 
     */
    static $pdo = null;

    /**
     * gets the current database connection
     * @return PDO
     */
    public static function getConnection() {
        if (is_null(self::$pdo)) {
            self::$pdo = new PDO(self::$dsn, self::$username, self::$passwd);
            // setting the error mode
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // setting the default charset to utf8
            self::$pdo->exec('set names utf8');
        }
        return self::$pdo;
    }

}
