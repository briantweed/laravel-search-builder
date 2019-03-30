# laravel-search-builder

*Filter a model collection based on form fields using existing model scope methods*

---

### Installation

```
composer require "briantweed/laravel-search-builder"
```

Once installed you can publish the `config/builder.php` file using the command:
```
 php artisan vendor:publish --provider="briantweed\LaravelSearchBuilder\LaravelSearchBuilderServiceProvider"
```
or
```
 php artisan vendor:publish --tag="builder"
```


### How to use

Include `briantweed/laravel-search-builder` in you controller. Initialise by including an instance of the model you're running the query on and the request:

```php
  $results = (new SearchBuilder(new Model, $request))->apply()
```


### How it works

**LaravelSearchBuilder** uses each form field name to create a scope method name. If it matches a scope name, its added to the query builder. I have seen other solutions where each filter name corresponds to its own individual class. I decided to use scopes because once created they are available to use in other circumstances and not solely for use with package.

The naming convention for the scopes can be set in the `config/builder.php` if you publish the file. By default, the keyword used by each scope is `Where`.

e.g. for a form field with the name `rating` **LaravelSearchBuilder** will look for a method called `scopeWhereRating()`.

You can also set the a field to sort the query by and the order. The field name for both sort and orderby are set in the `config/builder.php` file. The keyword for sorting scopes is `By`. The default values in the `config/builder.php` are `sort` and `order`. These need to be the name of the form fields.

e.g. if the sort field is value is `rating` **LaravelSearchBuilder** will look for a method called `scopeByRating()`.


If you want to filter by a field on a realted model you can by listing the form field name as `model__field`. The related model separator can be changed from `__` in the `config/builder.php` file. The scope should be added to the related model class.

Note: if using the id field of a related model add the model name to the scope query e.g. `$query->where('related.id', '=', $value);`


---

*Form*
```html 
<form>
  <input type="text" name="location" value="" />
  <input type="text" name="rating" value="" />
  <select name="sort">
    <option value="location">Location</option>
    <option value="rating">Rating</option>
  </select>
  <input type="submit" value="submit" />
</form>
```

*Controller*
```php 
  use App/Model;
  use briantweed/laravel-search-builder;
  
  class ModelController
  {
  
    public function index(Request $request)
    {
      $results = (new SearchBuilder(new Model, $request))->apply()
      return view('index', [
        'results' => $results
      ]);
    }
    
  }
```

*Model*
```php

  class Model
  {
    public function scopeWhereLocation($query, $value)
    {
      return $query->where('location', '=', $value);
    }
    
    public function scopeWhereRating($query, $value)
    {
      return $query->where('rating', '>=', $value);
    }
    
    public function scopeByLocation($query, $direction = 'asc')
    {
    		return $query->orderBy('location', $direction);
    }
    
    public function scopeByRating($query, $direction = 'desc')
    {
    		return $query->orderBy('rating', $direction);
    }
  }
```
