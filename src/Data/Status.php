<?php
declare(strict_types=1);

namespace battlecook\Data;

use battlecook\DataCookerException;

final class Status
{
    const NONE = 0;
    const UPDATED = 1;
    const INSERTED = 2;
    const DELETED = 3;

    const UNSET = 4;

    /**
     * @param int $before
     * @param int $after
     * @return int
     * @throws DataCookerException
     */
    private static function getStatus(int $before, int $after): int
    {
        $changedStatus = $before;
        if($before === self::UPDATED || $before === self::NONE)
        {
            if($after === self::UPDATED)
            {
                $changedStatus = self::UPDATED;
            }
            else if($after === self::INSERTED)
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
            else if($after === self::DELETED)
            {
                $changedStatus = self::DELETED;
            }
            else
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
        }
        else if($before === self::DELETED)
        {
            if($after === self::UPDATED)
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
            else if($after === self::INSERTED)
            {
                $changedStatus = self::UPDATED;
            }
            else if($after === self::DELETED)
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
            else
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
        }

        return $changedStatus;
    }

    /**
     *
     * case with auto increment
     *
     *     current       next       changed
     *
     * None, Set -----    Set    -->    Set
     *            ㄴ      Add    -->   error
     *            ㄴ      Del    -->    Del
     *
     *      Add  ----     Set    -->     Set
     *            ㄴ      Add    -->    error
     *            ㄴ      Del    -->     Del
     *
     *      Del  ----     Set    -->     error
     *            ㄴ      Add    -->      Set
     *            ㄴ      Del    -->     error
     *
     *
     * @param int $before
     * @param int $after
     * @return int
     * @throws DataCookerException
     */
    public static function getStatusWithAutoIncrement(int $before, int $after): int
    {
        $changedStatus = self::getStatus($before, $after);
        if($before === self::INSERTED)
        {
            if($after === self::UPDATED)
            {
                $changedStatus = self::UPDATED;
            }
            else if($after === self::INSERTED)
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
            else if($after === self::DELETED)
            {
                $changedStatus = self::DELETED;
            }
            else
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
        }

        return $changedStatus;
    }

    /**
     * @param int $before
     * @param int $after
     * @return int
     * @throws DataCookerException
     *
     *
     *  case without auto increment
     *
     *     current       next       changed
     *
     * None, Set -----    Set    -->    Set
     *            ㄴ      Add    -->   error
     *            ㄴ      Del    -->    Del
     *
     *      Add  ----     Set    -->     Add
     *            ㄴ      Add    -->    error
     *            ㄴ      Del    -->     unset data
     *
     *      Del  ----     Set    -->     error
     *            ㄴ      Add    -->      Set
     *            ㄴ      Del    -->     error
     *
     */
    public static function getStatusWithoutAutoincrement(int $before, int $after): int
    {
        $changedStatus = self::getStatus($before, $after);
        if($before === self::INSERTED)
        {
            if($after === self::UPDATED)
            {
                $changedStatus = self::INSERTED;
            }
            else if($after === self::INSERTED)
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
            else if($after === self::DELETED)
            {
                $changedStatus = self::UNSET;
            }
            else
            {
                throw new DataCookerException("invalid status before : $before , after : $after , with auto increment : $withAutoIncrement");
            }
        }

        return $changedStatus;
    }
}