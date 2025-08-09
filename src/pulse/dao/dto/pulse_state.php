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

class pulse_state {
  public $id = 0;
  public string $created = '';
  public string $updated = '';
  public int $pulse_id = 0;
  public int $users_id = 0;
  public int $seen = 0;
}
