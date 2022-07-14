<?php

namespace LargeLaravel\Containers\Book\DTO;

use Carbon\Carbon;
use LargeLaravel\Ship\Abstracts\DTO\DataTransferObject;

class BookDTO extends DataTransferObject
{
    public $id;
    public $title;
    public $original_title;
    public $author_id;
    public $description;
    public $image_guid;
    public $cover_type_id;
    public $num_of_pages;
    public $publish_date;
    public $publisher_id;
    public $ISBN;
    public $edition;
    public $created_at;
    public $updated_at;
}
