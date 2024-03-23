# LongAoDai Repository Pattern

This package provides a base repository implementation following the repository design pattern in PHP.

## Installation

You can install this package via Composer. Run the following command in your terminal:
```bash
composer require longaodai/repository-pattern
```

In `config/app.php` add provider
```php
'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        \LongAoDai\Repositories\RepositoryServiceProvider::class,
    ])->toArray(),
```
Public config file `pattern.php`
```bash
php artisan vendor:publish --tag=longaodai-repository-pattern
```
_Result exec 🍀:_
```php
 Copying file [vendor/longaodai/repository-pattern/config/pattern.php] to [config/pattern.php] ............................................... DONE
```
😀  Now, you can the create repository by command instead of creating it manually ~.~
```bash
php artisan setup:repository Comment
```
_Result exec 🍀:_
```php
Repository Comment created successfully !!!
Implement: App\Repositories\CommentEloquentRepository.php
Interface: App\Repositories\CommentRepositoryInterface.php
```
After running the first repository creation command, in the `app/Providers` folder there will be auto create a `RepositoryServiceProvider.php` file. Pls add more into `config/app.php` for register custom provider.
```php
'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        \LongAoDai\Repositories\RepositoryServiceProvider::class,
        \App\Providers\RepositoryServiceProvider::class,
    ])->toArray(),
```

## Usage
To use the base repository in your project, you need to create repository classes that extend the BaseRepository class provided by this package.

Here's an example of how to create a repository class:
```php
use LongAoDai\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    /**
     * Specify the model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return User::class;
    }
}

public function getList($data = null, $options = null): mixed
{
    $this->method('select', [
        'name', 'email'
    ]);

    return parent::getList($data, $options);
}

protected function filter($params): UserRepository
{
    if ($params->get('name')) {
        $this->method('where', 'name', 'like', '%' . $params->get('name') . '%');
    }

    if ($params->get('id')) {
        $this->method('where', 'id', $params->get('id'));
    }

    return parent::filter($params);
}

protected function mark($params): UserRepository
{
    if ($params->option('id')) {
        $this->method('where', 'id', $params->option('id'));
    }

    return parent::mark($params);
}
```
You can then use methods provided by the BaseRepository class such as all(), find(), create(), update(), and delete() in your repository classes.

In controller file you can use
```php
public function index()
{
    $repository = app(UserRepository::class);
    // Get list with pagination
    $user = $repository->getList($data = null, $options = null);
    // Get all data
    $user = $repository->all($data = null, $options = null);
    // Find by id
    $user = $repository->find(['id' => 1]);
    // Update data
    $user = $repository->update(
        collect([
            'name' => 'Vo Chi Long'
        ]),
        collect(['id' => 1])
    );
    // Create data
    $user = $repository->create(collect([
        'name' => 'Vo Chi Long',
        'email' => 'vochilong.work@gmail.com',
        'password' => bcrypt('password'),
    ]));
    // Get first by params condition
    $user = $repository->first(collect([
        'name' => 'Long'
    ]));
    // Update or Create by options condition
    $user = $repository->updateOrCreate(collect([
        'name' => 'Vo Chi Long'
    ]), collect([
        'email' => 'vochilong.work@gmail.com'
    ]));
    // Delete data by condition
    $user = $repository->destroy(['id' => 10]);
    
    return json_encode($user ?? []);
}
```