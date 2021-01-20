<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\Page;
use App\Models\Version;
use App\Models\Contentable;
use App\Models\TextBlock;

use App\Events\ContentElementSaved;
use App\Events\ContentElementCreated;

class ContentElement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $with = ['content', 'contentables', 'contentables.version'];
    protected $appends = ['type'];
    protected $dates = ['publish_at'];

    public function saveContentElement(array $input, $id = null)
    {
        $contentable = self::findContentable($input);
        $uuid = null;
        $new_version = true;

        if ($id) {
            $content_element = ContentElement::findOrFail($id);
            $uuid = $content_element->uuid;

            $is_published = $content_element->contentables()->whereHas('version', function($query) {
                $query->whereNotNull('published_at');
            })->count();

            if (!$is_published) {
                $new_version = false;
            } else {

                $content_elements = ContentElement::where('uuid', $uuid)
                    ->whereHas('contentables', function($query) use($contentable) {
                        $query->where('contentable_id', $contentable->id)
                              ->where('contentable_type', get_class($contentable))
                                ->whereHas('version', function($query) {
                                    $query->whereNull('published_at');
                                });
                    })
                    ->get()
                    ->sortByDesc(function ($content_element) use($contentable) {
                        return $content_element->getPageVersion($contentable)->id;
                    });

                if ($content_elements->count()) {
                    $content_element = $content_elements->first();
                } else {
                    $content_element = new ContentElement;
                    $content_element->uuid = $uuid;
                }
            }
        } else {
            $content_element = new ContentElement;
            $content_element->uuid = Str::uuid();
        }

        $content_class = 'App\\Models\\'.Str::studly(Arr::get($input, 'type'));
        $content = (new $content_class)->saveContent(Arr::get($input, 'content'), $new_version ? null : Arr::get($input, 'content.id'), $new_version ? true : null);

        $content_element->content_id = $content->id;
        $content_element->content_type = get_class($content);

        //$content_element->version_id = $contentable->getDraftVersion()->id;
        $content_element->publish_at = Arr::get($input, 'publish_at');

        $content_element->save();

        // assign or update the content element to the contentable

        if ($uuid) {
            $pages = ContentElement::findPagesByUuid($uuid, $contentable);
        } else {
            $pages = collect([$contentable]);
        }

        foreach ($pages as $page) {

            if (!$page->contentElements()->get()->contains('id', $content_element->id)) {
                $page->contentElements()->attach($content_element, [
                    'version_id' => $page->getDraftVersion()->id,
                    'sort_order' => Arr::get($input, 'pivot.sort_order'),
                    'unlisted' => Arr::get($input, 'pivot.unlisted'),
                    'expandable' => Arr::get($input, 'pivot.expandable'),
                ]);
            } else {
                $page->contentElements()->updateExistingPivot($content_element, [
                    'version_id' => $page->getDraftVersion()->id,
                    'sort_order' => Arr::get($input, 'pivot.sort_order'),
                    'unlisted' => Arr::get($input, 'pivot.unlisted'),
                    'expandable' => Arr::get($input, 'pivot.expandable'),
                ]);
            }

            cache()->tags([cache_name($contentable)])->flush();
        }

        // refresh the content element so that it updates its content
        $content_element->refresh();
        cache()->tags([cache_name($content_element)])->flush();

        if ($new_version) {
            broadcast(new ContentElementCreated($content_element, $contentable))->toOthers();
        } else {
            broadcast(new ContentElementSaved($content_element))->toOthers();
        }

        return $content_element;
    }

    public static function findPagesByUuid($uuid, $add_page = null) {

        $pages = ContentElement::where('uuid', $uuid)
             ->get()
             ->map(function($ce) {
                return $ce->contentables->map->pageable;
             })
             ->flatten();

        if ($add_page) {
             $pages->push($add_page);
        }

        return $pages->unique(function($item) {
            return $item->type.$item->id;
        });
    }

    public static function findContentable($input)
    {
        if (Str::contains(Arr::get($input, 'pivot.contentable_type'), 'App\\Models\\')) {
            $class_name = Arr::get($input, 'pivot.contentable_type');
        } else {
            $class_name = 'App\\Models\\'.Str::studly(Arr::get($input, 'pivot.contentable_type'));
        }

        Validator::make($input, [
            'pivot.contentable_id' => ['required', function ($attribute, $value, $fail) use ($input, $class_name) {
                $id_check = resolve($class_name)->find($value);
                if (!$id_check) {
                    $fail('No related object found when saving the content element');
                }
            }],
            'pivot.contentable_type' => ['required', function ($attribute, $value, $fail) use ($input, $class_name) {
                $class = resolve($class_name);
                if (!$class) {
                    $fail('No related class found when saving the content element');
                }
            }],
        ])->validate();

        return (new $class_name)->findOrFail(Arr::get($input, 'pivot.contentable_id'));
    }

    public function contentables()
    {
        return $this->hasMany(Contentable::class);
    }

    public function pages()
    {
        return $this->morphedByMany(Page::class, 'contentable')->withPivot('sort_order', 'unlisted', 'expandable', 'version_id');
    }

    public function blogs()
    {
        return $this->morphedByMany(Blog::class, 'contentable')->withPivot('sort_order', 'unlisted', 'expandable', 'version_id');
    }

    public function content()
    {
        return $this->morphTo();
    }

    public function versions()
    {
        return $this->belongsToMany(Version::class, 'contentables');
    }

    public function getTypeAttribute()
    {
        return Str::kebab(class_basename($this->content));
    }

    public function getPreviousVersion($page, $version = null)
    {
        if (!$version) {
            $version = $page->getDraftVersion();
        }

        return ContentElement::where('uuid', $this->uuid)
            ->whereHas('contentables', function($query) use($page, $version) {
                $query->where('contentable_id', $page->id)
                    ->where('contentable_type', get_class($page))
                    ->where('version_id', '<', $version->id);
            })
            ->get()
            ->sortByDesc(function ($content_element) use($page) {
                return $content_element->contentables()
                    ->where('contentable_id', $page->id)
                    ->where('contentable_type', get_class($page))
                    ->first()
                    ->version->id;
            })->first();
    }

    public function isType($type)
    {
        return $this->type === $type;
    }

    public function getPageVersion($contentable) 
    {
        $contentables = $this->contentables()
                    ->where('contentable_id', $contentable->id)
                    ->where('contentable_type', get_class($contentable))
                    ->get();

        if ($contentables->count() === 1) {
            return $contentables->first()->version;
        } else {
            throw Exception('More than one contentable found');
        }
    }
}
