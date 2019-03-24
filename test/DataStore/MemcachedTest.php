<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataStore\KeyValue\Memcached;
use battlecook\DataStorage\LeafNode;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;
use test\Fixture\DataStore\Quest;

require __DIR__ . '/../../vendor/autoload.php';

class MemcachedTest extends TestCase
{
    private const IP = "memcached";

    /**
     * @var $memcached \Memcached();
     */
    private static $memcached;

    public static function setUpBeforeClass(): void
    {
        self::$memcached = new \Memcached();
        if (self::$memcached->addServer(self::IP, 11211) === false) {
            die('memcached addServer failed');
        }
    }

    public static function tearDownAfterClass()
    {
        self::$memcached = null;
    }

    public function setUp()
    {
        self::$memcached->flush();
    }

    public function testCommit()
    {
        //given
        $key1 = get_class(new Item());
        $value1 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new LeafNode(array(1, 1, 1), array(1, 1, 1)),
                            2 => new LeafNode(array(1, 1, 2), array(1, 1, 1))
                        ),
                )
        );

        $key2 = get_class(new Quest());
        $value2 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => new LeafNode(array(1, 1, 1), array(2, 2, 2)),
                            2 => new LeafNode(array(1, 1, 2), array(2, 2, 2))
                        ),
                )
        );

        $store = new Memcached(null, array(new \battlecook\Config\Memcache(self::IP)));
        $data = array($key1 => $value1, $key2 => $value2);

        //when
        $store->commit($data);

        //then
        $expect1 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => array(1, 1, 1),
                            2 => array(1, 1, 1)
                        ),
                )
        );
        $this->assertEquals($expect1, self::$memcached->get($key1));


        $expect2 = array(
            1 =>
                array(
                    1 =>
                        array(
                            1 => array(2, 2, 2),
                            2 => array(2, 2, 2)
                        ),
                )
        );
        $this->assertEquals($expect2, self::$memcached->get($key2));
    }

    public function testGet()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache(self::IP)));
        $data = array(
            get_class(new Item()) =>
                array(
                    1 =>
                        array(
                            1 =>
                                array(
                                    1 => new LeafNode(array(1, 1, 1), array(1, 1, 1)),
                                    2 => new LeafNode(array(1, 1, 2), array(1, 1, 1))
                                ),
                        )
                )
        );

        $store->commit($data);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;

        //when
        $ret = $store->get($object);

        //then
        $this->assertEquals($object, $ret[0]);
    }

    public function testAdd()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache(self::IP)));

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
    }

    public function testSet()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache(self::IP)));

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
        $ret = $store->get(new Item());
        $this->assertEquals($object2, $ret[0]);
    }

    public function testRemove()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache(self::IP)));

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
        $ret = $store->get($object);
        $this->assertEquals($object2, $ret[0]);
    }
}