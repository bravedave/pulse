<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace bravedave\pulse\dao\dto;

use bravedave\dvc\dto;

class pulse extends dto {
  public $id = 0;
  public $created = '';
  public $updated = '';
  public $title = '';
  public $content = '';
  public $created_by = 0;

  // richData
  public pulse_state|null $state = null;
}
