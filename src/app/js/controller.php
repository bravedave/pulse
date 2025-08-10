<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace js;

use Controller as baseController;
use bravedave\dvc\{ckeditor, Response};

class controller extends baseController {

  public function ckeditor($file = '', $translation = '') {

    if (empty($file)) {

      Response::serve(__DIR__ . '/scripts/ckeditor.js');
    } else {

      if (ckeditor::serve($file, $translation))  return;
      $this->page404();
    }
  }
}
