<?php

namespace LargeLaravel\Containers\Book\UI\API\Controllers;

use LargeLaravel\Containers\Book\Subactions\Interfaces\GetBookListActionInterface;
use LargeLaravel\Containers\Book\UI\API\Resources\BookListResource;
use LargeLaravel\Ship\Abstracts\Controllers\Controller;
use LargeLaravel\Ship\Http\Requests\API\StoreBookRequest;

class StoreBookController extends Controller
{
    public function list(
        StoreBookRequest $request,
        StoreBookResourceInterface $storeBookResourceInterface,
        GetBookListActionInterface $storeBookAction
    )
    {
        $bookDTO = $storeBookAction->execute($request);

        return response()->json($storeBookResourceInterface->fromCollection($bookDTO));
    }
}
