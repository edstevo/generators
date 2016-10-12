<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace App\Contracts\Dao;

use App\Dao\Eloquent\CriteriaBase;

interface CriteriaContract
{

    /**
     * @return $this
     */
    public function resetScope();

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria(bool $status = true);

    /**
     * @return mixed
     */
    public function getCriteria();

    /**
     * @param CriteriaBase $criteria
     * @return $this
     */
    public function getByCriteria(CriteriaBase $criteria);

    /**
     * @param CriteriaBase $criteria
     * @return $this
     */
    public function pushCriteria(CriteriaBase $criteria);

    /**
     * @return $this
     */
    public function  applyCriteria();

}