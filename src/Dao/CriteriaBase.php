<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Dao\Eloquent;

use EdStevo\Generators\Contracts\Dao\DaoBase;

abstract class CriteriaBase
{

    /**
     * @param $model
     * @param DaoBase $repository
     * @return mixed
     */
    public abstract function apply($model, DaoBase $repository);

}