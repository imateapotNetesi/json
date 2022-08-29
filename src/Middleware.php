<?php
namespace Imateapot\JSON;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Exception;

class Middleware implements MiddlewareInterface {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

      try {
        $response = $handler->handle($request);
      } catch (Exception $e) {
        new Payload(null,$e->getMessage(),$e->getCode());

        $code = $e->getCode();

        if (  !($e->getCode() > 400 && $e->getCode() < 599) ) $code = 400;

        $psr17Factory = new Psr17Factory();

        $responseBody = $psr17Factory->createStream( json_encode((object) $payload, JSON_PRETTY_PRINT) );
        $response = $psr17Factory->createResponse($code)->withBody($responseBody);
      }
      return $response->withHeader('Content-type', 'application/json');
    }
}
