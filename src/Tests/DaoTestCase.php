<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Tests;

class DaoTestCase extends \TestCase
{

    protected $testDao;

    public function setUp()
    {
        parent::setUp();

        $this->testDao  = app()->make('App\Dao\Product');
    }
}