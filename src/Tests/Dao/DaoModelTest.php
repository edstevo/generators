<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Tests\Dao;

use EdStevo\Generators\Tests\DaoTestCase;

class DaoModelTest extends DaoTestCase
{

    public function testDelete()
    {
        $testModel      = $this->testDao->generate();

        $this->seeInDatabase($testModel->getTable(), ['id' => $testModel->getId()]);

        $testModel->daoDestory();

        $this->notSeeInDatabase($testModel->getTable(), ['id' => $testModel->getId()]);
    }

    public function testGetRelation()
    {
        $testModel      = $this->testDao->generate();
        $testModel->getRelationship('tasks');
    }
}
