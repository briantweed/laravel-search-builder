<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Order By Field Name.
    |
    | The orderBy field name must match this value.
    |--------------------------------------------------------------------------
    */

    'order' => 'order',


    /*
    |-------------------------------------------------------------------------
    | Sort By Field Name.
    |
    | The sort direction field name must match this value.
    |--------------------------------------------------------------------------
    */

    'sort' => 'sort',


    /*
    |--------------------------------------------------------------------------
    | Where Scope Keyword.
    |
    | Each form field name is combined with this keyword to create
    | the scope method name for filtering. e.g. scopeWhereField.
    |--------------------------------------------------------------------------
    */

    'where_scope' => 'where',


    /*
    |--------------------------------------------------------------------------
    | Sort Scope Keyword.
    |
    | Each form field name is combined with this keyword to create
    | the scope method name for sorting the query. e.g. scopeByField.
    |--------------------------------------------------------------------------
    */

    'sort_scope' => 'by',


    /*
    |--------------------------------------------------------------------------
    | Related Table Separator.
    |
    | Used when filtering by form fields on related tables e.g. related__field.
    | I've noticed problems when using a single dot as the separator so
    | best to using something else.
    |--------------------------------------------------------------------------
    */

    'related_table_separator' => '__',


];
