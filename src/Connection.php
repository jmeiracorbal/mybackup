<?php

namespace MyBackup;

class Connection {

    private $dsn;
    private $username;
    private $password;

    public function __construct($dsn, $username, $password) {
        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password; 
    }

    public function dsn() {
        return $this->dsn;
    }
    
    public function username() {
        return $this->username;
    }

    public function password() {
        return $this->password;
    }

}