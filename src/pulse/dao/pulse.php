<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

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

    $sql = sprintf(
      'SELECT
        pulse.*,
        (SELECT `seen` FROM `pulse_state` WHERE `pulse_id` = pulse.id) AS seen
      FROM `pulse`
      %s
      ORDER BY `created` DESC',
      $where
    );
    // logger::info(sprintf('<%s> %s', $sql, logger::caller()));

    return (new dtoSet)($sql); // an array of records
  }

  public function getRichData(dto\pulse $dto): dto\pulse {

    $sql = sprintf('SELECT * FROM `pulse_state` WHERE `pulse_id` = %d', $this->quote($dto->id));
    $dto->state = (new dto\pulse_state)($sql);

    if (!$dto->state) {

      $dto->state = new dto\pulse_state;
    }
    return $dto;
  }

  public function seen(int $id, int $seen = 0) {

    if ($id = (int)$id) {

      if ($id > 0) {

        $sql = sprintf('SELECT * FROM `pulse_state` WHERE `pulse_id` = %d', $id);
        if ($state = (new dto\pulse_state)($sql)) {

          (new pulse_state)->UpdateByID([
            'seen' => $seen ? 1 : 0
          ], $state->id);

          logger::info(sprintf('<updated %s> %s', $seen, logger::caller()));
        } else {

          (new pulse_state)->Insert([
            'pulse_id' => $id,
            'seen' => $seen ? 1 : 0
          ]);
          logger::info(sprintf('<inserted %s:%s> %s', $id, $seen, logger::caller()));
        }
      }
    }
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
