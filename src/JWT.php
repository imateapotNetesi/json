<?php
namespace Imateapot\JSON;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWT implements MiddlewareInterface {

    protected $attribute = 'jwt';
    protected $header = 'Authorization';
    protected $regex = '/Bearer\s+(.*)$/i';
    protected $cookie = 'token';
    protected $key = 'secretkey';


    public function setAttribute (string $value): void {
      $this->attribute = $value;
    }

    public function setHeader (string $value): void {
      $this->header = $value;
    }

    public function setRegex (string $value): void {
      $this->regex = $value;
    }

    public function setCookie (string $value): void {
      $this->cookie = $value;
    }

    public function setKey (Key $value): void {
      $this->cookie = $value;
    }


    private function hydrate(array $data = []): void {
      foreach ($data as $key => $value) {
          $key = str_replace(".", " ", $key);
          $method = lcfirst(ucwords($key));
          $method = 'set'.ucfirst(str_replace(" ", "", $method));
          if (method_exists($this, $method)) {
              call_user_func([$this, $method], $value);
          }
      }
    }

    public function __construct(array $options = []) {
      $this->hydrate($options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

      try {
        $token = $this->fetchToken($request);
        $decoded = $this->decodeToken($token);
        //$this->checkFingerprint($decoded['fingerprint']);
      }
      catch (Exception $e) {
        $payload = new Payload(null,'Unauthorized',401);
        $psr17Factory = new Psr17Factory();
        $responseBody = $psr17Factory->createStream( (string) $payload );
        $response = $psr17Factory->createResponse(401)->withBody($responseBody);
        return $response->withHeader('Content-type', 'application/json');
      }

      $request = $request->withAttribute($this->attributeName, $decoded);

      $response = $handler->handle($request);

      return $response;
    }

    private function fetchToken(ServerRequestInterface $request): string {
        $header = $request->getHeaderLine($this->headerLine);

        if (false === empty($header)) {
            if (preg_match($this->regex, $header, $matches)) {
                return $matches[1];
            }
        }

        $cookieParams = $request->getCookieParams();

        if (isset($cookieParams[$this->cookieName])) {
            if (preg_match($this->regex, $cookieParams[$this->cookieName], $matches)) {
                return $matches[1];
            }
            return $matches[1];
        };

        throw new Exception("Token not found.");
    }

    private function decodeToken(string $token): array {
        try {
            $decoded = JWT::decode(
                $token,
                $this->options["key"]
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    private function checkFingerprint(string $fingerprint): array {
        try {

        } catch (Exception $exception) {
            throw $exception;
        }
    }

}
