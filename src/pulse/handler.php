<?php
// file: src/app/pulse/handler.php
// MIT License

namespace bravedave\pulse;

use bravedave\dvc\{ServerRequest, json};
use currentUser;

final class handler {

  public static function pulseDelete(ServerRequest $request): json {

    $action = $request('action');
    if ($id = (int)$request('id')) {

      (new dao\pulse)->delete($id);
      return json::ack($action);
    }

    return json::ack($action);
  }

  public static function pulseGetByID(ServerRequest $request): json {

    $action = $request('action');
    if ($id = (int)$request('id')) {

      if ($dto = (new dao\pulse)->getByID($id)) {

        return json::ack($action, $dto);
      }
    }
    return json::nak($action);
  }

  public static function pulseGetMatrix(ServerRequest $request): json {

    $action = $request('action');
    $from = $request('from', '');
    $to = $request('to', '');
    return json::ack($action, (new dao\pulse)->getMatrix($from, $to));
  }

  public static function pulseSave(ServerRequest $request): json {

    $action = $request('action');
    $a = [
      'title' => $request('title'),
      'content' => $request('content'),
      'created_by' => currentUser::id(),
    ];

    $dao = new dao\pulse;
    if ($id = (int)$request('id')) {

      $dao->UpdateByID($a, $id);
    } else {
      $dao->Insert($a);
    }

    return json::ack($action);
  }

  public static function pulseSeen(ServerRequest $request): json {

    $action = $request('action');
    if ($id = (int)$request('id')) {

      (new dao\pulse)->seen($id, (int)$request('seen'));
      return json::ack($action);
    }

    return json::nak($action);
  }
}
