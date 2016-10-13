<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Dao\Eloquent;

use EdStevo\Generators\Contracts\Dao\DaoBase as DaoBaseContract;

abstract class CriteriaBase
{

    /**
     * @param $model
     * @param DaoBaseContract $repository
     * @return mixed
     */
    public abstract function apply($model, DaoBaseContract $repository);

}