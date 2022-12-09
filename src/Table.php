<?php

namespace MyBackup;

class Table {

    private $name;
    private $rows;

    public function __construct($name, $rows) {
        $this->name = $name;
        $this->rows = $rows;
    }

    public function name() {
        return $this->name;
    }

    public function rows() {
        return $this->rows;
    }

}