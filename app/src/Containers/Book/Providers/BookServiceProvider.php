<?php

namespace LargeLaravel\Containers\Book\Providers;

use Illuminate\Support\ServiceProvider;
use LargeLaravel\Containers\Book\Actions\Decorators\GetBookListActionLogger;
use LargeLaravel\Containers\Book\Actions\Decorators\BookStoreActionLogger;
use LargeLaravel\Containers\Book\Actions\GetBookListAction;
use LargeLaravel\Containers\Book\Actions\BookStoreAction;
use LargeLaravel\Containers\Book\Proxies\BookEloquentProxy;
use LargeLaravel\Containers\Book\Subactions\Interfaces\GetBookListActionInterface;
use LargeLaravel\Containers\Book\Subactions\Interfaces\BookStoreActionInterface;

class BookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $bookListAction = new GetBookListAction(new BookEloquentProxy());
        $bookListActionLogged = new GetBookListActionLogger($bookListAction);

        $this->app->bind(GetBookListActionInterface::class, function ($app) use($bookListActionLogged) {
            return $bookListActionLogged;
        });

        $bookStoreAction = new BookStoreAction(new BookEloquentProxy());
        $bookStoreActionLogged = new BookStoreActionLogger($bookStoreAction);

        $this->app->bind(BookStoreActionInterface::class, function ($app) use($bookStoreActionLogged) {
            return $bookStoreActionLogged;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
