<?php
// file: src/app/pulse/dao/pulse.php
// MIT License

namespace bravedave\pulse\dao;

use bravedave\dvc\{dao, dtoSet, logger};

class pulse extends dao {
  protected $_db_name = 'pulse';
  protected $template = dto\pulse::class;

  public function getMatrix(string $from = '', string $to = ''): array {
    $where = [];
    if ($from && $to) {
      $fromDate = date('Y-m-d 00:00:00', strtotime($from));
      $toDate = date('Y-m-d 23:59:59', strtotime($to));
      $where[] = sprintf(
      '`created` BETWEEN %s AND %s',
      $this->quote($fromDate),
      $this->quote($toDate)
      );
    } else {
      if ($from) {
      $fromDate = date('Y-m-d 00:00:00', strtotime($from));
      $where[] = '`created` >= ' . $this->quote($fromDate);
      }
      if ($to) {
      $toDate = date('Y-m-d 23:59:59', strtotime($to));
      $where[] = '`created` <= ' . $this->quote($toDate);
      }
    }

    $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT * FROM `pulse` $where ORDER BY `created` DESC";
    // logger::info(sprintf('<%s> %s', $sql, logger::caller()));

    return (new dtoSet)($sql); // an array of records
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
