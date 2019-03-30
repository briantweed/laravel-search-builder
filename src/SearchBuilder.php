<?php

namespace briantweed\LaravelSearchBuilder;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\{Builder, Model};


/**
 * Class SearchBuilder.

 * @package App\Builders
 * @author briantweed
 * @version 1.0.2
 * @link config/builder.php
 *
 */
class SearchBuilder
{

    private $model;
    private $query;
    private $fields;
    private $sort;
    private $orderBy;


    /**
     * SearchBuilder constructor.
     *
     * @param Model $model
     * @param Request $request
     */
    public function __construct(Model $model, Request $request)
    {
        $this->model = $model;
        $this->query = $this->model->newQuery();

        $this->setFields($request->all());
        $this->setOrderBy();
        $this->setSort();
    }


    /**
     * Add fields, orderBy and sort direction.
     *
     * @since 1.0.0
     * @return Builder
     */
    public function apply(): Builder
    {
        $this->addFieldsToQuery();
        $this->addOrderByToQuery();
        return $this->query;
    }


    /**
     * Set the filters.
     *
     * @since 1.0.0
     * @param array $fields
     * @return void
     */
    private function setFields(array $fields): void
    {
        $this->fields = $fields;
    }


    /**
     * Set the orderBy field.
     *
     * @since 1.0.0
     * @return void
     */
    private function setOrderBy(): void
    {
        $this->orderBy = array_key_exists(config('builder.order'), $this->fields) ? $this->fields[config('builder.order')] : null;
    }


    /**
     * Set the sort direction.
     *
     * @since 1.0.0
     * @return void
     */
    private function setSort(): void
    {
        $this->sort = array_key_exists(config('builder.sort'), $this->fields) ? $this->fields[config('builder.sort')] : null;
    }


    /**
     * Check if each field has a corresponding scope method.
     *
     * @since 1.0.0
     * @since 1.0.1 - check field name for double underscore (related table field)
     * @since 1.0.2 - related table separator added
     * @return void
     */
    private function addFieldsToQuery(): void
    {
        foreach($this->fields as $field => $value)
        {
            if (isset($value))
            {
                if (strpos($field, config('builder.related_table_separator')) !== false) {
                    $this->addRelatedScope($field, $value);
                }
                else {
                    $this->addModelScope($field, $value);
                }
            }
        }
    }


    /**
     * Check if the orderBy has a corresponding scope method.
     *
     * @since 1.0.0
     * @return void
     */
    private function addOrderByToQuery(): void
    {
        if ($this->sort)
        {
            $scopeMethod = 'scope' . ucwords(config('builder.sort_scope')) . ucwords($this->sort);
            if (method_exists($this->model, $scopeMethod))
            {
                $scopeName = config('builder.sort_scope') . $this->sort;
                if ($this->orderBy) {
                    $this->query->$scopeName($this->orderBy);
                }
                else {
                    $this->query->$scopeName();
                }
            }
        }
    }


    /**
     * Add scope from a related model.
     *
     * @since 1.0.1
     * @since 1.0.2 - related table separator added
     * @param string $field
     * @param string $value
     * @return void
     */
    private function addRelatedScope(string $field, string $value): void
    {
        list($model, $scope) = explode(config('builder.related_table_separator'), $field);
        $this->query->whereHas($model, function ($query) use($scope, $value) {
            $scopeName = ucwords(config('builder.where_scope')) . ucwords(Str::camel($scope));
            $query->$scopeName($value);
        });
    }


    /**
     * Add scope from this model.
     *
     * @since 1.0.1
     * @param $field
     * @param $value
     * @return void
     */
    private function addModelScope(string $field, string $value): void
    {
        $scopeMethod = 'scope' . ucwords(config('builder.where_scope')) . ucwords(Str::camel($field));
        if (method_exists($this->model, $scopeMethod)) {
            $scopeName = config('builder.where_scope') . $field;
            $this->query->$scopeName($value);
        }
    }

}
