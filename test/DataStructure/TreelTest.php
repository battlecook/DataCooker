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
        $tree = new Tree();
        $keys = array(1, "2", 3);
        $value = array("attr1", "attr2", "attr3");

        //when
        $tree->insert($keys, $value);

        //then
    }
}
