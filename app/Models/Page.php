<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\Models\ContentElement;
use App\Models\PageAccess;

use App\Traits\AppendAttributesTrait;
use App\Traits\VersioningTrait;
use App\Traits\HasContentElementsTrait;
use App\Traits\SlugTrait;
use App\Traits\TagsTrait;

use App\Events\PageSaved;

class Page extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AppendAttributesTrait;
    use VersioningTrait;
    use HasContentElementsTrait;
    use SlugTrait;
    use TagsTrait;

    protected $dates = ['publish_at'];

    protected $with = ['pages', 'footerFgPhoto', 'footerBgPhoto', 'tags'];

    protected $appends = ['type'];
    
    protected $casts = [
        'unlisted' => 'boolean',
    ];

    public $append_attributes = [
        'editable',
        'full_slug',
        'can_be_published',
        'content_elements',
        'preview_content_elements',
        'sub_menu',
        'full_type',
        'type',
        'resource',
        'published_at',
    ];

    public function savePage(array $input, $id = null)
    {
        $home_page = false;
        if ($id) {
            $page = Page::findOrFail($id);
            if ($page->slug === '/') {
                $home_page = true;
            }
        } else {
            $page = new Page;
        }

        $page->name = Arr::get($input, 'name');
        $page->title = Arr::get($input, 'title');
        if (!$home_page) {
            $page->slug = Arr::get($input, 'slug');
        }

        if (!$home_page) {
            $parent_page = Page::find(Arr::get($input, 'parent_page_id'));
            if ($parent_page instanceof Page) {
                $page->parent_page_id = $parent_page->id;
            } else {
                $page->parent_page_id = null;
            }
        }

        $page->sort_order = Arr::get($input, 'sort_order');
        $page->unlisted = Arr::get($input, 'unlisted') == true ? true : false;
        $page->footer_color = Arr::get($input, 'footer_color');

        if (Arr::get($input, 'footer_fg_photo')) {
            $footer_fg_photo = (new Photo)->savePhoto(Arr::get($input, 'footer_fg_photo'), $page->footer_fg_photo_id, $page);
            $page->footer_fg_photo_id = $footer_fg_photo->id;
        }

        if (Arr::get($input, 'footer_bg_photo')) {
            $footer_bg_photo = (new Photo)->savePhoto(Arr::get($input, 'footer_bg_photo'), $page->footer_bg_photo_id, $page);
            $page->footer_bg_photo_id = $footer_bg_photo->id;
        }

        $page->publish_at = Arr::get($input, 'publish_at');
        $page->save();

        $page->saveContentElements($input);
        $page->saveTags($input);

        cache()->tags([cache_name($page)])->flush();

        broadcast(new PageSaved($page))->toOthers();

        return $page;
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'content');
    }

    public function parentPage()
    {
        if ($this->parent_page_id > 0) {
            return $this->belongsTo(Page::class, 'parent_page_id');
        } else {
            return null;
        }
    }

    public function pages()
    {
        return $this->hasMany(Page::class, 'parent_page_id');
    }

    public function getFullSlugAttribute()
    {
        $slug = $this->getSlug();

        if ($this->parent_page_id > 0) {
            $parent_page = Page::find($this->parent_page_id);
            
            if ($parent_page instanceof Page) {
                while ($parent_page->id > 1) {
                    $slug = $parent_page->getSlug().'/'.$slug;
                    $parent_page = Page::find($parent_page->parent_page_id);
                }
            }
        }

        return $slug;
    }

    public static function publishScheduledContent()
    {
        $pages = Version::whereNull('published_at')
            ->whereHasMorph('versionable', ['App\Models\Page', 'App\Models\Blog'], function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('publish_at')
                          ->where('publish_at', '<', now());
                })
                ->orWhereHas('contentElements', function ($query) {
                    $query->whereNotNull('publish_at')
                          ->where('publish_at', '<', now());
                });
            })
            ->get()
            ->map(function ($version) {
                return $version->versionable;
            })
            ->each(function ($page) {
                $page->publish();
            });
    }

    public function footerFgPhoto()
    {
        return $this->belongsTo(Photo::class, 'footer_fg_photo_id');
    }

    public function footerBgPhoto()
    {
        return $this->belongsTo(Photo::class, 'footer_bg_photo_id');
    }

    public function getFooterFgPhoto() 
    {
        $value = $this->footerFgPhoto;   
        if ($value) {
            return $value;
        } else {
            if ($this->parent_page_id > 0) {
                return Page::find($this->parent_page_id)->getFooterFgPhoto();
            } else {
                return null;
            }
        }
    }

    public function getFooterBgPhoto() 
    {
        $value = $this->footerBgPhoto;   
        if ($value) {
            return $value;
        } else {
            if ($this->parent_page_id > 0) {
                return Page::find($this->parent_page_id)->getFooterBgPhoto();
            } else {
                return null;
            }
        }
    }

    public function getFooterColorAttribute($value)
    {
        if ($value) {
            return $value;
        } else {
            if ($this->parent_page_id > 0) {
                return Page::find($this->parent_page_id)->footer_color;
            } else {
                return null;
            }
        }
    }

    public function getFooterTextColorAttribute() 
    {
        if (!Str::contains($this->footer_color, ',')) {
            return null;
        }
        $number_total = round(collect(explode(',', $this->footer_color))->sum() / 3);
        if ($number_total < 75) {
            return 'text-gray-200';
        }
        return null;
    }

    public function getSubMenuAttribute()
    {
        if ($this->id !== 1) {
            return $this->pages;
        }
    }

    public function appendRecursive($attributes = null)
    {
        if (!$attributes) {
            $attribute = $this->append_attributes;
        }

        if (!is_array($attributes) && $attributes) {
            $attributes = [$attributes];
        }

        $this->appendAttributes($attributes);

        foreach ($this->pages as $page) {
            $page->appendRecursive($attributes);
        }
    }

    public function sortPages(Page $page, $input)
    {
        $old_parent_page = $page->parentPage;

        $page->sort_order = Arr::get($input, 'sort_order');
        $page->parent_page_id = Arr::get($input, 'parent_page_id');

        $parent_page = Page::findOrFail(Arr::get($input, 'parent_page_id'));

        $this->reorderPages($parent_page, $page);

        if ($old_parent_page->id != Arr::get($input, 'parent_page_id')) {
            $this->reorderPages($old_parent_page, $page);
        }
    }

    protected function reorderPages(Page $parent_page, Page $page)
    {
        $pages = $parent_page->pages;

        $pages = $pages->reject(function ($p) use ($page) {
            return $p->id === $page->id;
        });

        if ($page->parent_page_id === $parent_page->id) {
            $pages = $pages->push($page);
        }

        $pages = $pages->sortBy(function ($p) {
            return $p->sort_order;
        })
        ->values()
        ->each(function ($p, $index) {
            $p->sort_order = $index + 1;
            $p->save();
        });
    }
}
