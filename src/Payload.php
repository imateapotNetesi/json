<?php
namespace Imateapot\JSON;

class Payload {
  protected $json;
  public function __construct($data = null,string $message = '',int $code = 200) {
    $this->json = json_encode((object) [
      'data' => $data,
      'message' => $message,
      'code' => $code
    ] /*,JSON_PRETTY_PRINT*/ );
  }
  public function __toString() {
    return $this->json;
  }
}
