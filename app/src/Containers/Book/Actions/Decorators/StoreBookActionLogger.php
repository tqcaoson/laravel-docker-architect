<?php


namespace LargeLaravel\Containers\Book\Actions\Decorators;


use LargeLaravel\Containers\Book\DTO\BookDTO;
use LargeLaravel\Ship\Http\Requests\API\Interfaces\StoreRequestInterface;

class StoreBookActionLogger extends StoreBookActionDecorator
{
    public function execute(StoreRequestInterface $storeBookRequest): BookDTO
    {
        $bookDTO = parent::execute($storeBookRequest);
        \Log::info("returned books");

        return $bookDTO;
    }
}
