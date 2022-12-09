<?php

namespace MyBackup;

use Ifsnop\Mysqldump as IMysqldump;

class Backup {

    private $dumper;
    private $storagePath;
    private $tableConditions;
    private $tableLimits;

    private static $callableFormatLog;

    private function __clone() {}

    public static function log(callable $callback) {
        self::$callableFormatLog = $callback;
    }

    public static function create(Connection $connection, $storagePath) {
        return (new self($connection))->backup($storagePath);
    }

    public static function createWithConditions(Connection $connection, $storagePath, array $tableConditions) {
        // reutilizamos el constructor y enviamos las condiciones para que no obtengan el valor por defecto
        return (new self($connection, $tableConditions))->backup($storagePath);
    }

    public static function createWithRateLimits(Connection $connection, $storagePath, array $tableLimits) {
        // reutilizamos el constructor y simulamos el paso de las condiciones para que obtengan el valor por defecto
        return (new self($connection, [], $tableLimits))->backup($storagePath);
    }

    public static function createComplete(Connection $connection, $storagePath, array $tableConditions, array $tableLimits) {
        // reutilizamos el constructor y le pasamos las condiciones y el límite
        return (new self($connection, $tableConditions, $tableLimits))->backup($storagePath);
    }

    private function __construct($connection, $tableConditions = [], $tableLimits = []) {
        $this->tryToCreateDumperByConnectionValues($connection, $tableConditions, $tableLimits);
    }

    private function tryToCreateDumperByConnectionValues($connection, $tableConditions, $tableLimits) {

        try {

            $dump = new IMysqldump\Mysqldump(
                $connection->dsn(), $connection->username(), $connection->password()
            );

            // comprobamos si se definen condiciones para las queries
            $this->defineTableConditionsIfExists($tableConditions);
            
            // comprobamos si se definen límites para las queries
            $this->defineTableLimitsIfExists($tableLimits);
            
        } catch (\Exception $e) {
            // elevar excepción a otro nivel para capturarla
            throw new \Exception('mysqldump-php error: ' . $e->getMessage());
        }

        $this->dumper = $dump;

    }

    private function backup($storagePath) {

        // comprobamos si existe el path
        $this->defineStorageDirectory($storagePath);
        
        // intentamos crear el backup
        $this->tryToCreateBackup();

    }

    private function defineStorageDirectory($storagePath) {

        if (!is_dir(dirname($storagePath))) {
            throw new \Exception("No se ha encontrado el directorio para crear el backup: " . dirname($storagePath));
        }

        $this->storagePath = $storagePath;

    }

    private function defineTableConditionsIfExists() {
        if(is_array($this->tableConditions)) {
            $this->dumper->setTableWheres($this->tableConditions);
        }
    }

    private function defineTableLimitsIfExists() {
        if(is_array($this->tableLimits)) {
            $this->dumper->setTableLimits($this->tableLimits);
        }
    }

    private function tryToCreateBackup() {

        try {

            // verbose mode
            if (isset(self::$callableFormatLog)) {
                
                $callable = self::$callableFormatLog;
                $this->dumper->setInfoHook(function($object, $info) use($callable) {
                    $info  = (object) $info;
                    call_user_func_array($callable, [
                        new Table(
                            $info->name, $info->rowCount
                        )
                    ]);
                });
            }

            // generamos el backup
            $this->dumper->start($this->storagePath);
            
        } catch (\Exception $e) {
            // elevar excepción a otro nivel para capturarla
            throw new \Exception('mysqldump-php error: ' . $e->getMessage());
        }

    }

}