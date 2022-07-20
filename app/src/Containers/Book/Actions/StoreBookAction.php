<?php


namespace LargeLaravel\Containers\Book\Actions;


use Carbon\Carbon;
use LargeLaravel\Containers\Book\DTO\BookDTO;
use LargeLaravel\Containers\Book\Proxies\BookEloquentProxy;
use LargeLaravel\Containers\Book\Subactions\Interfaces\StoreBookActionInterface;
use LargeLaravel\Ship\Http\Requests\API\Interfaces\PaginateRequestInterface;

class StoreBookAction implements StoreBookActionInterface
{
    private $bookEloquentProxy;

    public function __construct(BookEloquentProxy $bookEloquentProxy)
    {
        $this->bookEloquentProxy = $bookEloquentProxy;
    }

    public function execute(StoreRequestInterface $storeRequestInterface): BookDTO
    {
        $bookDTO = new BookDTO($storeRequestInterface->all());
        $book = $this->bookEloquentProxy->create(
            $bookDTO
        );

        return $bookDTO;
    }
}
