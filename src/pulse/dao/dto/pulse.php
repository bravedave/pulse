<?php
  // file: src/app/pulse/dao/dto/pulse.php
  // MIT License

namespace bravedave\pulse\dao\dto;

use bravedave\dvc\dto;

class pulse extends dto {
  public $id = 0;
  public $created = '';
  public $updated = '';
  public $title = '';
  public $content = '';
  public $created_by = 0;
}