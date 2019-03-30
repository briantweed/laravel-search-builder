# laravel-search-builder

*Automatically create and apply a search query to a collection built using model scope methods*

Filter forms can get messy. Checking which fields have selected, piecing together query string snippets; and just when you've got everything working along comes another handful of fields to filter on. The code can get really messy, really quickly. One solution I've seen is to have each filter correspond to a class containing the desired query string. While this works well, the solution I've tried to implement uses one of Laravel's existing features - scopes.

**LaravelSearchBuilder** takes each form field name, finds the corresponding model scope and adds it to the query builder.

<br/>

### Installation

```powershell
composer require "briantweed/laravel-search-builder"
```
<br>

Once installed you can publish the `config/builder.php` file using the command:
```powershell
php artisan vendor:publish --provider="briantweed\LaravelSearchBuilder\LaravelSearchBuilderServiceProvider"
```
or
```powershell
php artisan vendor:publish --tag="builder"
```

<br>

### How to Use

Initialise the SearchBuilder class by passing an instance of the model you're running the query on and the request:

```php
public function index(Request $request)
{
    $results = (new SearchBuilder(new Model, $request))->apply();
}
```


### How it works



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
