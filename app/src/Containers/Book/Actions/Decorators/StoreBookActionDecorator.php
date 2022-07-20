<?php


namespace LargeLaravel\Containers\Book\Actions\Decorators;


use LargeLaravel\Containers\Book\DTO\BookDTO;
use LargeLaravel\Containers\Book\Subactions\Interfaces\StoreBookActionInterface;
use LargeLaravel\Ship\Http\Requests\API\Interfaces\StoreBookResourceInterface;

class StoreBookActionDecorator implements StoreBookActionInterface
{
    protected $storeBookActionInterface;

    public function __construct(StoreBookActionInterface $storeBookActionInterface)
    {
        $this->storeBookActionInterface = $storeBookActionInterface;
    }

    public function execute(StoreBookResourceInterface $storeBookRequest): BookDTO
    {
        return $this->storeBookActionInterface->execute($storeBookRequest);
    }
}
