<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Contracts\Dao;


interface EventsContract
{

    /**
     * Reset the repository skip events status
     *
     * @return $this
     */
    public function resetEvents();

    /**
     * Set the repository to skip the events
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipEvents(bool $status = true);

}