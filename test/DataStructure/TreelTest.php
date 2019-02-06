<?php
declare(strict_types=1);

namespace test\DataStructure;

use battlecook\DataStructure\Tree;
use PHPUnit\Framework\TestCase;

require __DIR__  . '/../../vendor/autoload.php';

class TreeTest extends TestCase
{
    public function testTreeInsert()
    {
        //given
        $withAutoIncrement = true;
        $keys = array(1, "2", 3);
        $value = array("attr1", "attr2", "attr3");
        $tree = new Tree($withAutoIncrement, count($keys));

        //when
        $ret = $tree->insert($keys, $value);

        //then
        $this->assertEquals(true, $ret);
    }

    public function testTreeSearch()
    {
        //given
        $withAutoIncrement = true;
        $keys = array(1, "2", 3);
        $value = array("attr1", "attr2", "attr3");
        $tree = new Tree($withAutoIncrement, count($keys));
        $tree->insert($keys, $value);

        //when
        $keys = array(1, "2");
        $ret = $tree->search($keys);

        //then
    }
}
