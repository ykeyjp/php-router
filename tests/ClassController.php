<?php
namespace ykey\router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClassController
{
    public function action(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        return $response->withStatus(103, 'action');
    }
}
