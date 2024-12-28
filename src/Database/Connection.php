<?php

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

class Connection
{
    public static function initialize(): void
    {
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'],
            'password'  => $_ENV['DB_PASS'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        // Set the capsule instance as globally available
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
