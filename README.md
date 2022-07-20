# Installation
## Prerequisite
Check if you have `redis` installed, by running command: `redis-cli`

Note: If you're using Windows then install `Redis` may be harder than MacOS and Linux. Then you can consider running with Docker (as described in next section)
## Install guide
Clone this project.

Run the following commands:
```
composer install
npm install
cp .env.example .env
php artisan key:generate
npm install -g laravel-echo-server
```

Then setup your database infor in `.env` to match yours

Now, migrate and seed the database:
```
php artisan migrate --seed
```

Next, config Laravel echo server by running:
```
laravel-echo-server init
```
Just choose `Yes`, and remember to choose `redis` and `http`

After that change `MIX_FRONTEND_PORT` in `.env` to 6001 (match `laravel-echo-server` port)
## Run the app
To run the app, run the following commands, each command in **a separate terminal**:
```
php artisan serve
npm run watch
laravel-echo-server start
php artisan queue:work
```

Now access your app at `localhost:8000`, register an account and try, open another browser tab with another account to test realtime chat.

# Running with docker
## Pre-install
Make sure you installed `docker` and `docker-compose`
## Guide
First create `.env` file
```
cp .env.example .env
```
Edit `.env` update the following parts:
```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laraveluser
DB_PASSWORD=laraveluserpass

...

REDIS_HOST=redis
REDIS_PASSWORD=redis_pass
REDIS_PORT=6379

...

LARAVEL_ECHO_SERVER_REDIS_HOST=redis
LARAVEL_ECHO_SERVER_REDIS_PORT=6379
LARAVEL_ECHO_SERVER_REDIS_PASSWORD=redis_pass
LARAVEL_ECHO_SERVER_AUTH_HOST=http://webserver:80
LARAVEL_ECHO_SERVER_DEBUG=false

...
```

Next, Run the following commands:
```
docker run --rm -v $(pwd):/app -w /app composer install --ignore-platform-reqs --no-autoloader --no-dev --no-interaction --no-progress --no-suggest --no-scripts --prefer-dist
docker run --rm -v $(pwd):/app -w /app composer update --ignore-platform-reqs --no-autoloader --no-dev --no-interaction --no-progress --no-suggest --no-scripts --prefer-dist
docker run --rm -v $(pwd):/app -w /app composer dump-autoload --classmap-authoritative --no-dev --optimize
docker run --rm -v $(pwd):/app -w /app node npm install --production
docker run --rm -v $(pwd):/app -w /app node npm run prod
```
The commands above are equivalent with: 
- **composer install <...other options>**
- **composer dump-autoload <...other options>**
- **npm install --production**
- **npm run prod**

## Bootstrap application

Run the following command to start application:
```
docker-compose up -d --build
```
Now we need to generate project's key migrate and seed database. Run command:
```
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

Now access the app at: `localhost:4000`

If you want to change to another port instead of `4000`. Change `APP_PORT` and `MIX_FRONTEND_PORT` to the same one you want. Then run following command to rebuild frontend:
```
docker run --rm -v $(pwd):/app -w /app node npm run prod
```

## Note
Every command with **Laravel** you need to run it like follow:
```
docker-compose exec app php artisan <same like normal>
```

Every command with **composer** need to run like follow:
```
docker run --rm -v $(pwd):/app -w /app composer <same like normal>
```

Every command with **npm** need to run like follow:
```
docker run --rm -v $(pwd):/app -w /app node npm run dev/watch/prod
```

## Deploy to production
When deploying to production, normally you'll run you app with HTTPS (port 443), then your frontend will be served under HTTPS too. So changing the `MIX_FRONTEND_PORT` in `.env` to 443.

Other settings are same

## Run containers as non-root user
If you're looking for running this app with Docker using non-root users (which is highly recommended in production), then checkout my `docker-non-root` branch

# Main Components & Ideas

In this part main components, ideas and design principles are explained in detail.

<a id="Design-Patterns-Used"></a>
## Design patterns used for the project
 
 In this project DTOs are used for transfering data between objects and thanks to PHP 7.4 typed properties we can construct DTOs without annotations. Thanks to the author of this [article](https://dev.to/zubairmohsin33/data-transfer-object-dto-in-laravel-with-php7-4-typed-properties-2hi9) and spatie for their [package](https://github.com/spatie/data-transfer-object).

 The following design patterns were used to build this project:
 - data transfer object
 - proxy
 - porto
 - decorator
 - iterator

<a id="Actions"></a>
## Actions

Actions are used to encapsulate business logic and must be used only from controllers.

<a id="Subactions"></a>
## Subactions

Subactions are used to extract business logic that needs to be reused in other containers. Well, initially it is [recommended](https://github.com/Mahmoudz/Porto#Tasks) to use tasks for that in porto pattern, but in most cases you don't need so much flexibility, also writing actions + tasks makes your process of writing code much slower.

<a id="Interaction-With-Database"></a>
## Interaction with database

Eloquent does not suit for large scale projects as it uses lots of magic under the hood.
In order to scale your codebase, you need to either wrap Eloquent in some abstraction, or replace it with something like Doctrine ORM. 
In the current project Eloquent is wrapped in a class called Proxy, e.g. BookEloquentProxy. 
Often developers call it Repository when wrapping Eloquent with something, but it is a mistake, as Repository assumes the following according to Edward Hieatt and Rob Mee:

*Mediates between the domain and data mapping layers using a collection-like interface for accessing domain objects.*

But developers often call their abstraction like UserEloquentRepository, but according to the definition above Repository shouldn't know anything about the way the data is stored.
So, it would be better better to call this abstraction Proxy. According to Wikipedia Proxy pattern does the following:

*In short, a proxy is a wrapper or agent object that is being called by the client to access the real serving object behind the scenes.*

<a id="Collections-Of-DTOs"></a>
## Collections of DTOs and typed collections

After we get data from some EloquentProxy, for example BookEloquentProxy, we need to convert this data to collection of DTOs:

    public function execute(PaginateRequestInterface $paginateRequest): BookCollection  
    {  
        $bookCollection = [];  
        $bookList = $this->bookEloquentProxy->findAll(  
            [],  
            $paginateRequest->getLimit(),  
            $paginateRequest->getOffset()  
        );  
        foreach ($bookList as $book) {  
            $bookCollection[] = new BookDTO($book);  
        }  
        return new BookCollection(...$bookCollection);  
    }

This approach is good for two reasons: we have a typed collection and we can refactor easily both every entity of a collection, and a collection itself. Also, we can typehint BookCollection when passing it as a param:

    public function fromCollection(BookCollection $bookCollection): array  
    {  
        $mappedCollection = [];  

        foreach ($bookCollection as $bookDTO) {  
            $mappedCollection[] = [  
                // any IDE will provide autocomplete here 
                // without any additional packages like IDE helper for Laravel
                'id' => $bookDTO->id,  
                'title' => $bookDTO->title,  
            ]; 
        }  
        return $this->wrapResponse($mappedCollection);  
    }

Also, you get really independent on Eloquent, as you don't use generic Eloquent collections, instead you use collections of DTOs and you can easily replace your data source with any other ORM, API etc. without breaking your code.

<a id="Entity-Relations"></a>
## Entity relations

Eloquent relations should not be used in a large project, as they make your code even more unmaintainable. Refactoring gets almost impossible with Eloquent relations, so instead put your related collection (has many/many to many relations) or DTO (has one) to your desired DTO:

    class BookDTO extends DataTransferObject
    {
      public int $id;
      
      // .... some other properties
      
      // these are comments related to BookDTO
      public CommentCollection $comments
    }



<a id="Decorators"></a>
## Decorators

Decorators are really great, as they allow you to extend an object's behaviour in a really OOP way. What would you do if you nedded to log the value of your action, e.g. list of books? Well, we often see this recommendation:

    public function execute(PaginateRequestInterface $paginateRequest): BookCollection  
    {  
       $bookCollection = [];  
       // some code here

       Log::info('get ' . count($bookCollection) . 'books');

       return new BookCollection(...$bookCollection);  
    }

But what did we do right now? We broke here open closed principle. Our code must be open for extension, but closed for modification. By inserting Log::info() to execute() method, we modified it, instead of extending. Also, our object now does more than one thing: it fectches books and logs the result.

How can we do it in a OOP way? Decorators to the rescue!
In a Laravel service container we decorate our action before binding it to our interface:

    public function register()  
    {  
      $bookListAction = new GetBookListAction(new BookEloquentProxy());  
      $bookListActionLogged = new GetBookListActionLogger($bookListAction);  
      
      $this->app->bind(GetBookListActionInterface::class, function ($app) use($bookListActionLogged) {  
        return $bookListActionLogged;  
      });
     }
As GetBookListActionLogger implements GetBookListActionInterface, it can be easily bound to this in a service container and in this case we extended GetBookListAction instead of modifying it. We can add as many decorators as we like and everything will work fine.

<a id="API-Resources"></a>
## API resources

API resources are used to transform API responses. Sometimes, you need to convert some field into another type and hide some fields. Every API resource must extend ApiResource and implement its own interface:

    <?php

    namespace LargeLaravel\Containers\Book\UI\API\Resources;

    use LargeLaravel\Containers\Book\Collections\BookCollection;
    use LargeLaravel\Containers\Book\UI\API\Resources\Interfaces\BookListResourceInterface;
    use LargeLaravel\Ship\Abstracts\Resources\ApiResource;


    class BookListResource extends ApiResource implements BookListResourceInterface
    {
      public function fromCollection(BookCollection $bookCollection): array
      {
        $mappedCollection = [];

        foreach ($bookCollection as $bookDTO) {
            $mappedCollection[] = [
                'id' => $bookDTO->id,
                'title' => $bookDTO->title,
            ];
        }

        return $this->wrapResponse($mappedCollection);
      }
    }

<a id="View-Composers"></a>
## View composers
//TODO

 <a id="Laravel-Artisan-Commands"></a>
# Laravel artisan commands

Some classes of Laravel in the project are moved to Ship folder and some artisan commands are run with additional options.

<a id="Seeding"></a>
## Seeding

To seed database run db:seed like this with option --class:

    php artisan db:seed --class '\LargeLaravel\Ship\Seeders\DatabaseSeeder'

Your custom seeders must be in the  Data folder of the proper container, e.g. Containers/User/Data/Seeders/UserSeeder.

<a id="Test Api"></a>
## Test Api

Go to link below:

    http://127.0.0.1:4000/api/book/

Your custom seeders must be in the  Data folder of the proper container, e.g. Containers/User/Data/Seeders/UserSeeder.

 <a id="Todo"></a>
# TODO

 - ~~move console, http kernels to Ship folder~~
 - ~~move RouteServiceProvider to Ship folder~~
 - make interface Filter which is implemented by every Where class
 - ~~write installation guide~~
 - write tests
 - ~~write documentation in readme with all design patterns and principles used~~
 - deploy to Heroku
<<<<<<< HEAD
 - add CI/CD
=======
 - add CI/CD
>>>>>>> first commit
