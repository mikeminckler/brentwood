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

    protected $with = ['pages'];
    
    protected $casts = [
        'unlisted' => 'boolean',
    ];

    public $append_attributes = [
        'editable',
        'full_slug',
        'can_be_published',
        'content_elements',
        'preview_content_elements',
        'footer_fg_image',
        'footer_bg_image',
        'sub_menu',
        'type',
        'resource',
        'published_at',
    ];

    public function savePage($id = null, $input)
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
        $page->footer_fg_file_upload_id = Arr::get($input, 'footer_fg_file_upload.id');
        $page->footer_bg_file_upload_id = Arr::get($input, 'footer_bg_file_upload.id');
        $page->publish_at = Arr::get($input, 'publish_at');
        $page->save();

        $page->saveContentElements($input);

        cache()->tags([cache_name($page)])->flush();

        broadcast(new PageSaved($page))->toOthers();

        return $page;
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

    public function footerFgFileUpload()
    {
        return $this->belongsTo(FileUpload::class, 'footer_fg_file_upload_id');
    }

    public function footerBgFileUpload()
    {
        return $this->belongsTo(FileUpload::class, 'footer_bg_file_upload_id');
    }

    public function getFooterFgAttribute()
    {
        if ($this->footerFgFileUpload) {
            return $this->footerFgFileUpload;
        } else {
            if ($this->parent_page_id > 0) {
                return Page::find($this->parent_page_id)->footer_fg;
            } else {
                return null;
            }
        }
    }

    public function getFooterBgAttribute()
    {
        if ($this->footerBgFileUpload) {
            return $this->footerBgFileUpload;
        } else {
            if ($this->parent_page_id > 0) {
                return Page::find($this->parent_page_id)->footer_bg;
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

    public function getFooterFgImageAttribute()
    {
        return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-footer-fg-image', function () {
            if ($this->footer_fg) {
                if (Storage::disk('public')->exists('/photos/footers/fg-'.$this->footer_fg->name)) {
                    return '/photos/footers/fg-'.$this->footer_fg->name;
                } else {
                    return $this->createImage($this->footer_fg, 'fg');
                }
            }
        });
    }

    public function getFooterBgImageAttribute()
    {
        return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-footer-bg-image', function () {
            if ($this->footer_bg) {
                if (Storage::disk('public')->exists('/photos/footers/bg-'.$this->footer_bg->name)) {
                    return '/photos/footers/bg-'.$this->footer_bg->name;
                } else {
                    return $this->createImage($this->footer_bg, 'bg');
                }
            }
        });
    }

    public function createImage($file_upload, $prefix)
    {
        //\Log::info('CREATE '.$prefix.'-'.$file_upload->storage_filename);
        if (!Storage::exists($file_upload->storage_filename)) {
            return null;
        }
        $file = Storage::get($file_upload->storage_filename);
        $image = Image::make($file)
            ->resize(2000, 2000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        //})->encode('png');

        $file_name = '/photos/footers/'.$prefix.'-'.$file_upload->name;
        Storage::disk('public')->put($file_name, $image->stream());
        cache()->tags([cache_name($this)])->flush();
        return $file_name;
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
