<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\ZaiusException;

class ObjectsTest extends TestAbstract {
    public function testGetObjects() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $objects = $zaiusClient->getObjects();

        $this->assertInternalType('array',$objects);
        $this->assertGreaterThan(1,count($objects));
    }

    public function testGetInexistentObject() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $object = $zaiusClient->getObject('foo');
        $this->assertNull($object);
    }

    public function testGetObject() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $object = $zaiusClient->getObject('products');
        $this->assertInternalType('array',$object);
    }

    public function testCreateObjectSchema() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $fields =  [
            [
                'name' => 'object_id',
                "display_name"=> "New Object Identifier",
                "type"=> "string",
                "primary"=> true
            ],
            [
                "name"=> "another_field",
                "display_name"=> "Another Fields",
                "type"=> "string"
            ],
            [
                "name"=> "child_id",
                "display_name"=> "Child Identifier",
                "type"=> "number"
            ]
        ];

        $relations = [
        ];

        try {
            $zaiusClient->createObjectSchema('test_objects', 'Test Object', $fields, $relations, 'test_object');
        }
        catch (ZaiusException $exception) {
            if(!strpos($exception->getMessage(),'already used by another object')) {
                throw $exception;
            }
        }
    }

    public function testCreateOrUpdateObject() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $zaiusClient->postObject('products',['product_id'=>33,'name'=>'test product']);

        $zaiusClient->postObject('products',['product_id'=>33,'name'=>'test modified']);
    }

    public function testListObjectFields() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $fields = $zaiusClient->getObjectFields('products');

        $this->assertInternalType('array',$fields);
        $this->assertGreaterThan(1,count($fields));
    }

    public function testObjectField() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $field = $zaiusClient->getObjectField('products','product_id');

        $this->assertInternalType('array',$field);
        $this->assertGreaterThan(1,count($field));
    }

    public function testInexistentObjectField() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $field = $zaiusClient->getObjectField('products','foo');

        $this->assertNull($field);
    }

    public function testCreateField() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        try {
            $zaiusClient->createObjectField('products','test_field','string','Test field','Test description');
        }
        catch(ZaiusException $exception) {
            if(!strpos($exception->getMessage(),'already used by another field')) {
                throw $exception;
            }
        }

    }

    public function testGetRelations() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $relations = $zaiusClient->getRelations('products');


        $this->assertInternalType('array',$relations);
        $this->assertGreaterThan(1,count($relations));
    }

    public function testGetRelation() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $relation = $zaiusClient->getRelation('products','category');

        $this->assertInternalType('array',$relation);
        $this->assertArrayHasKey('name',$relation);
    }


    
}