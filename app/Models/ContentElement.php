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
use App\Models\TextBlock;

use App\Events\ContentElementSaved;
use App\Events\ContentElementCreated;

class ContentElement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $with = ['content', 'version'];
    protected $appends = ['type', 'published_at'];
    protected $dates = ['publish_at'];

    public function saveContentElement(array $input, $id = null)
    {
        $contentable = self::findContentable($input);

        $new_version = true;
        if ($id) {
            $content_element = ContentElement::findOrFail($id);
            $uuid = $content_element->uuid;

            if (!$content_element->published_at || Arr::get($input, 'instance')) {
                $new_version = false;
            } else {
                $content_elements = ContentElement::where('uuid', $uuid)
                    ->get()
                    ->filter(function ($content_element) {
                        return $content_element->published_at ? false : true;
                    })->sortByDesc(function ($content_element) {
                        return $content_element->version->id;
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

        $content_element->version_id = $contentable->getDraftVersion()->id;
        $content_element->publish_at = Arr::get($input, 'publish_at');

        $content_element->save();

        // assign or update the content element to the contentable

        if (!$contentable->contentElements()->get()->contains('id', $content_element->id)) {
            $contentable->contentElements()->attach($content_element, [
                'sort_order' => Arr::get($input, 'pivot.sort_order'),
                'unlisted' => Arr::get($input, 'pivot.unlisted'),
                'expandable' => Arr::get($input, 'pivot.expandable'),
            ]);
        } else {
            $contentable->contentElements()->updateExistingPivot($content_element, [
                'sort_order' => Arr::get($input, 'pivot.sort_order'),
                'unlisted' => Arr::get($input, 'pivot.unlisted'),
                'expandable' => Arr::get($input, 'pivot.expandable'),
            ]);
        }

        // refresh the content element so that it updates its content
        $content_element->refresh();
        cache()->tags([cache_name($content_element), cache_name($contentable)])->flush();

        if ($new_version) {
            broadcast(new ContentElementCreated($content_element, $contentable))->toOthers();
        } else {
            broadcast(new ContentElementSaved($content_element))->toOthers();
        }

        return $content_element;
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

    public function pages()
    {
        return $this->morphedByMany(Page::class, 'contentable')->withPivot('sort_order', 'unlisted', 'expandable');
    }

    public function blogs()
    {
        return $this->morphedByMany(Blog::class, 'contentable')->withPivot('sort_order', 'unlisted', 'expandable');
    }

    public function content()
    {
        return $this->morphTo();
    }

    public function version()
    {
        return $this->belongsTo(Version::class);
    }

    public function getTypeAttribute()
    {
        return Str::kebab(class_basename($this->content));
    }

    public function getPublishedAtAttribute()
    {
        return $this->version->published_at;
    }

    public function getPreviousVersion()
    {
        return ContentElement::where('uuid', $this->uuid)
            ->where('version_id', '<', $this->version_id)
            ->get()
            ->sortByDesc(function ($content_element) {
                return $content_element->version_id;
            })->first();
    }

    public function isType($type)
    {
        return $this->type === $type;
    }
}
