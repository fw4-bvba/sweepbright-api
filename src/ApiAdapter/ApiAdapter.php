<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\ApiAdapter;

use SweepBright\Request\Request;
use SweepBright\Response\Response;

abstract class ApiAdapter implements ApiAdapterInterface
{
    public function request(string $method, string $endpoint, ?Request $request = null)
    {
        $http_body = $this->requestBody($method, $endpoint, $request ? json_encode($request) : null);
        $json_body = json_decode($http_body, false);
        return $json_body;
    }
}
