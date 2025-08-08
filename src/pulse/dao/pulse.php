<?php
  // file: src/app/pulse/dao/pulse.php
  // MIT License

namespace bravedave\pulse\dao;

use bravedave\dvc\{dao, dtoSet};

class pulse extends dao {
  protected $_db_name = 'pulse';
  protected $template = dto\pulse::class;

  public function getMatrix() : array {

    return (new dtoSet)('SELECT * FROM `pulse`'); // an array of records
  }

  public function Insert($a) {
    $a['created'] = $a['updated'] = self::dbTimeStamp();
    return parent::Insert($a);
  }

  public function UpdateByID($a, $id) {
    $a['updated'] = self::dbTimeStamp();
    return parent::UpdateByID($a, $id);
  }
}