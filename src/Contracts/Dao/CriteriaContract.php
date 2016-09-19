<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace FlowflexComponents\Generators\Contracts\Dao;

use FlowflexComponents\Generators\Dao\CriteriaBase;

/**
 * Interface CriteriaInterface
 * @package Bosnadev\Repositories\Contracts
 */
interface CriteriaContract
{

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true);

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