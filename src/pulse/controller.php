<?php
/*
 * file: src/app/pulse/controller.php
 * MIT License
 */

namespace bravedave\pulse;

use bravedave\dvc\{controller as dvcController, Response, ServerRequest};

class controller extends dvcController {

  protected function _index() {

    $this->data = (object)[
      'from' => date('Y-m-d', strtotime('-3 month')),
      'to' => date('Y-m-d'),
      'title' => $this->title = config::label,
    ];

    $this->renderBS5([
      'aside' => fn() => $this->load('index'),
      'main' => fn() => $this->load('matrix')
    ]);
  }

  protected function before() {

    config::pulse_checkdatabase();
    parent::before();
    $this->viewPath[] = __DIR__ . '/views/';
  }

  protected function postHandler() {

    $request = new ServerRequest;
    $action = $request('action');

    /*
      _brayworth_.fetch.post(_brayworth_.url('pulse'),{
        action: 'delete',
        id : 1
      }).then(console.log);

      _brayworth_.fetch.post(_brayworth_.url('pulse'),{
        action: 'get-by-id',
        id : 1
      }).then(console.log);

      _brayworth_.fetch.post(_brayworth_.url('pulse'),{
        action: 'get-matrix'
      }).then(console.log);
    */
    return match ($action) {
      'pulse-delete' => handler::pulseDelete($request),
      'get-by-id' => handler::pulseGetByID($request),
      'get-matrix' => handler::pulseGetMatrix($request),
      'pulse-save' => handler::pulseSave($request),
      'pulse-seen' => handler::pulseSeen($request),
      default => parent::postHandler()
    };
  }

  public function ckeditor() {

    Response::serve(__DIR__ . '/scripts/ckeditor.js');
  }

  public function edit($id = 0) {
    // tip : the structure is available in the view at $this->data->dto
    $this->data = (object)[
      'title' => $this->title = config::label,
      'dto' => new dao\dto\pulse
    ];

    if ($id = (int)$id) {
      $dao = new dao\pulse;
      $this->data->dto = $dao->getByID($id);
      $this->data->title .= ' edit';
    }

    $this->load('edit');
  }
}
