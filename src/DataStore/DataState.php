<?php

namespace battlecook\DataStore;

class DataState
{
    const CLEAR = 0;
    const DIRTY_ADD = 1;
    const DIRTY_SET = 2;
    const DIRTY_DEL = 3;
}