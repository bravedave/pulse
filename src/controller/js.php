<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 * 
 * MIT License
 *
*/
use bravedave\dvc\ckeditor;

class js extends controller {

  public function ckeditor($file = '', $translation = '') {

    if (ckeditor::serve($file, $translation))  return;
    $this->page404();
  }
}
