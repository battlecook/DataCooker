<?php

namespace battlecook\DataObject;

interface DataObject
{
    public function getShardKey();

    public function getIdentifiers();

    public function getAutoIncrements();

    public function getAttributes();

    public function getShortName();
}