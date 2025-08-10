<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * This file is compatible with dream/cms controller.php
*/

use bravedave\dvc\ckeditor;

class js extends Controller {

  public function ckeditor($file = '', $translation = '') {

    if (ckeditor::serve($file, $translation))  return;
    $this->page404();
  }
}
