<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Contracts\Models;


interface DaoModelContract
{

    /**
     * Get the identifier for this model
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get the field used as an identifier for this model
     *
     * @return string
     */
    public function getIdField() : string;
}