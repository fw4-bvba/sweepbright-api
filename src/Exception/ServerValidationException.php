<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Exception;

use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerValidationException extends ClientException
{
    protected $fields;
    
    public function __construct(
        string $message,
        RequestInterface $request,
        ResponseInterface $response = null,
        array $fields = [],
        \Exception $previous = null,
        array $handlerContext = []
    ) {
        $this->fields = $fields;
        parent::__construct($message, $request, $response, $previous, $handlerContext);
    }
    
    public function getFields(): array
    {
        return $this->fields;
    }
}
