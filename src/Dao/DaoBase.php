<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace FlowflexComponents\Generators\Dao;

use FlowflexComponents\Generators\Contracts\Dao\CriteriaContract;
use FlowflexComponents\Generators\Contracts\Dao\DaoBase as DaoBaseContract;
use FlowflexComponents\Generators\Dao\Exceptions\ModelNotFoundException;
use FlowflexComponents\Generators\Dao\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class DaoBase implements DaoBaseContract, CriteriaContract
{

    /**
     * @var
     */
    protected $model;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    public function __construct(Collection $collection)
    {
        $this->criteria         = $collection;
        $this->resetScope();
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract protected function model();

    /**
     * @return Model
     * @throws RepositoryException
     */
    protected function makeModel() {
        $model  = resolve($this->model());

        if (!$model instanceof Model)
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

    /**
     * Retrieve all entries of the resource from the DB
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        $this->applyCriteria();
        return $this->model->get();
    }

    /**
     * Put a new entry for the resource in the DB
     *
     * @param   array  $data
     *
     * @return  \Illuminate\Database\Eloquent\Model;
     */
    public function store($data)
    {
        return $this->model->create($data);
    }

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int  $id
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function find($id)
    {
        $this->applyCriteria();
        return $this->model->find($id);
    }

    /**
     * Retrieve an entry of the resource from the DB
     * If the resource cannot be found, throw ModelNotFoundException
     *
     * @param  int  $id
     *
     * @return \Illuminate\Database\Eloquent\Model;
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public function findOrFail($id)
    {
        $this->applyCriteria();
        return $this->model->findOrFail($id);
    }

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array  $data
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function findWhere($data)
    {
        $this->applyCriteria();
        return $this->model->where($data)->first();
    }

    /**
     * Retrieve multiple entries of the resource from the DB where it matches certain criteria
     *
     * @param  array  $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where($data)
    {
        $this->applyCriteria();
        return $this->model->where($data)->get();
    }

    /**
     * Update the specified resource in the DB.
     *
     * @param   array   $data
     * @param   int     $id
     * @param   string  $attribute
     *
     * @return \Illuminate\Database\Eloquent\Model;
     */
    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \Illuminate\Database\Eloquent\Model  $model
     *
     * @return  boolean
     */
    public function destroy($model)
    {
        return $model->delete();
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelation($model, $relation)
    {
        return $model->$relation;
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   array                               $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere($model, $relation, $constraints = [])
    {
        return $model->$relation()->where($constraints)->get();
    }

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @param   string                              $relation
     * @param   array                               $data
     *
     * @param   \Illuminate\Database\Eloquent\Model $model
     */
    public function storeRelation($model, $relation, $data)
    {
        return $model->$relation()->create($data);
    }

    /**
     * Get the validation rules associated with a model
     *
     * @return  array
     */
    public function getRules()
    {
        return $this->model->rules();
    }

    /**
     * Throw exception when model cannot be found
     *
     * @throws  ModelNotFoundException
     */
    public function notFound()
    {
        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * @return $this
     */
    public function resetScope() {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true){
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * @param CriteriaBase $criteria
     * @return $this
     */
    public function getByCriteria(CriteriaBase $criteria) {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param CriteriaBase $criteria
     * @return $this
     */
    public function pushCriteria(CriteriaBase $criteria) {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function  applyCriteria() {
        if($this->skipCriteria === true)
            return $this;

        foreach($this->getCriteria() as $criteria) {
            if($criteria instanceof CriteriaBase)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

}