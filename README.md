# Laravel Guard
Simple and easy to use roles and permissions system *(ACL)* for Laravel 5.

Laravel Guard is package to easy controlling access to parts of your system. It provides simple tool to protect your routes and user methods to checking permissions.

## Installation

### Install via composer
Add dependency to your `composer.json` file and run `composer update`.

```json
to do
```

## Configuration

### Make new migration to store roles and permissions
Currently Guard stores all information about roles and permissions in database.

```php
Schema::create('roles', function(Blueprint $table)
{
    $table->increments('id');
    $table->string("tag", 20);
    $table->string("name", 100);
});

Schema::create('permissions', function(Blueprint $table)
{
    $table->increments('id');
    $table->string("tag", 50);
    $table->string("name", 50);
    $table->string("description");
});

Schema::create('permission_role', function(Blueprint $table)
{
    $table->increments('id');
    $table->unsignedInteger('role_id');
    $table->unsignedInteger('permission_id');

    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
});
```

Of course you should add new field to your *users* table to link user with *role*.

```php
Schema::update('users', function(Blueprint $table)
{
    $table->unsignedInteger('role_id');

    $table->foreign('role_id')->references('id')->on('roles');
});
```

### Configure your **User** model
Guard provides new *contract* - **Permissible**. It requires two methods:

- is($role)
- can($action)

Don't worry! Guard has trait which implements these methods. The only thing you have to do is use it and add new relationship to `roles`.

**User model**
```php
use Szykra\Guard\Contracts\Permissible;
use Szykra\Guard\Traits\Permissions;

class User extends Model implements Permissible, AuthenticatableContract, CanResetPasswordContract {

	use Permissions, Authenticatable, CanResetPassword;

    public function role()
    {
        return $this->belongsTo('Szykra\Guard\Models\Role');
    }

}
```

Guard provides two new models to your application - **Role** and **Permission**. Don't worry about them - they are needed to retrieve information from database.

### Add service provider to your config
Open your `config/app.php` file and add this line to `$providers` array

```php
'Szykra\Guard\GuardServiceProvider'
```

Now **Permissible** interface is binding to currently logged user. You can inject it everywhere you need by _IoC Container_ but remember - if you are not logged in then application throws _binding exception_. Always use this interface with `auth` middleware!

### Register new middleware
Open your `app/Http/Kernel.php` and add this line to `$middleware` array

```php
'Szykra\Guard\Middleware\ProtectRoutes',
```

If you don't want to protect all routes you can register this middleware as `$routeMiddleware` and use it only in specific routes.

```
'guard' => 'Szykra\Guard\Middleware\ProtectRoutes'
```

## Create roles and permissions
@todo

## Usage

### Route protection
To protect your route define key `needs` in route array

```php
/* String */
$router->get('/users', [
	'as' => 'users.index',
	'uses' => 'UsersController@index',
	'needs' => 'USERS.READ'
]);

/* As array */
$router->get('/users/{id}', [
	'as' => 'users.show',
	'uses' => 'UsersController@show',
	'needs' => ['USERS.READ']
]);
```

You can require more permissions for single route:
```php
/* String - separate by pipe */
$router->post('/users', [
	'as' => 'users.store',
	'uses' => 'UsersController@store',
	'needs' => 'USERS.READ|USERS.CREATE'
]);

/* As array */
$router->put('/users', [
	'as' => 'users.update',
	'uses' => 'UsersController@update',
	'needs' => ['USERS.READ', 'USERS.CREATE']
]);
```

If you are define Guard as `$routeMiddleware` you must add `middleware` action:

```php
$router->put('/users', [
	'as' => 'users.update',
	'uses' => 'UsersController@update',
	'needs' => ['USERS.READ', 'USERS.CREATE'],
	'middleware' => 'guard'
]);
```

Of course you can group your routes with required permissions:

```php
$router->group(['needs' => ['USERS.READ']], function() use ($router)
{
    // Needs USERS.READ permission
    $router->get('/users/{id}', [
        'as' => 'users.show',
        'uses' => 'UsersController@show',
    ]);

    // Needs USERS.READ and USERS.UPDATE permissions
    $router->put('/users/{id}', [
        'as' => 'users.update',
        'uses' => 'UsersController@update',
        'needs' => ['USERS.UPDATE']
    ]);
});
```

## Checking permissions
You have two new methods in user model to checking permissions.

- `$user->can($action)`
- `$user->is($role)`

To get user instance use Laravel `Auth` facade or inject instance of **Permissible** into your class.

### Inject to constuctor
```php
use Szykra\Guard\Contracts\Permissible;

class UsersController extends Controller {
    
    public function __construct(Permissible $user)
    {
        $this->user = $user;
    }

    public function update(Request $request, $id)
    {
        if( ! $this->user->can('USERS.UPDATE')) {
            // redirect, exception, flash message, etc.
        }

        // do something with user
    }

}
```

### Inject to action
```php
use Szykra\Guard\Contracts\Permissible;

class UsersController extends Controller {
    
    public function destroy(Permissible $user, $id)
    {
        if( ! $this->user->can('USERS.DELETE')) {
            // redirect, exception, flash message, etc.
        }

        // destroy user
    }

}
```

### Retrieve user by _Auth_ facade
You can check permissions whereever you have intance of current autheticated user, e.g. by `Auth::user()`.
It's very useful in views, when you have to render a part of view only for users with specific permissions.

```php
<section class="actions">
    <a href="{{ route('users.show', $user->id) }}">Show</a>

    @if(Auth::user()->can('USERS.EDIT'))
        | <a href="{{ route('users.edit', $user->id) }}">Edit</a>
    @endif
</section>
```