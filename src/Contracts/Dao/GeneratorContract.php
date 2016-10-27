<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Dao;

interface GeneratorContract
{

    /**
     * Generate examples of this model
     *
     * @param array $data
     * @param bool  $persist
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function generate(array $data = [], bool $persist = true);

    /**
     * Generate multiple examples of this model
     *
     * @param int   $number
     * @param array $data
     * @param bool  $persist
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function generateMultiple(int $number = 2, array $data = [], bool $persist = true);

}