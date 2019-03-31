<h1> <img src="https://image.flaticon.com/icons/svg/1461/1461628.svg" width="50"/> Percolator </h1>

*An automatic bridge between you filter form and your model scopes*

Applying filter forms to database queries can get messy. Checking which fields have been selected, piecing together query string snippets; and just when you've got everything working along comes another handful of fields to filter on. The code can get really messy, really quickly. One solution I've seen is to have each filter correspond to a class containing the desired query string. While this works well, the solution I've tried to implement uses one of Laravel's existing features - scopes.

**Percolator** takes each form field name, finds the corresponding model scope and adds it to the query builder. And that's it. As long as the naming conventions are followed and the scope exists, the filters will be automatically applied when required.

<br/>

### Installation

```
composer require "briantweed/percolator"
```
<br>

### How to Use

Include the Percolator class

```php
use briantweed\Percolator\Percolator;
```


Initialise it by passing an instance of the model you're running the query on and the request. Call the `apply()` method to build the query, then use `get()` or `paginate()` to build the collection. 

```php
public function index(Request $request)
{
    $results = (new Percolator(new Model, $request))->apply()
        ->paginate();
}
```

<br>

### Naming Convention

The `config/percolator.php` file contains several keywords that are used to determine the scope method names.

If you want to make changes to any of the keywords you can publish the `config/percolator.php` file vai the provider:
```
php artisan vendor:publish --provider="briantweed\Percolator\PercolatorServiceProvider"
```
or via the tag:
```
php artisan vendor:publish --tag="percolator"
```

By default the `where_scope` value is `where` and should be used when naming any scope that you want **Percolator** to use. For example, for a form field with the name `rating` **Percolator** will look for a scope called `scopeWhereRating`.

Similarly, the `sort_scope` default value is `by` and **Percolator** will look for a scope called `scopeByRating`.

The `sort` value needs to be the same as the sorting form field name in order for  **Percolator** to recognise it as such. The same applies to the `order` value.

Finally the `related_table_separator` is used when filtering by fields from related models. The naming convention for this is the realted model name, followed by the `related_table_separator` and the related model field name. For example `location__name`.

<br>

---

### Example Usage

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
use briantweed\Percolator\Percolator;

class ModelController
{

    public function index(Request $request)
    {
        $results = (new Percolator(new Model, $request))->apply()->get();
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
