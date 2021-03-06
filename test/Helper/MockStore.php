<?php
declare(strict_types=1);

namespace test\Helper;

use battlecook\DataStore\AbstractStore;
use battlecook\DataStore\IDataStore;

final class MockStore extends AbstractStore implements IDataStore
{
    public function setUp($object)
    {
        $this->setMeta($object);
        $this->cache(get_class($object));
    }

    public function tearDown()
    {
        AbstractStore::initializeCachedMeta();
    }

    public function add($object)
    {
        // TODO: Implement add() method.
    }

    public function get($object)
    {
        // TODO: Implement get() method.
    }

    public function search($object): array
    {
        // TODO: Implement search() method.
    }

    public function set($object)
    {
        // TODO: Implement set() method.
    }

    public function remove($object)
    {
        // TODO: Implement remove() method.
    }

    public function commitAll($data = null)
    {
        // TODO: Implement commit() method.
    }
}