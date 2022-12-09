# MyBackup

Project to make database backups based on MySQL. Inspired and based on [mysqldump-php](https://github.com/ifsnop/mysqldump-php).

## Try to create backup with a guided example

Call method `create`. This method required a instance of `Connection`. This class is a DTO with database required info (dsn, username and password):

```php
    new Connection(
        'mysql:host=mysserver.com;dbname=myuser', 'myuser', 'password'
    ),
```

Complete example: 

```php
require_once './vendor/autoload.php';

use MyBackup\Backup;
use MyBackup\Connection;

Backup::create(
    new Connection(
        'mysql:host=myserver.com;dbname=mydb', 'myuser', 'password'
    ),
    dirname(__FILE__) . '/storage/dump.sql'
);
```

## Anothers options to create a MySQL backup

### Create with conditions

Mapping with tableName and SQL condition.

```php
$tableConditions = array(
    'users' => 'date_registered > NOW() - INTERVAL 3 MONTH AND deleted=0',
    'logs' => 'date_logged > NOW() - INTERVAL 1 DAY',
    'posts' => 'isLive=1'
);

createWithConditions($connection, $storagePath, $tableConditions);
```

### Create with limits

Mapping with tableName and row date limit.

```php
$tableLimits = array(
    'users' => 300,
    'logs' => 50,
    'posts' => 10
);

createWithRateLimits(Connection $connection, $storagePath, $tableLimits);
```

### Create complete backup

Create with table conditions and row date limits

```php
createComplete(Connection $connection, $storagePath, $tableConditions, $tableLimits);
```

## Trace log backup info

* Define afeter backup create:

```php
Backup::log(
    function($table) {
        print_r(
            "Tabla " . $table->name() . " con un total de " . $table->rows() . " filas. \n"
        );
    }
);
```