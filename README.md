# Laravel Guard
![version](https://img.shields.io/packagist/v/szykra/laravel-guard.svg)
![codeclimate](https://img.shields.io/codeclimate/github/skrajewski/laravel-guard.svg)
![license](https://img.shields.io/packagist/l/szykra/laravel-guard.svg)

Simple and easy to use roles and permissions system *(ACL)* for Laravel 5.

**Laravel Guard** is package to easy controlling access to parts of your system. It provides simple tool to protect your routes and user methods to checking permissions.

## Installation

### Install via composer
Add dependency to your `composer.json` file and run `composer update`.

```json
"szykra/laravel-guard": "~0.1.0"
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

## Roles and permissions
Guard provides management for _Roles_ and _Permissions_ but what exactly does that mean?

In complex system we have a lot types of users, e.g. Administrators, Managers, Users or Moderators.
This types are called **roles**. Users can perform a lot of actions at the system but specific types of users should have
specified rights called **permissions**. When a user has a role, also has permissions that depend on his role.
We can check these permissions to prevent or allow specific actions.

### Permission naming convention
Guard does not defined how you should name your permissions. Try to keep it **simple**, **short**, **consistent** and **easy to remember**. I really like use a simple notation _**RESOURCE.ACTION**_, e.g. `USERS.READ`,  `USERS.UPDATE`. Feel free to use own naming convention, e.g. `read users`, `update user`. The choice is yours!

### Creating Roles and Permissions
You have a lot of possibilities to create _Roles_ or _Permissions_. You can manually insert data to database, create special _Seeder_ to prepare data or use artisan **Guard commands** to create _Role_ and _Permission_ entries on demand.

#### Create using Artisan CLI
Guard provides new _artisan_ commands:
- `guard:grant role permission`
- `guard:make:role tag [name]`
- `guard:make:permission tag [name]`

To create new role run below command:
```sh
php artisan guard:make:role ADMIN Administrator
```

Create new permission
```sh
php artisan guard:make:permission USERS.READ
```

To create permission and instantly link it with role use _--role_ option
```sh
php artisan guard:make:permission USERS.CREATE -r ADMIN
```

To link existing role with permission use `guard:grant` command
```sh
php artisan guard:grant ADMIN USERS.READ
```

#### Create using _Seeder_
If you have a lot of roles and permissions then seeder is a good choice, e.g.

```php
use Szykra\Guard\Models\Permission;
use Szykra\Guard\Models\Role;
use Illuminate\Database\Seeder;

class GuardTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'ADMIN'  => 'Administrator',
            'EDITOR' => 'Content Editor'
        ];

        $permissions = [
            ['tag' => 'POSTS.CREATE', 'name' => 'Create posts', 'description' => 'Ability to create new post'],
            ['tag' => 'POSTS.READ', 'name' => 'Read posts', 'description' => 'Ability to read posts data'],
            ['tag' => 'POSTS.UPDATE', 'name' => 'Update posts', 'description' => 'Ability to update posts data'],
            ['tag' => 'POSTS.DELETE', 'name' => 'Delete posts', 'description' => 'Ability to delete posts']
        ];

        $permModels = [];

        foreach ($permissions as $perm) {
            $permModels[$perm['tag']] = Permission::create($perm);
        }

        $rolesToPerm = [
            'ADMIN'  => ['POSTS.CREATE', 'POSTS.READ', 'POSTS.UPDATE', 'POSTS.DELETE'],
            'EDITOR' => ['POSTS.CREATE', 'POSTS.READ', 'POSTS.UPDATE']
        ];

        foreach ($rolesToPerm as $tag => $permissions) {
            $name = $roles[$tag];
            $role = Role::create(compact('tag', 'name'));

            foreach ($permissions as $perm) {
                $role->permissions()->save($permModels[$perm]);
            }
        }
    }
}
```

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
You can check permissions wherever you have instance of current authenticated user, e.g. by `Auth::user()`.
It's very useful in views, when you have to render a part of view only for users with specific permissions.

```php
<section class="actions">
    <a href="{{ route('users.show', $user->id) }}">Show</a>

    @if(Auth::user()->can('USERS.EDIT'))
        | <a href="{{ route('users.edit', $user->id) }}">Edit</a>
    @endif
</section>
```

### Checking permissions in _Form Request_
Laravel 5 Form Requests are very nice places to checking permissions. See below example.

```php
use Szykra\Guard\Contracts\Permissible;

class CreateUserRequest extends Request {

	public function authorize(Permissible $user)
	{
		return $user->can("USERS.CREATE");
	}

	public function rules()
	{
        return [
            // your validation rules
        ];
	}

}
```

## Reaction when user has not enough permissions
If user has not enough permissions then Guard thrown `InsufficientPermissionException`. You can catch it and return view, redirect or something else.

To catch this exception globally use your _ExceptionHandler_, e.g. `app/Exception/Handler.php`, method `render()`

```php
public function render($request, Exception $e)
{
    if($e instanceof InsufficientPermissionException) {
        Flash::warning("Insufficient permissions", "You don't have enough permission to access to this section.");

        return redirect()->route('home');
    }

	return parent::render($request, $e);
}
```

## License
The MIT License. Copyright &copy; 2015 Szymon Krajewski.
