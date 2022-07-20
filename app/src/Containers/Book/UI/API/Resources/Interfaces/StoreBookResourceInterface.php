<?php

namespace LargeLaravel\Containers\Book\UI\API\Resources\Interfaces;

use LargeLaravel\Containers\Book\DTO\BookDTO;

interface StoreBookResourceInterface
{
    public function fromCollection(BookDTO $bookDTO): array;
}
