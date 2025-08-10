<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace ckdemo;

use bravedave\dvc\strings;
use Controller as baseController;
use cms;

class controller extends baseController {

  protected function _index() {

    $this->data = (object)[
     'pageUrl' => strings::url($this->route),
     'searchFocus' => true,
     'title' => $this->title = 'CK Demo',
    ];

    $this->renderBS5([
     'main' => fn () => $this->load('ck-demo')
    ]);
  }

  protected function before() {
    $this->viewPath[] = __DIR__ . '/views/';
    parent::before();
  }
}
