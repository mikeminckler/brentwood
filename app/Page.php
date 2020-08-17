<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\ContentElement;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\PageAccess;
use App\AppendAttributesTrait;
use App\Events\PagePublished;
use App\Events\PageSaved;
use Carbon\Carbon;

class Page extends Model
{
    use SoftDeletes;
    use AppendAttributesTrait;

    protected $dates = ['publish_at'];
    protected $with = ['pages'];
    //protected $with = ['pages', 'footerFgFileUpload', 'footerBgFileUpload'];

    public $append_attributes = [
        'editable',
        'full_slug', 
        'can_be_published', 
        'content_elements', 
        'preview_content_elements',
        'footer_fg_image',
        'footer_bg_image',
        'sub_menu',
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
        $page->publish_at = Arr::get($input, 'publish_at') ? Carbon::parse(Arr::get($input, 'publish_at')) : null;
        $page->save();

        $page->saveContentElements($input);

        cache()->tags([cache_name($page)])->flush();

        event(new PageSaved($page));

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
        if (!$this->slug) {
            $slug = Str::kebab($this->name);   
        } else {
            $slug = $this->slug;
        }

        if ($this->parent_page_id > 0) {
            $parent_page = Page::find($this->parent_page_id);
            
            if ($parent_page instanceof Page) {
                while ($parent_page->id > 1) {
                    $slug = Str::kebab($parent_page->name).'/'.$slug;
                    $parent_page = Page::find($parent_page->parent_page_id);
                }
            }
        }

        return $slug;
    }

    public static function findByFullSlug($slug) 
    {
        return Page::all()->filter(function($page) use($slug) {
            return $page->full_slug === $slug;
        })->last();
    }

    public function getSlugAttribute($value) 
    {
        if ($value) {
            return $value;
        }   

        return Str::kebab($this->name);
    }

    public function contentElements() 
    {
        return $this->belongsToMany(ContentElement::class)->withPivot('sort_order', 'unlisted', 'expandable');
    }

    public function saveContentElements($input) 
    {
        if (Arr::get($input, 'content_elements')) {
            foreach (Arr::get($input, 'content_elements') as $content_element) {
                $content_element = (new ContentElement)->saveContentElement(Arr::get($content_element, 'id'), $content_element);
            }
        }

        return $this;
    }

    public function versions() 
    {
        return $this->hasMany(Version::class);   
    }

    public function publishedVersion() 
    {
        return $this->belongsTo(Version::class, 'published_version_id');   
    }

    public function getPublishedAtAttribute() 
    {
        return optional($this->publishedVersion)->published_at;   
    }

    public function publish() 
    {

        $publish_at_content_elements = $this->contentElements()
                                            ->where('publish_at', '<', now())
                                            ->whereHas('version', function($query) {
                                                $query->whereNull('published_at');
                                            })
                                            ->get();

        $new_draft_content_elements = $this->contentElements()
                                        ->whereHas('version', function($query) {
                                            $query->whereNull('published_at');
                                        })
                                        ->get()
                                        ->filter(function($content_element) use ($publish_at_content_elements) {
                                            return !$publish_at_content_elements->contains('id', $content_element->id);
                                        });

        $draft_version = $this->getDraftVersion();
        $draft_version->publish();
        $this->published_version_id = $draft_version->id;
        $this->publish_at = null;
        $this->save();

        cache()->tags([cache_name($this), cache_name($draft_version)])->flush();

        if ($publish_at_content_elements->count()) {
            foreach ($new_draft_content_elements as $new_draft_content_element) {
                $new_draft_content_element->version_id = $this->getDraftVersion()->id;
                $new_draft_content_element->save();
            }
        }

        event(new PagePublished($this));

        return $this;
    }

    public static function publishScheduledContent() 
    {
        $pages = Version::whereNull('published_at')
            ->whereHas('page', function($query) {
                $query->where(function($query) {
                    $query->whereNotNull('publish_at')
                          ->where('publish_at', '<', now());
                })
                ->orWhereHas('contentElements', function($query) {
                    $query->whereNotNull('publish_at')
                          ->where('publish_at', '<', now());
                });
            })
            ->get()
            ->map(function($version) {
                return $version->page;
            })
            ->each(function($page) {
                $page->publish();
            });

    }

    public function getDraftVersion() 
    {
        $draft_version = $this->versions()->whereNull('published_at')->first();
        if ($draft_version) {
            return $draft_version;
        } else {
            return (new Version)->saveVersion(null, [
                'name' => $this->versions()->count() + 1,
                'page_id' => $this->id,
            ]);
        }
    }

    public function getDraftVersionIdAttribute() 
    {
        return $this->getDraftVersion()->id; 
    }

    public function getContentElements() 
    {
        $version_id = requestInput('version_id');
        
        if ($version_id > 0) {
            return $this->contentElements()
                ->where('version_id', '<=', $version_id)
                ->get();
        } else {
            return $this->contentElements()->get();
        }
    }

    public function getContentElementsAttribute() 
    {
        //return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-content-elements', function() {
            return $this->getContentElements()
                         ->groupBy('uuid')
                         ->map(function($uuid) {
                            return $uuid->sortByDesc( function( $content_element) {
                                return $content_element->version_id;
                            })->first();
                         })
                         ->sortBy(function($content_element) {
                            return $content_element->sort_order;
                         })->values();
        //});
    }

    public function getPublishedContentElementsAttribute() 
    {
        return $this->getContentElements()
                     ->groupBy('uuid')
                     ->map(function($uuid) {
                        return $uuid->filter( function($content_element) {
                            return $content_element->published_at ? true : false;
                        })
                        ->sortByDesc( function( $content_element) {
                            return $content_element->version_id;
                        })->first();
                     })
                     ->filter()
                     ->filter( function($content_element) {
                        return $content_element->pivot->unlisted ? false : true;
                     })
                     ->sortBy(function($content_element) {
                        return $content_element->pivot->sort_order;
                     })->values();
    }

    public function getPreviewContentElementsAttribute() 
    {
        if (!session()->get('editing')) {
            return collect();
        }
        return $this->getContentElements()
                     ->groupBy('uuid')
                     ->map(function($uuid) {
                        return $uuid->sortByDesc( function( $content_element) {
                            return $content_element->version_id;
                        })->first();
                     })
                     ->filter( function($content_element) {
                        return $content_element->pivot->unlisted ? false : true;
                     })
                     ->sortBy(function($content_element) {
                        return $content_element->pivot->sort_order;
                     })->values();
    }

    public function getCanBePublishedAttribute() 
    {

        if (!auth()->check()) {
            return false;
        }

        if (!auth()->user()->hasRole('publisher')) {
            return false;
        }

        if (!$this->published_version_id && $this->contentElements->count()) {
            return true;
        }

        return $this->content_elements->filter(function($content_element) {
                return $content_element->published_at ? false : true;
            })->count() ? true : false;
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

    public function createPageAccess($object)
    {
        $page_access = (new PageAccess)->savePageAccess($this, $object);
        return $this;
    }

    public function removePageAccess($object)
    {
        $page_access = (new PageAccess)->removePageAccess($this, $object);
        return $this;
    }

    public function pageAccesses()
    {
        return $this->hasMany(PageAccess::class);
    }

    public function getEditableAttribute() 
    {

        if (!auth()->check()) {
            return false;
        }

        if (!session()->has('editing')) {
            return false;
        }

        return auth()->user()->can('update', $this);
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

        $pages = $pages->reject(function($p) use($page){
            return $p->id === $page->id;
        });

        if ($page->parent_page_id === $parent_page->id) {
            $pages = $pages->push($page);
        }

        $pages = $pages->sortBy(function($p) {
            return $p->sort_order;
        })
        ->values()
        ->each( function ($p, $index) {
            $p->sort_order = $index + 1;
            $p->save();
        });
    }

}
