<?php
  // file: src/app/pulse/dao/dbinfo.php
  // MIT License

namespace bravedave\pulse\dao;

use bravedave\dvc\dbinfo as dvcDbInfo;

class dbinfo extends dvcDbInfo {
  protected function check() {
    parent::check();
    parent::checkDIR(__DIR__);
  }
}