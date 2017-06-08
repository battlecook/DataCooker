<?php

namespace test\DataStore;

use battlecook\DataStore\RedisDataStore;
use PHPUnit\Framework\TestCase;
use test\Fixture\ExcelDataStore\Monster;
use test\Fixture\PdoDataStore\GetOtherStore\Quest;

require __DIR__  . '/../../vendor/autoload.php';

class RedisDataStoreTest extends TestCase
{
    public function setUp()
    {
        $redis = new \Redis();
        $redis->pconnect("localhost", 6379);
        $config = new Config();
        $redis->auth($config->getRedisPassword());

        $redis->flushAll();
    }

    public function testGet()
    {
        //given
        $keyPrefix = 'ProjectName';
        $store = new RedisDataStore(null, function (){
            $redis = new \Redis();
            $redis->pconnect("localhost", 6379);
            $config = new Config();
            $redis->auth($config->getRedisPassword());

            return $redis;
        }, $keyPrefix);

        //when
        $object = new Quest();
        $object->key1 = 1;
        $object->key2 = 1;
        $object->key3 = 1;
        $object->attr = 'attr1';

        $store->add($object);

        $object = new Quest();
        $object->key1 = 1;
        $object->key2 = 1;
        $object->key3 = 2;
        $object->attr = 'attr2';

        $store->add($object);

        $object = new Quest();
        $object->key1 = 1;
        $object->key2 = 2;
        $object->key3 = 1;
        $object->attr = 'attr3';

        $store->add($object);

        //then
        $object = new Quest();
        $object->key1 = 1;
        $object->key2 = 2;
        $object->key3 = 1;

        $ret = $store->get($object);
        $this->assertEquals(1, count($ret));
        $this->assertEquals('attr3', $ret[0]->attr);
    }

    public function testAdd()
    {
        //given
        $keyPrefix = 'ProjectName';
        $store = new RedisDataStore(null, function (){
            $redis = new \Redis();
            $redis->pconnect("localhost", 6379);
            $config = new Config();
            $redis->auth($config->getRedisPassword());

            return $redis;
        }, $keyPrefix);

        //when
        $object = new Monster();
        $object->id = 2;
        $object->x = 2;
        $object->y = 3;

        $store->add($object);

        //then
        $ret = $store->get($object);
        $this->assertEquals(array($object), $ret);
    }
}
