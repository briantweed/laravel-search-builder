# laravel-search-builder

*Filter a model collection based on form fields using existing model scope methods*

---
```
composer require "briantweed/laravel-search-builder"
```

### How it works (work in progress)

**LaravelSearchBuilder** uses each field name from the submitted form request to create a scope method name. If it matches an existing scope model, that scope is added to the query builder.

The naming convention for the scopes can be set in the `config/builder.php` if you publish the file. By default, the keyword used by each scope is `Where`.

e.g. for a form field with the name `rating` **LaravelSearchBuilder** will look for a method called `scopeWhereRating()`.

You can also set the a field to sort the query by and the order. The field name for both sort and orderby are set in the `config/builder.php` file. The keyword for sorting scopes is `By`. The default values in the `config/builder.php` are `sort` and `order`. These need to be the name of the form fields.

e.g. if the sort field is value is `rating` **LaravelSearchBuilder** will look for a method called `scopeByRating()`.
