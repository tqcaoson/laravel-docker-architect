<?php

namespace LargeLaravel\Containers\Book\Subactions\Interfaces;

use LargeLaravel\Containers\Book\DTO\BookDTO;
use LargeLaravel\Ship\Http\Requests\API\Interfaces\StoreRequestInterface;

interface StoreBookActionInterface
{
    public function execute(StoreRequestInterface $storeRequestInterface): BookDTO;
}
