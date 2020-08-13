<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Page;
use App\Version;
use App\TextBlock;
use Carbon\Carbon;

class ContentElement extends Model
{
    use SoftDeletes;

    protected $with = ['content', 'version'];
    protected $appends = ['type', 'published_at'];
    protected $dates = ['publish_at'];

    public function saveContentElement($id = null, $input) 
    {
        $new_version = true;
        if ($id) {
            $content_element = ContentElement::findOrFail($id);
            $uuid = $content_element->uuid;
            if (!$content_element->published_at) {
                $new_version = false;
            } else {
                $content_element = new ContentElement;
                $content_element->uuid = $uuid;
            }
        } else {
            $content_element = new ContentElement;
            $content_element->uuid = Str::uuid();
        }

        $page = Page::findOrFail(Arr::get($input, 'pivot.page_id'));

        $content_class = 'App\\'.Str::studly(Arr::get($input, 'type'));
        $content = (new $content_class)->saveContent($new_version ? null : Arr::get($input, 'content.id'), Arr::get($input, 'content'));

        $content_element->content_id = $content->id;
        $content_element->content_type = get_class($content);

        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->publish_at = Arr::get($input, 'publish_at') ? Carbon::parse(Arr::get($input, 'publish_at')) : null;

        $content_element->save();

        // assign or update the content element to the page 
        if (!$content_element->pages()->get()->contains('id', $page->id)) {
            $content_element->pages()->attach($page->id, [
                'sort_order' => Arr::get($input, 'pivot.sort_order'),
                'unlisted' => Arr::get($input, 'pivot.unlisted'),
                'expandable' => Arr::get($input, 'pivot.expandable'),
            ]);
        } else {
            $content_element->pages()->updateExistingPivot($page->id, [
                'sort_order' => Arr::get($input, 'pivot.sort_order'),
                'unlisted' => Arr::get($input, 'pivot.unlisted'),
                'expandable' => Arr::get($input, 'pivot.expandable'),
            ]);
        }

        // refresh the content element so that it updates its content
        $content_element->refresh();
        cache()->tags([cache_name($content_element), cache_name($page)])->flush();
        return $content_element;
    }

    public function pages() 
    {
        return $this->belongsToMany(Page::class)->withPivot('sort_order', 'unlisted', 'expandable');
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
            ->sortByDesc(function($content_element) {
                return $content_element->version_id;
            })->first();
    }

    public function isType($type) 
    {
        return $this->type === $type;   
    }
}
