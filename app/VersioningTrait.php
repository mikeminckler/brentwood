<?php

namespace App;

use App\Events\PageSaved;
use App\Events\PageDraftCreated;

trait VersioningTrait
{

    public function versions() 
    {
        return $this->morphMany(Version::class, 'versionable');   
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

        broadcast(new PageSaved($this))->toOthers();

        return $this;
    }

    public function getDraftVersion() 
    {
        $draft_version = $this->versions()->whereNull('published_at')->first();
        if ($draft_version) {
            return $draft_version;
        } else {
            $version = (new Version)->saveVersion(null, [
                'name' => $this->versions()->count() + 1,
                'versionable_type' => get_class($this),
                'versionable_id' => $this->id,
            ]);

            broadcast(new PageDraftCreated($this->load('versions')));

            return $version;
        }
    }

    public function getDraftVersionIdAttribute() 
    {
        return $this->getDraftVersion()->id; 
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

}
