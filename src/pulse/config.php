<?php
  // file: src/app/pulse/config.php
  // MIT License

namespace bravedave\pulse;

use config as rootConfig;

class config extends rootConfig {

  const pulse_db_version = 2;

  const label = 'Pulse';

  static function pulse_checkdatabase() {

    $dao = new dao\dbinfo;
    $dao->checkVersion('pulse', self::pulse_db_version);
  }
}