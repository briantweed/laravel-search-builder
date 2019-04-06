<?php

namespace BrianTweed;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\{Builder, Model};


/**
 * Class Percolator.

 * @package App\percolator
 * @author briantweed
 * @version 1.0.4
 * @link config/percolator.php
 *
 */
class Percolator
{

    private $model;
    private $query;
    private $fields;
    private $sort;
    private $orderBy;

    private const RELATIONS_PATH = "Eloquent\\\\Relations";


    /**
     * Percolator constructor.
     *
     * @param Model $model
     * @param Request $request
     */
    public function __construct(Model $model, Request $request)
    {
        $this->model = $model;
        $this->query = $this->model->newQuery();

        $this->setSelectFields();
        $this->setFields($request->all());
        $this->setOrderBy();
        $this->setSort();
    }


    /**
     * Add fields, orderBy and sort direction.
     *
     * @since 1.0.0
     * @return Builder
     * @throws ReflectionException
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
        $this->orderBy = array_key_exists(config('builder.order_field'), $this->fields) ? $this->fields[config('builder.order_field')] : null;
    }


    /**
     * Set the sort direction.
     *
     * @since 1.0.0
     * @return void
     */
    private function setSort(): void
    {
        $this->sort = array_key_exists(config('builder.sort_field'), $this->fields) ? $this->fields[config('builder.sort_field')] : null;
    }



    private function setSelectFields()
    {
        $table = $this->model->getTable();
        $schemaBuilder = $this->model->getConnection()->getSchemaBuilder();
        $fields = $schemaBuilder->getColumnListing($table);
        $fields = array_map(function($field) use($table) {
            return $table . '.' . $field;
        }, $fields);

        $this->query->select($fields);
    }



    /**
     * Check if each field has a corresponding scope method.
     *
     * @since 1.0.0
     * @since 1.0.2 - check if field belongs to related model
     * @return void
     */
    private function addFieldsToQuery(): void
    {
        foreach($this->fields as $field => $value)
        {
            if (isset($value))
            {
                if ($this->isRelatedScope($field)) {
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
     * @since 1.0.3 - check if field belongs to related model
     * @return void
     * @throws ReflectionException
     */
    private function addOrderByToQuery(): void
    {
        if ($this->sort)
        {
            if ($this->isRelatedScope($this->sort)) {
                $this->addRelatedOrderBy();
            }
            else {
                $this->addModelOrderBy();
            }
        }
    }


    /**
     * Should we check a related model for the scope.
     *
     * @since 1.0.3
     * @param string $field
     * @return bool
     */
    private function isRelatedScope(string $field): bool
    {
        return strpos($field, config('builder.related_table_separator')) !== false;
    }


    /**
     * Create and return the scope method name.
     *
     * @since 1.0.4
     * @param string $keyword
     * @param string $field
     * @return string
     */
    private function createScopeMethod(string $keyword, string $field): string
    {
        return 'scope' . ucwords(config('builder.' . $keyword . '_scope')) . ucwords(Str::camel($field));
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
        $scopeMethod = $this->createScopeMethod('where', $field);
        if (method_exists($this->model, $scopeMethod))
        {
            $scopeName = config('builder.where_scope') . $field;
            $this->query->$scopeName($value);
        }
    }


    /**
     * Add orderBy scope from a related model.
     *
     * @TODO - refactor
     * @since 1.0.4
     * @return void
     * @throws ReflectionException
     */
    private function addRelatedOrderBy(): void
    {
        list($model, $scope) = explode(config('builder.related_table_separator'), $this->sort);

        $scopeMethod = $this->createScopeMethod('sort', $scope);
        if ($this->orderBy) {
            $this->query = $this->model->$model->$scopeMethod($this->query, $this->orderBy);
        }
        else {
            $this->query = $this->model->$model->$scopeMethod($this->query);
        }

        $reflectionClass = new ReflectionClass($this->model);
        $relations = array_values(array_filter($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC), function($relation) {
            return preg_match('/' . self::RELATIONS_PATH . '/', $relation->getReturnType());
        }));

        $key = array_search($model, array_column($relations, 'name'));
        if ( $key !== false )
        {
            $relatedModel = $relations[$key]->invoke($this->model);
            $this->query->join($relatedModel->getRelated()->getTable(), $this->model->getTable().'.'.$relatedModel->getForeignKeyName(), '=', $relatedModel->getRelated()->getTable().'.'.$relatedModel->getOwnerKeyName());
        }
    }


    /**
     * Add orderBy from this model.
     *
     * @since 1.0.4
     * @return void
     */
    private function addModelOrderBy(): void
    {
        $scopeMethod = $this->createScopeMethod('sort', $this->sort);
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
