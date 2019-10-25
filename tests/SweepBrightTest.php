<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Tests;

use PHPUnit\Framework\TestCase;
use SweepBright\SweepBright;
use SweepBright\Request;
use SweepBright\Exception\ClientValidationException;

class SweepBrightTest extends TestCase
{
    protected $api;
    protected $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new TestApiAdapter();
        $this->api = new SweepBright();
        $this->api->setApiAdapter($this->adapter);
    }

    public function testGetEstate()
    {
        $this->adapter->queueResponseFromFile('GetEstate.json');
        $estate = $this->api->getEstate('test');
        
        $this->assertIsString($estate->subType);
        $this->assertIsString($estate->sub_type);
        $this->assertIsInt($estate->bathrooms);
        $this->assertFalse(isset($estate->invalidProperty));
    }

    public function testSetEstateUrl()
    {
        $response = $this->api->setEstateUrl('test', 'https://www.sweepbright.com');
        $this->assertNull($response);
    }

    public function testSaveContactValidation()
    {
        $this->expectException(ClientValidationException::class);
        $response = $this->api->saveContact([
            'test' => 'test'
        ]);
    }

    public function testSaveContact()
    {
        $response = $this->api->saveContact([
            'firstName' => 'test',
            'preferences' => [
                'max_price' => '',
                'min_price' => 100000,
                'min_rooms' => null,
            ],
        ], [
            'negotiation' => 'string',
            'types' => ['string'],
            'max_price' => '100000',
            'min_price' => 100000,
            'min_rooms' => 1,
        ], [
            'country' => 'string',
            'postal_codes' => ['string'],
        ]);
        $this->assertNull($response);
    }

    public function testSaveEstateContact()
    {
        $response = $this->api->saveEstateContact('test', [
            'firstName' => 'test',
        ]);
        $this->assertNull($response);
    }
    
    // Requests
    
    public function testRequestArrayValidation()
    {
        $this->expectException(ClientValidationException::class);
        $this->api->saveContact([
            'preferences' => [
                'types' => 'invalid',
            ],
        ]);
    }
    
    public function testRequestObjectValidation()
    {
        $this->expectException(ClientValidationException::class);
        $this->api->saveContact([
            'preferences' => new \Exception()
        ]);
    }
    
    public function testRequestInvalidProperty()
    {
        $this->expectException(ClientValidationException::class);
        $this->api->saveContact([
            'invalidProperty' => 'test',
        ]);
    }

    public function testRequestUnset()
    {
        $request = new Request\SaveContactRequest();
        
        $this->assertFalse(isset($request->firstName));
        $request->firstName = 'test';
        $this->assertTrue(isset($request->firstName));
        $this->assertIsString($request->first_name);
        unset($request->firstName);
        $this->assertFalse(isset($request->first_name));
    }

    public function testRequestDebugInfo()
    {
        $request = new Request\SaveContactRequest();
        $request->firstName = 'test';
        $debug_info = $request->__debugInfo();
        
        $this->assertIsString($debug_info['first_name']);
    }
    
    // Responses

    public function testResponseUnset()
    {
        $this->adapter->queueResponseFromFile('GetEstate.json');
        $estate = $this->api->getEstate('test');
        
        $this->assertIsString($estate->subType);
        unset($estate->subType);
        $this->assertFalse(isset($estate->subType));
    }

    public function testResponseInvalidProperty()
    {
        $this->adapter->queueResponseFromFile('GetEstate.json');
        $estate = $this->api->getEstate('test');
     
        $this->expectException(ClientValidationException::class);
        $estate->invalidProperty;
    }

    public function testResponseJsonSerialize()
    {
        $this->adapter->queueResponseFromFile('GetEstate.json');
        $estate = $this->api->getEstate('test');
     
        $json = json_encode($estate);
        $this->assertIsString(json_decode($json, true)['sub_type']);
    }

    public function testResponseDebugInfo()
    {
        $this->adapter->queueResponseFromFile('GetEstate.json');
        $estate = $this->api->getEstate('test');
        $debug_info = $estate->__debugInfo();
        
        $this->assertIsArray($debug_info);
        $this->assertIsString($debug_info['sub_type']);
    }
}
