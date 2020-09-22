<?php

namespace App\Utilities;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;

class Paginate
{
    public static function create($collection)
    {
        //$collection = self::sortCollection($collection);
        $collection->each(function ($object) {
            if (method_exists($object, 'appendAttributes')) {
                $object->appendAttributes();
            }
        });

        $page = request('page', 1);
        $requested_number = request('count', 10);
        session()->put('paginate_count', $requested_number);
        $items = new Paginator(
            $collection->forPage($page, $requested_number),
            $collection->count(),
            $requested_number,
            $page,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );
        return $items;
    }
}
