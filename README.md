Fervent
======


Self-validating smart models for Laravel Framework 4's Eloquent ORM.

Based on the Ardent by Max Ehsan and Igor Santos

## Installation

Add `fervent` as a requirement to `composer.json` (see our latest stable version on the badges!):

```javascript
{
    "require": {
        "fervent": "dev-master"
    }
}
```

Update your packages with `composer update` or install with `composer install`.

### Usage outside of Laravel

If you're willing to use Fervent as a standalone ORM package you're invited to do so by using the
following configuration line in your project's boot/startup file (changing the properties according
to your database, obviously):

```php
\Fervent\Fervent::configureAsExternal(array(
  'driver'    => 'mysql',
  'host'      => 'localhost',
  'port'      => 3306,
  'database'  => 'my_system',
  'username'  => 'myself',
  'password'  => 'h4ckr',
  'charset'   => 'utf8',
  'collation' => 'utf8_unicode_ci'
));
```

------------------------------------------------------------------------------------------------------------

## Documentation

* [Introduction](#introduction)
* [Getting Started](#getting-started)
* [Effortless Validation with Fervent](#effortless-validation-with-fervent)
* [Retrieving Validation Errors](#retrieving-validation-errors)
* [Overriding Validation](#overriding-validation)
* [Custom Validation Error Messages](#custom-validation-error-messages)
* [Custom Validation Rules](#custom-validation-rules)
* [Model hooks](#model-hooks)
* [Cleaner definition of relationships](#cleaner-definition-of-relationships)
* [Automatically Hydrate Fervent Entities](#automatically-hydrate-fervent-entities)
* [Automatically Purge Redundant Form Data](#automatically-purge-redundant-form-data)
* [Automatically Transform Secure-Text Attributes](#automatically-transform-secure-text-attributes)
* [Updates with Unique Rules](#updates-with-unique-rules)


## Introduction

How often do you find yourself re-creating the same boilerplate code in the applications you build? Does this typical form processing code look all too familiar to you?

```php
Route::post('register', function() {
        $rules = array(
            'name'                  => 'required|min:3|max:80|alpha_dash',
            'email'                 => 'required|between:3,64|email|unique:users',
            'password'              => 'required|alpha_num|between:4,8|confirmed',
            'password_confirmation' => 'required|alpha_num|between:4,8'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes()) {
            User::create(array(
                    'name'     => Input::get('name'),
                    'email'    => Input::get('email'),
                    'password' => Hash::make(Input::get('password'))
                ));

            return Redirect::to('/')->with('message', 'Thanks for registering!');
        } else {
            return Redirect::to('/')->withErrors($validator->getMessages());
        }
    }
);
```

Implementing this yourself often results in a lot of repeated boilerplate code. As an added bonus, you controllers (or route handlers) get prematurely fat, and your code becomes messy, ugly and difficult to understand.

What if someone else did all the heavy-lifting for you? What if, instead of regurgitating the above mess, all you needed to type was these few lines?...

```php
Route::post('register', function() {
        $user = new User;
        if ($user->save()) {
            return Redirect::to('/')->with('message', 'Thanks for registering!');
        } else {
            return Redirect::to('/')->withErrors($user->errors());
        }
    }
);
```

**Enter Fervent!** 

**Fervent** - the magic-dust-powered, wrist-friendly, one-stop solution to all your dreary input sanitization boilerplates!

Puns aside, input validation functionality can quickly become tedious to write and maintain. Fervent deals away with these complexities by providing helpers for automating many repetitive tasks.

Fervent is not just great for input validation, though - it will help you significantly reduce your Eloquent data model code. Fervent is particularly useful if you find yourself wearily writing very similar code time and again in multiple individual applications.

For example, user registration or blog post submission is a common coding requirement that you might want to implement in one application and reuse again in other applications. With Fervent, you can write your *self-aware, smart* models just once, then re-use them (with no or very little modification) in other projects. Once you get used to this way of doing things, you'll honestly wonder how you ever coped without Fervent.

**No more repetitive brain strain injury for you!**


## Getting Started

`Fervent` aims to extend the `Eloquent` base class without changing its core functionality. Since `Fervent` itself is a descendant of `Illuminate\Database\Eloquent\Model`, all your `Fervent` models are fully compatible with `Eloquent` and can harness the full power of Laravels awesome OR/M.

To create a new Fervent model, simply make your model class derive from the `Fervent` base class. In the next examples we will use the complete namespaced class to make examples cleaner, but you're encouraged to make use of `use` in all your classes:

```php
use Fervent\Fervent;

class User extends Fervent {}
```

However, if you are not using the vanilla `Eloquent` base class, you can also add `Fervent` capabilities to any `Eloquent` descendant or service provider by including `FerventTrait`:

```php
use Fervent\FerventTrait;

class User extends Eloquent {
    use FerventTrait;
}
```

> **Note:** You can freely *co-mingle* your plain-vanilla Eloquent models with Fervent descendants. If a model object doesn't rely upon user submitted content and therefore doesn't require validation - you may leave the Eloquent model class as it is.


## Effortless Validation with Fervent

Fervent models use Laravel's built-in [Validator class](http://laravel.com/docs/validation). Defining validation rules for a model is simple and is typically done in your model class as a static variable:

```php
class User extends \Fervent\Fervent {
  public static $rules = array(
    'name'                  => 'required|between:4,16',
    'email'                 => 'required|email',
    'password'              => 'required|alpha_num|between:4,8|confirmed',
    'password_confirmation' => 'required|alpha_num|between:4,8',
  );
}
```

> **Note**: you're free to use the [array syntax](http://laravel.com/docs/validation#basic-usage) for validation rules as well.

Fervent models validate themselves automatically when `Fervent->save()` is called.

```php
$user           = new User;
$user->name     = 'John doe';
$user->email    = 'john@doe.com';
$user->password = 'test';

$success = $user->save(); // returns false if model is invalid
```

> **Note:** You can also validate a model at any time using the `Fervent->validate()` method.


## Retrieving Validation Errors

When an Fervent model fails to validate, a `Illuminate\Support\MessageBag` object is attached to the Fervent object which contains validation failure messages.

Retrieve the validation errors message collection instance with `Fervent->errors()` method or `Fervent->validationErrors` property.

Retrieve all validation errors with `Fervent->errors()->all()`. Retrieve errors for a *specific* attribute using `Fervent->validationErrors->get('attribute')`.

> **Note:** Fervent leverages Laravel's MessagesBag object which has a [simple and elegant method](http://laravel.com/docs/validation#working-with-error-messages) of formatting errors.


## Overriding Validation

There are two ways to override Fervent's validation:

#### 1. Forced Save
`forceSave()` validates the model but saves regardless of whether or not there are validation errors.

#### 2. Override Rules and Messages
both `Fervent->save($rules, $customMessages)` and `Fervent->validate($rules, $customMessages)` take two parameters:

- `$rules` is an array of Validator rules of the same form as `Fervent::$rules`.
- The same is true of the `$customMessages` parameter (same as `Fervent::$customMessages`)

An array that is **not empty** will override the rules or custom error messages specified by the class for that instance of the method only.

> **Note:** the default value for `$rules` and `$customMessages` is empty `array()`; thus, if you pass an `array()` nothing will be overriden.


## Custom Validation Error Messages

Just like the Laravel Validator, Fervent lets you set custom error messages using the [same syntax](http://laravel.com/docs/validation#custom-error-messages).

```php
class User extends \Fervent\Fervent {
  public static $customMessages = array(
    'required' => 'The :attribute field is required.',
    ...
  );
}
```


## Custom Validation Rules

You can create custom validation rules the [same way](http://laravel.com/docs/validation#custom-validation-rules) you would for the Laravel Validator.


## Model Hooks 

Fervent provides some syntatic sugar over Eloquent's model events: traditional model hooks. They are an easy way to hook up additional operations to different moments in your model life. They can be used to do additional clean-up work before deleting an entry, doing automatic fixes after validation occurs or updating related models after an update happens.

All `before` hooks, when returning `false` (specifically boolean, not simply "falsy" values) will halt the operation. So, for example, if you want to stop saving if something goes wrong in a `beforeSave` method, just `return false` and the save will not happen - and obviously `afterSave` won't be called as well.

Here's the complete list of available hooks:

- `before`/`afterCreate()`
- `before`/`afterSave()`
- `before`/`afterUpdate()`
- `before`/`afterDelete()`
- `before`/`afterValidate()` - when returning false will halt validation, thus making `save()` operations fail as well since the validation was a failure.

For example, you may use `beforeSave` to hash a users password:

```php
class User extends \Fervent\Fervent {
  public function beforeSave() {
    // if there's a new password, hash it
    if($this->isDirty('password')) {
      $this->password = Hash::make($this->password);
    }
    
    return true;
    //or don't return nothing, since only a boolean false will halt the operation
  }
}
```

### Additionals beforeSave and afterSave (since 1.0)

`beforeSave` and `afterSave` can be included at run-time. Simply pass in closures with the model as argument to the `save()` (or `forceSave()`) method.

```php
$user->save(array(), array(), array(),
  function ($model) { // closure for beforeSave
    echo "saving the model object...";
    return true;
  },
  function ($model) { // closure for afterSave
    echo "done!";
  }
);
```

> **Note:** the closures should have one parameter as it will be passed a reference to the model being saved.


## Cleaner definition of relationships

Have you ever written an Eloquent model with a bunch of relations, just to notice how cluttered your class is, with all those one-liners that have almost the same content as the method name itself?

In Fervent you can cleanly define your relationships in an array with their information, and they will work just like if you had defined them in methods. Here's an example:

```php
class User extends \Fervent\Fervent {
  public static $relationsData = array(
    'address' => array('hasOne', 'Address'),
    'orders'  => array('hasMany', 'Order'),
    'groups'  => array('belongsToMany', 'Group', 'table' => 'groups_have_users')
  );
}

$user = User::find($id);
echo "{$user->address->street}, {$user->address->city} - {$user->address->state}";
```

The array syntax is as follows:

- First indexed value: relation name, being one of
[`hasOne`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_hasOne),
[`hasMany`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_hasMany),
[`hasManyThrough`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_hasManyThrough),
[`belongsTo`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_belongsTo),
[`belongsToMany`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_belongsToMany),
[`morphTo`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_morphTo),
[`morphOne`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_morphOne),
[`morphMany`](http://laravel.com/api/4.2/Illuminate/Database/Eloquent/Model.html#method_morphMany).
- Second indexed: class name, with complete namespace. The exception is `morphTo` relations, that take no additional argument.
- named arguments, following the ones defined for the original Eloquent methods:
    - `foreignKey` [optional], valid for `hasOne`, `hasMany`, `belongsTo` and `belongsToMany`
    - `firstKey`, `secondKey` [optional], valid for `hasManyThrough`
    - `table`,`otherKey` [optional],`timestamps` [boolean, optional], and `pivotKeys` [array, optional], valid for `belongsToMany`
    - `name`, `type` and `id`, used by `morphTo`, `morphOne` and `morphMany` (the last two requires `name` to be defined)
    
> **Note:** This feature was based on the easy [relations on Yii 1.1 ActiveRecord](http://www.yiiframework.com/doc/guide/1.1/en/database.arr#declaring-relationship).


## Automatically Hydrate Fervent Entities

Fervent is capable of hydrating your entity model class from the form input submission automatically! 

Let's see it action. Consider this snippet of code:

```php
$user           = new User;
$user->name     = Input::get('name');
$user->email    = Input::get('email');
$user->password = Hash::make(Input::get('password'));
$user->save();
```

Let's invoke the *magick* of Fervent and rewrite the previous snippet:

```php
$user = new User;
$user->save();
```

That's it! All we've done is remove the boring stuff.

Believe it or not, the code above performs essentially the same task as its older, albeit rather verbose sibling. Fervent populates the model object with attributes from user submitted form data. No more hair-pulling trying to find out which Eloquent property you've forgotten to populate. Let Fervent take care of the boring stuff, while you get on with the fun stuffs!  
It follows the same [mass assignment rules](http://laravel.com/docs/eloquent#mass-assignment) internally, depending on the `$fillable`/`$guarded` properties.

To enable the auto-hydration feature, simply set the `$autoHydrateEntityFromInput` instance variable to `true` in your model class. However, to prevent filling pre-existent properties, if you want auto-hydration also for update scenarios, you should use instead `$forceEntityHydrationFromInput`:

```php
class User extends \Fervent\Fervent {
  public $autoHydrateEntityFromInput = true;    // hydrates on new entries' validation
  public $forceEntityHydrationFromInput = true; // hydrates whenever validation is called
}
```


## Automatically Purge Redundant Form Data

Fervent models can *auto-magically* purge redundant input data (such as *password confirmation*, hidden CSRF `_token` or custom HTTP `_method` fields) - so that the extra data is never saved to database. Fervent will use the confirmation fields to validate form input, then prudently discard these attributes before saving the model instance to database!

To enable this feature, simply set the `$autoPurgeRedundantAttributes` instance variable to `true` in your model class:

```php
class User extends \Fervent\Fervent {
  public $autoPurgeRedundantAttributes = true;
}
```

You can also purge additional fields. The attribute `Fervent::$purgeFilters` is an array of closures to which you can add your custom rules. Those closures receive the attribute key as argument and should return `false` for attributes that should be purged. Like this:

```php
function __construct($attributes = array()) {
  parent::__construct($attributes);

  $this->purgeFilters[] = function($key) {
    $purge = array('tempData', 'myAttribute');
    return ! in_array($key, $purge);
  };
}
```


## Automatically Transform Secure-Text Attributes

Suppose you have an attribute named `password` in your model class, but don't want to store the plain-text version in the database. The pragmatic thing to do would be to store the hash of the original content. Worry not, Fervent is fully capable of transmogrifying any number of secure fields automatically for you!

To do that, add the attribute name to the `Fervent::$passwordAttributes` static array variable in your model class, and set the `$autoHashPasswordAttributes` instance variable to `true`:

```php
class User extends \Fervent\Fervent {
  public static $passwordAttributes  = array('password');
  public $autoHashPasswordAttributes = true;
}
```

Fervent will automatically replace the plain-text password attribute with secure hash checksum and save it to database. It uses the Laravel `Hash::make()` method internally to generate hash.


## Updates with Unique Rules

Fervent can assist you with unique updates. According to the Laravel Documentation, when you update (and therefore validate) a field with a unique rule, you have to pass in the unique ID of the record you are updating. Without passing this ID, validation will fail because Laravel's Validator will think this record is a duplicate.

From the Laravel Documentation:

```php
    'email' => 'unique:users,email,10'
```

In the past, programmers had to manually manage the passing of the ID and changing of the ruleset to include the ID at runtime. Not so with Fervent. Simply set up your rules with `unique`, call function `updateUniques` and Fervent will take care of the rest.

#### Example:

In your extended model define your rules

```php
  public static $rules = array(
     'email' => 'required|email|unique',
     'password' => 'required|between:4,20|confirmed',
     'password_confirmation' => 'between:4,20',
  );
```

In your controller, when you need to update, simply call

```php
$model->updateUniques();
```

If required, you can runtime pass rules to `updateUniques`, otherwise it will use the static rules provided by your model.

Note that in the above example of the rules, we did not tell the Validator which table or even which field to use as it is described in the Laravel Documentation (ie `unique:users,email,10`). Fervent is clever enough to figure it out. (Thank you to github user @Sylph)

