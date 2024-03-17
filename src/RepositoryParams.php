<?php

namespace LongAoDai\Repositories;

use Illuminate\Support\Collection;

/**
 * Class RepositoryParams
 *
 * @package LongAoDai\Repositories
 */
class RepositoryParams
{
    protected $data;
    protected $options;

    /**
     * @param $data
     * @param $options
     */
    public function __construct($data = null, $options = null)
    {
        $this->data = $this->prepareParameter($data);
        $this->options = $this->prepareParameter($options);
    }

    /**
     * @param $param
     *
     * @return array
     */
    private function prepareParameter($param)
    {
        switch (true) {
            case $param instanceof Collection:
                $param = $param->toArray();
                break;
            case $param instanceof \stdClass:
                $param = (array)$param;
                break;
            case is_array($param):
                break;
            case empty($param):
                $param = [];
                break;
            default:
                $param = [];
        }

        return $param;
    }

    /**
     * Get data key in data
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key = null)
    {
        if (!empty($key)) {
            return $this->data[$key] ?? null;
        }

        return !empty($this->data) ? $this->data : null;
    }

    /**
     * Get data key in options
     *
     * @param $key
     *
     * @return mixed
     */
    public function option($key = null)
    {
        if (!empty($key)) {
            return $this->options[$key] ?? null;
        }

        return !empty($this->options) ? $this->options : null;
    }

    /**
     * Get all params
     *
     * @return array
     */
    public function all()
    {
        return [
            'data' => $this->get(),
            'options' => $this->option()
        ];
    }
}
