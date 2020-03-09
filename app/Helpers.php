<?php

function requestInput($field = null)
{
    $input = collect(request()->all())->map(function ($input) {
        return decodeInput($input);
    })->all();

    if ($field) {
        return Illuminate\Support\Arr::get($input, $field);
    } else {
        return $input;
    }
}

function decodeInput($input)
{
    if (!is_array($input)) {
        if (is_array(json_decode($input, true))) {
            $decoded = json_decode($input, true);
            return decodeInput($decoded);
        } else {
            return $input;
        }
    } else {
        return collect($input)->map(function ($input) {
            if (is_array($input)) {
                return collect($input)->map(function ($input) {
                    return decodeInput($input);
                })->all();
            } else {
                return $input;
            }
        })->all();
    }
}

function cache_name($item)
{
    return Illuminate\Support\Str::kebab(class_basename($item)).'-'.$item->id;
}
