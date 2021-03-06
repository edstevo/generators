<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Generators\Dao;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class DaoModel extends Model
{

    /**
     * Get the appends data for this model
     *
     * @return array
     */
    public function getAppends()
    {
        return $this->appends;
    }

    /**
     * Get the identifier for this model
     *
     * @return mixed
     */
    abstract function getId();

    /**
     * Get the visual identifier for this model
     *
     * @return mixed
     */
    public function getVisualId()
    {
        return $this->getId();
    }

    /**
     * Get the field used as an identifier for this model
     *
     * @return string
     */
    public function getIdField() : string
    {
        return 'id';
    }

    /**
     * Expressive way to use the destroy method via dao repository
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function daoUpdate(array $data = []) : DaoModel
    {
        return $this->getDaoRepository()->update($this, $data);
    }

    /**
     * Expressive way to use the destroy method via dao repository
     *
     * @return bool
     */
    public function daoDestroy() : bool
    {
        return $this->getDaoRepository()->destroy($this);
    }

    /**
     * Expressive way to use the get relation method with this model via the dao repository
     *
     * @param string $relation
     *
     * @return mixed
     */
    public function getRelationship(string $relation)
    {
        return $this->getDaoRepository()->getRelation($this, $relation);
    }

    /**
     * Expressive way to use the get relation where method with this model via the dao repository
     *
     * @param string $relation
     * @param array  $constraints
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRelationshipWhere(string $relation, array $constraints = []) : Collection
    {
        return $this->getDaoRepository()->getRelationWhere($this, $relation, $constraints);
    }

    /**
     * Expressive way to use the store relation method with this model via the dao repository
     *
     * @param string $relation
     * @param array  $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function storeRelationship(string $relation, array $data = []) : DaoModel
    {
        return $this->getDaoRepository()->storeRelation($this, $relation, $data);
    }

    /**
     * Expressive way to use the update relation method with this model via the dao repository
     *
     * @param string $relationship
     * @param DaoModel $relation
     * @param array  $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function updateRelationship(string $relationship, DaoModel $relation, array $data = []) : DaoModel
    {
        return $this->getDaoRepository()->updateRelation($this, $relationship, $relation, $data);
    }

    /**
     * Expressive way to use the destroy relation method with this model via the dao repository
     *
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     *
     * @return bool
     */
    public function destroyRelationship(string $relationship, DaoModel $relation) : bool
    {
        return $this->getDaoRepository()->destroyRelation($this, $relationship, $relation);
    }

    /**
     * Expressive way to use the attach method with this model via the dao repository
     *
     * @param string                              $relation
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param array                               $pivotData
     */
    public function attach(string $relation, Model $model, array $pivotData = [])
    {
        return $this->getDaoRepository()->attach($this, $relation, $model, $pivotData);
    }

    /**
     * Expressive way to use the sync method with this model via the dao repository
     *
     * @param string $relationship
     * @param        $relation_id
     * @param bool   $detaching
     *
     * @return mixed
     */
    public function sync(string $relationship, $relation_id, bool $detaching = true)
    {
        return $this->getDaoRepository()->sync($this, $relationship, $relation_id, $detaching);
    }

    /**
     * Expressive way to use the detach method with this model via the dao repository
     *
     * @param        $model
     * @param string $relationship
     * @param        $relation
     *
     * @return bool
     */
    public function detach(string $relationship, $relation) : bool
    {
        return $this->getDaoRepository()->detach($this, $relationship, $relation);
    }

    /**
     * Expressive way to use the get rules method with this model via the dao repository
     *
     * @return array
     */
    public function getRules() : array
    {
        return $this->getDaoRepository()->getRules();
    }

    /**
     * Return the dao repository related to this model
     *
     * @return \EdStevo\Generators\Dao\Eloquent\DaoBase
     */
    public function getDaoRepository()
    {
        return app()->make('App\Dao\\' . $this->getModelName());
    }

    /**
     * Get the name of this model
     *
     * @return string
     */
    private function getModelName() : string
    {
        return collect(explode('\\', get_class($this)))->last();
    }
}