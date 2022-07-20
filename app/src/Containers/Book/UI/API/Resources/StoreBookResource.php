<?php

namespace LargeLaravel\Containers\Book\UI\API\Resources;

use LargeLaravel\Containers\Book\DTO\BookDTO;
use LargeLaravel\Containers\Book\UI\API\Resources\Interfaces\StoreBookResourceInterface;
use LargeLaravel\Ship\Abstracts\Resources\ApiResource;


class StoreBookResource extends ApiResource implements StoreBookResourceInterface
{
    public function fromCollection(BookDTO $bookDTO): array
    {
        return $this->wrapResponse($bookDTO);
    }
}
