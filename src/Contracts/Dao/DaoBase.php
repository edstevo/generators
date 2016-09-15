<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */
namespace FlowflexComponents\Generators\Contracts\Dao;

interface DaoBase
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
     * @return  \Illuminate\Database\Eloquent\Model;
     */
    public function store($data);

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int $id
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function find($id);

    /**
     * Retrieve an entry of the resource from the DB
     * If the resource cannot be found, throw ModelNotFoundException
     *
     * @param  int $id
     *
     * @return \Illuminate\Database\Eloquent\Model;
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function findOrFail($id);

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function findWhere($data);

    /**
     * Retrieve multiple entries of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where($data);

    /**
     * Update the specified resource in the DB.
     *
     * @param   array   $data
     * @param   int     $id
     * @param   string  $attribute
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function update(array $data, $id, $attribute = "id");

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     *
     * @return  boolean
     */
    public function destroy($model);

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relationship
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelation($model, $relationship);

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   array                               $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere($model, $relation, $constraints = []);

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   array                               $data
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     */
    public function storeRelation($model, $relation, $data);

    /**
     * Get the validation rules associated with a model
     *
     * @return  array
     */
    public function getRules();

    /**
     * Throw exception when model cannot be found
     *
     * @throws  \FlowflexComponents\Generators\Dao\Exceptions\ModelNotFoundException
     */
    public function notFound();
}