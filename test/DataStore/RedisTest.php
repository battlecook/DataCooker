<?php
declare(strict_types=1);

namespace test\DataStore;

use battlecook\DataStore\KeyValue\Redis;
use battlecook\Types\LeafNode;
use battlecook\Types\Attribute;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStore\Item;
use test\Fixture\DataStore\Quest;
use test\Helper\Config;
use test\Helper\MockStore;
use test\Helper\Option;

require __DIR__ . '/../../vendor/autoload.php';

class RedisTest extends TestCase
{
    /**
     * @var $redis \Redis();
     */
    private static $redis;

    public static function setUpBeforeClass(): void
    {
        self::$redis = new \Redis();

        if (self::$redis->pconnect(Option::$redisIP, 6379) === false) {
            throw new DataCookerException("redis connection failed");
        }

        $ret = self::$redis->ping();
        if ($ret !== '+PONG') {
            throw new DataCookerException("redis ping failed");
        }
    }

    public static function tearDownAfterClass()
    {
        self::$redis = null;
    }

    public function setUp()
    {
        $mockStore = new MockStore();
        $mockStore->tearDown();
        $mockStore->setUp(new Item());

        self::$redis->flushAll();
    }

    private function createStore()
    {
        return new Redis(Option::getRedisConfig());
    }

    public function testCommit()
    {
        //given
        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;

        $key1 = get_class(new Item());
        $value1 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new LeafNode(array(1, 1, 1), $object1),
                            2 => new LeafNode(array(1, 1, 2), $object2)
                        ),
                )
        );

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 1;
        $object3->id3 = 1;
        $object3->attr1 = 2;
        $object3->attr2 = 2;
        $object3->attr3 = 2;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 1;
        $object4->id3 = 2;
        $object4->attr1 = 2;
        $object4->attr2 = 2;
        $object4->attr3 = 2;

        $key2 = get_class(new Quest());
        $value2 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new LeafNode(array(1, 1, 1), $object3),
                            2 => new LeafNode(array(1, 1, 2), $object4)
                        ),
                )
        );

        $store = $this->createStore();
        $data = array($key1 => $value1, $key2 => $value2);

        //when
        $store->commitAll($data);

        //then
        $expect1 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new Attribute(array(1, 1, 1)),
                            2 => new Attribute(array(1, 1, 1)),
                        ),
                )
        );

        $this->assertEquals($expect1, unserialize(self::$redis->get($key1 . '\\' . 1)));

        $expect2 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new Attribute(array(2, 2, 2)),
                            2 => new Attribute(array(2, 2, 2))
                        ),
                )
        );
        $this->assertEquals($expect2, unserialize(self::$redis->get($key2 . '\\' . 1)));
    }

    public function testSearchRoot()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 2;
        $object3->id3 = 1;
        $object3->attr1 = 1;
        $object3->attr2 = 1;
        $object3->attr3 = 1;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 2;
        $object4->id3 = 2;
        $object4->attr1 = 1;
        $object4->attr2 = 1;
        $object4->attr3 = 1;

        $object5 = new Item();
        $object5->id1 = 2;
        $object5->id2 = 1;
        $object5->id3 = 1;
        $object5->attr1 = 1;
        $object5->attr2 = 1;
        $object5->attr3 = 1;

        $object6 = new Item();
        $object6->id1 = 2;
        $object6->id2 = 1;
        $object6->id3 = 2;
        $object6->attr1 = 1;
        $object6->attr2 = 1;
        $object6->attr3 = 1;

        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), $object1),
                                    2 => new LeafNode(array(1, 1, 2), $object2)
                                ),

                            2 =>
                                array(
                                    1 => new LeafNode(array(1, 2, 1), $object3),
                                    2 => new LeafNode(array(1, 2, 2), $object4)
                                ),
                        ),
                    2 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(2, 1, 1), $object5),
                                    2 => new LeafNode(array(2, 1, 2), $object6)
                                ),
                        )
                )
        );

        $store->commitAll($data);

        $object = new Item();
        $object->id1 = 1;

        //when
        $ret = $store->search($object);

        //then
        $this->assertEquals(4, count($ret));
    }

    public function testSearchInternal()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 2;
        $object3->id3 = 1;
        $object3->attr1 = 3;
        $object3->attr2 = 3;
        $object3->attr3 = 3;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 2;
        $object4->id3 = 2;
        $object4->attr1 = 4;
        $object4->attr2 = 4;
        $object4->attr3 = 4;

        $object5 = new Item();
        $object5->id1 = 2;
        $object5->id2 = 1;
        $object5->id3 = 1;
        $object5->attr1 = 5;
        $object5->attr2 = 5;
        $object5->attr3 = 5;

        $object6 = new Item();
        $object6->id1 = 2;
        $object6->id2 = 1;
        $object6->id3 = 2;
        $object6->attr1 = 6;
        $object6->attr2 = 6;
        $object6->attr3 = 6;

        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), $object1),
                                    2 => new LeafNode(array(1, 1, 2), $object2)
                                ),

                            2 =>
                                array(
                                    1 => new LeafNode(array(1, 2, 1), $object3),
                                    2 => new LeafNode(array(1, 2, 2), $object4)
                                ),
                        ),
                    2 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(2, 1, 1), $object5),
                                    2 => new LeafNode(array(2, 1, 2), $object6)
                                ),

                        )
                )
        );

        $store->commitAll($data);

        $object = new Item();
        $object->id1 = 2;
        $object->id2 = 1;

        //when
        $ret = $store->search($object);

        //then
        $this->assertEquals(2, count($ret));
        $this->assertEquals($object5, $ret[0]);
        $this->assertEquals($object6, $ret[1]);
    }

    public function testSearchLeaf()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 2;
        $object3->id3 = 1;
        $object3->attr1 = 3;
        $object3->attr2 = 3;
        $object3->attr3 = 3;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 2;
        $object4->id3 = 2;
        $object4->attr1 = 4;
        $object4->attr2 = 4;
        $object4->attr3 = 4;

        $object5 = new Item();
        $object5->id1 = 2;
        $object5->id2 = 1;
        $object5->id3 = 1;
        $object5->attr1 = 5;
        $object5->attr2 = 5;
        $object5->attr3 = 5;

        $object6 = new Item();
        $object6->id1 = 2;
        $object6->id2 = 1;
        $object6->id3 = 2;
        $object6->attr1 = 6;
        $object6->attr2 = 6;
        $object6->attr3 = 6;

        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), $object1),
                                    2 => new LeafNode(array(1, 1, 2), $object2)
                                ),

                            2 =>
                                array(
                                    1 => new LeafNode(array(1, 2, 1), $object3),
                                    2 => new LeafNode(array(1, 2, 2), $object4)
                                ),
                        ),
                    2 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(2, 1, 1), $object5),
                                    2 => new LeafNode(array(2, 1, 2), $object6)
                                ),

                        )
                )
        );

        $store->commitAll($data);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 2;
        $object->id3 = 1;

        //when
        $ret = $store->search($object);

        //then
        $this->assertEquals($object3, $ret[0]);
    }


    public function testGet()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 2;
        $object3->id3 = 1;
        $object3->attr1 = 3;
        $object3->attr2 = 3;
        $object3->attr3 = 3;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 2;
        $object4->id3 = 2;
        $object4->attr1 = 4;
        $object4->attr2 = 4;
        $object4->attr3 = 4;

        $object5 = new Item();
        $object5->id1 = 2;
        $object5->id2 = 1;
        $object5->id3 = 1;
        $object5->attr1 = 5;
        $object5->attr2 = 5;
        $object5->attr3 = 5;

        $object6 = new Item();
        $object6->id1 = 2;
        $object6->id2 = 1;
        $object6->id3 = 2;
        $object6->attr1 = 6;
        $object6->attr2 = 6;
        $object6->attr3 = 6;

        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), $object1),
                                    2 => new LeafNode(array(1, 1, 2), $object2)
                                ),

                            2 =>
                                array(
                                    1 => new LeafNode(array(1, 2, 1), $object3),
                                    2 => new LeafNode(array(1, 2, 2), $object4)
                                ),
                        ),
                    2 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(2, 1, 1), $object5),
                                    2 => new LeafNode(array(2, 1, 2), $object6)
                                ),

                        )
                )
        );

        $store->commitAll($data);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 2;
        $object->id3 = 1;

        //when
        $ret = $store->get($object);

        //then
        $this->assertEquals($object3, $ret);
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage already exist data at leafnode
     */
    public function testAddAlreadyExistData()
    {
        //given
        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;

        $key1 = get_class(new Item());
        $value1 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new LeafNode(array(1, 1, 1), $object1),
                            2 => new LeafNode(array(1, 1, 2), $object2)
                        ),
                )
        );

        $store = $this->createStore();
        $data = array($key1 => $value1);

        $store->commitAll($data);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $store->add($object);

        //then
    }

    public function testAddEmptyData()
    {
        //given
        $store = $this->createStore();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $store->add($object);

        //then
        $this->assertEquals($object, $ret);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $expected = $store->search($object);

        $this->assertEquals($expected[0], $ret);

    }

    public function testAdd()
    {
        //given
        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;

        $key1 = get_class(new Item());
        $value1 = array(
            1 =>
                array(
                    1 =>
                        array(
                            2 => new LeafNode(array(1, 1, 2), $object2)
                        ),
                )
        );

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 1;
        $object3->id3 = 1;
        $object3->attr1 = 2;
        $object3->attr2 = 2;
        $object3->attr3 = 2;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 1;
        $object4->id3 = 2;
        $object4->attr1 = 2;
        $object4->attr2 = 2;
        $object4->attr3 = 2;

        $key2 = get_class(new Quest());
        $value2 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new LeafNode(array(1, 1, 1), $object3),
                            2 => new LeafNode(array(1, 1, 2), $object4)
                        ),
                )
        );

        $store = $this->createStore();
        $data = array($key1 => $value1, $key2 => $value2);

        $store->commitAll($data);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $store->add($object);

        //then
        $this->assertEquals($object, $ret);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $expected = $store->search($object);

        $this->assertEquals($expected[0], $ret);
    }

    public function testSet()
    {
        //given
        $store = $this->createStore();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;
        $store->add($object);

        //when
        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 1;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;
        $store->set($object2);

        //then
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $ret = $store->search($object);
        $this->assertEquals($object2, $ret[0]);
    }

    public function testRemoveLeaf()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $store->add($object1);

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;
        $store->add($object2);

        //when
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $store->remove($object);

        //then
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $ret = $store->search($object);
        $this->assertEquals($object2, $ret[0]);
    }

    public function testRemoveInternal()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 2;
        $object3->id3 = 1;
        $object3->attr1 = 3;
        $object3->attr2 = 3;
        $object3->attr3 = 3;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 2;
        $object4->id3 = 2;
        $object4->attr1 = 4;
        $object4->attr2 = 4;
        $object4->attr3 = 4;

        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), $object1),
                                    2 => new LeafNode(array(1, 1, 2), $object2)
                                ),

                            2 =>
                                array(
                                    1 => new LeafNode(array(1, 2, 1), $object3),
                                    2 => new LeafNode(array(1, 2, 2), $object4)
                                ),
                        ),
                )
        );

        $store->commitAll($data);

        //when
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $store->remove($object);

        //then
        $object = new Item();
        $object->id1 = 1;
        $ret = $store->search($object);

        $this->assertEquals(array($object3, $object4), $ret);
    }

    public function testRemoveAll()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;

        $object3 = new Item();
        $object3->id1 = 1;
        $object3->id2 = 2;
        $object3->id3 = 1;
        $object3->attr1 = 3;
        $object3->attr2 = 3;
        $object3->attr3 = 3;

        $object4 = new Item();
        $object4->id1 = 1;
        $object4->id2 = 2;
        $object4->id3 = 2;
        $object4->attr1 = 4;
        $object4->attr2 = 4;
        $object4->attr3 = 4;

        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), $object1),
                                    2 => new LeafNode(array(1, 1, 2), $object2)
                                ),

                            2 =>
                                array(
                                    1 => new LeafNode(array(1, 2, 1), $object3),
                                    2 => new LeafNode(array(1, 2, 2), $object4)
                                ),
                        ),
                )
        );

        $store->commitAll($data);

        //when
        $object = new Item();
        $object->id1 = 1;
        $store->remove($object);

        //then
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $ret = $store->search($object);
        $this->assertEquals(array(), $ret);
    }
}