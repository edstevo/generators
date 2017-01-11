<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */
namespace EdStevo\Generators\Contracts\Dao;

use EdStevo\Generators\Dao\DaoModel;
use Illuminate\Support\Collection;

interface DaoBaseContract
{

    /**
     * Retrieve all entries of the resource from the DB
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Put a new entry for the resource in the DB
     *
     * @param   array $data
     *
     * @return  \EdStevo\Generators\Dao\DaoModel;
     */
    public function store(array $data);

    /**
     * Find a current instance or create a new one
     *
     * @param array $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function firstOrCreate(array $data) : DaoModel;

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int $id
     *
     * @return \EdStevo\Generators\Dao\DaoModel;
     */
    public function find($id);

    /**
     * Retrieve an entry of the resource from the DB
     * If the resource cannot be found, throw ModelNotFoundException
     *
     * @param  int  $id
     *
     * @return \EdStevo\Generators\Dao\DaoModel;
     *
     * @throws \EdStevo\Generators\Dao\DaoModelNotFoundException;
     */
    public function findOrFail($id) : DaoModel;

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel|null;
     */
    public function findWhere(array $data);

    /**
     * Retrieve multiple entries of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where(array $data);

    /**
     * Retrieve multiple entries of the resource from the DB where it matches an attribute
     *
     * @param  array  $ids
     * @param  string  $attribute
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function whereIn(array $ids, string $attribute = null);

    /**
     * Update the specified resource in the DB.
     *
     * @param \EdStevo\Generators\Dao\DaoModel  $model
     * @param array     $data
     * @param string $attribute
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function update(DaoModel $model, array $data) : DaoModel;

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \EdStevo\Generators\Dao\DaoModel  $model
     *
     * @return  boolean
     */
    public function destroy(DaoModel $model) : bool;

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relation
     *
     * @return  mixed
     */
    public function getRelation(DaoModel $model, string $relation);

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relation
     * @param   array                               $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere(DaoModel $model, string $relation, array $constraints = []) : Collection;

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param array                            $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function storeRelation(DaoModel $model, string $relationship, array $data = []) : DaoModel;

    /**
     * Update a relation of the model
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     * @param array                            $data
     *
     * @return \EdStevo\Generators\Dao\DaoModel
     */
    public function updateRelation(DaoModel $model, string $relationship, DaoModel $relation, array $data) : DaoModel;

    /**
     * Destroy a relation and fire an event attached with this model
     *
     * @param \EdStevo\Generators\Dao\DaoModel $model
     * @param string                           $relationship
     * @param \EdStevo\Generators\Dao\DaoModel $relation
     *
     * @return bool|null
     */
    public function destroyRelation(DaoModel $model, string $relationship, DaoModel $relation);

    /**
     * Associate a model with a relation via a pivot
     *
     * @param   \EdStevo\Generators\Dao\DaoModel    $model
     * @param   string                              $relationship
     * @param   \EdStevo\Generators\Dao\DaoModel    $relation
     * @param   array                               $pivot_data
     *
     * @param   null
     */
    public function attach(DaoModel $model, string $relationship, DaoModel $relation, array $pivot_data = []);

    /**
     * Sync a model and its relations via a pivot
     *
     * @param   \EdStevo\Generators\Dao\DaoModel    $model
     * @param   string                              $relation
     * @param   int/string                          $relation_id
     * @param   bool                                $detaching
     *
     * @param   array
     */
    public function sync(DaoModel $model, string $relationship, $relation_id, bool $detaching = true);

    /**
     * Update a pivot table across a many to many relationship
     *
     * @param        $model
     * @param string $relationship
     * @param        $relation
     * @param array  $pivot_data
     *
     * @return mixed
     */
    public function updatePivot(DaoModel $model, string $relationship, DaoModel $relation, array $pivot_data = []);

    /**
     * Dissociate a model with a relation via a pivot
     *
     * @param   \EdStevo\Generators\Dao\DaoModel $model
     * @param   string                              $relationship
     * @param   \EdStevo\Generators\Dao\DaoModel $relation
     *
     * @param   bool
     */
    public function detach(DaoModel $model, string $relationship, DaoModel $relation) : bool;

    /**
     * Get the validation rules associated with a model
     *
     * @return  array
     */
    public function getRules();

    /**
     * Throw exception when model cannot be found
     *
     * @throws  \EdStevo\Generators\Dao\Exceptions\ModelNotFoundException
     */
    public function notFound();

    /**
     * Get the Name of the Related Model
     *
     * @param $model
     * @param $relation
     *
     * @return string
     */
    public function getRelationModel(DaoModel $model, DaoModel $relation) : string;

    /**
     * Get the Class Name from a namespace
     *
     * @param $namespace
     *
     * @return mixed
     */
    public function getClassName($namespace);

}