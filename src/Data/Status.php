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
     * @param int $before
     * @param int $after
     * @param bool $withAutoIncrement
     * @return int
     * @throws DataCookerException
     */
    public function getStatus(int $before, int $after, $withAutoIncrement): int
    {
        if($before === self::UPDATED || $before === self::NONE)
        {
            if($after === self::UPDATED)
            {
                $changedStatus = self::UPDATED;
            }
            else if($after === self::INSERTED)
            {
                throw new DataCookerException();
            }
            else if($after === self::DELETED)
            {
                $changedStatus = self::DELETED;
            }
            else
            {
                throw new DataCookerException();
            }
        }
        else if($before === self::INSERTED)
        {


            if($withAutoIncrement === true)
            {

                if($after === self::UPDATED)
                {
                    $changedStatus = self::UPDATED;
                }
                else if($after === self::INSERTED)
                {
                    throw new DataCookerException();
                }
                else if($after === self::DELETED)
                {
                    $changedStatus = self::DELETED;
                }
                else
                {
                    throw new DataCookerException();
                }

            }
            else
            {


            }






        }
        else if($before === self::DELETED)
        {
            if($after === self::UPDATED)
            {
                throw new DataCookerException();
            }
            else if($after === self::INSERTED)
            {
                $changedStatus = self::UPDATED;
            }
            else if($after === self::DELETED)
            {
                throw new DataCookerException();
            }
            else
            {
                throw new DataCookerException();
            }
        }
        else
        {
            throw new DataCookerException();
        }

        return $changedStatus;
    }
}