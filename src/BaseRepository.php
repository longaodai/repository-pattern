<?php

namespace LongAoDai\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class BaseRepository
 *
 * @package LongAoDai\Repositories
 */
abstract class BaseRepository
{
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->registerModel();
    }

    /**
     * @return mixed
     */
    abstract function model();

    /**
     * Register model
     *
     * @return mixed
     */
    public function registerModel()
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new ModelNotFoundException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model->newQuery();
    }

    /**
     * Reset model -> new query
     *
     * @return mixed
     */
    public function resetModel()
    {
        return $this->registerModel();
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function method($name)
    {
        $argList = func_get_args();
        unset($argList[0]);

        return $this->model->{$name}(...$argList);
    }

    /**
     * Get all
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function all($data = null, $options = null)
    {
        $params = $this->buildParams($data, $options);
        $this->filter($params);

        return $this->method('get');
    }

    /**
     * Get data with paginate
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function getList($data = null, $options = null)
    {
        $params = $this->buildParams($data, $options);
        $this->filter($params);

        return $this->method('paginate', $this->getPaginate($params));
    }

    /**
     * Get by id
     *
     * @param $data
     *
     * @return mixed
     */
    public function find($data)
    {
        $params = $this->buildParams($data);

        return $this->method('find', $params->get('id'));
    }

    /**
     * Get first by condition
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function first($data = null, $options = null)
    {
        $params = $this->buildParams($data, $options);
        $this->filter($params);

        return $this->method('first');
    }

    /**
     * Create data
     *
     * @param $data
     *
     * @return mixed
     */
    public function create($data = null)
    {
        $params = $this->buildParams($data);

        return $this->method('create', $params->get());
    }

    /**
     * Update data
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function update($data = null, $options = null)
    {
        $params = $this->buildParams($data, $options);
        $this->mark($params);

        return $this->method('update', $params->get());
    }

    /**
     * Update or Create where condition in options
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function updateOrCreate($data = null, $options = null)
    {
        $params = $this->buildParams($data, $options);

        return $this->method('updateOrCreate', $params->option(), $params->get());
    }

    /**
     * Destroy data
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function destroy($data = null, $options = null)
    {
        $params = $this->buildParams($data, $options);

        $this->filter($params);

        return $this->method('delete');
    }

    /**
     * Build params ($params = [data => $data, options => $options])
     *
     * @param $data
     * @param $options
     *
     * @return RepositoryParams
     */
    public function buildParams($data = null, $options = null): RepositoryParams
    {
        return new RepositoryParams($data ?? [], $options ?? []);
    }

    /**
     * Filter support methods all(), getList(),... by params
     *
     * @param $params
     *
     * @return BaseRepository
     */
    protected function filter($params)
    {
        return $this;
    }

    /**
     * Filter support methods update(),... by params
     *
     * @param $params
     *
     * @return BaseRepository
     */
    protected function mark($params)
    {
        return $this;
    }

    /**
     * Get number paginate
     *
     * @param $params
     *
     * @return int
     */
    protected function getPaginate($params): int
    {
        return (!empty($params->option('paginate')) ? $params->option('paginate') : 20);
    }
}
