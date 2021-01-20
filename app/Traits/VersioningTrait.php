<?php

namespace App\Traits;

use App\Models\Version;
use App\Models\ContentElement;

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

    public function publishContentElement(ContentElement $content_element) 
    {
        return $this->publish($content_element);
    }

    public function publish(ContentElement $publish_now_content_element = null)
    {

        if ($publish_now_content_element) {
            $publish_at_content_elements = collect([$publish_now_content_element]);
        } else {
            $publish_at_content_elements = $this->contentElements()
                                                ->where('publish_at', '<', now())
                                                ->get()
                                                ->filter( function($content_element) {
                                                    return Version::find($content_element->pivot->version_id)->published_at ? false : true;
                                                });
        }

        $new_draft_content_elements = $this->contentElements()
                                        ->get()
                                        ->filter( function($content_element) {
                                            return Version::find($content_element->pivot->version_id)->published_at ? false : true;
                                        })
                                        ->filter(function ($content_element) use ($publish_at_content_elements) {
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
                $contentable = $new_draft_content_element->contentables()
                                                          ->where('contentable_id', $this->id)
                                                          ->where('contentable_type', get_class($this))
                                                          ->first();
                $contentable->version_id = $this->getDraftVersion()->id;
                $contentable->save();
            }
        }

        $event_class = '\\App\\Events\\'.class_basename($this).'Saved';

        broadcast(new $event_class($this))->toOthers();

        // find other instances of the content elements that were just published and update them as well
        if ($publish_at_content_elements->count()) {
            $search_content_elements = $publish_at_content_elements;
        } else {
            $search_content_elements = $this->contentElements;
        }

        $uuids = $search_content_elements->map(function($ce) {
            return $ce->uuid;
        })
        ->flatten()
        ->unique();

        $published_content_elements = collect();

        foreach ($uuids as $uuid) {
            $pages = ContentElement::findPagesByUuid($uuid)->filter(function($page) {
                return $page->id !== $this->id || get_class($page) !== get_class($this);
            });

            if ($pages->count()) {
                foreach ($pages as $page) {
                    $content_element = $page->content_elements->filter(function($ce) use ($uuid) {
                        return $ce->uuid === $uuid;
                    })->first();

                    if (!$published_content_elements->contains('id', $content_element)) {

                        if (!$content_element->getPageVersion($page)->published_at) {
                            $page->publishContentElement($content_element);
                        }

                        $published_content_elements->push($content_element);
                    }
                }
            }

        }

        return $this;
    }

    public function getDraftVersion()
    {
        $draft_version = $this->versions()->whereNull('published_at')->first();
        if ($draft_version) {
            return $draft_version;
        } else {
            $version = (new Version)->saveVersion([
                'name' => $this->versions()->count() + 1,
                'versionable_type' => get_class($this),
                'versionable_id' => $this->id,
            ], null);

            $event_class = '\\App\\Events\\'.class_basename($this).'DraftCreated';
            broadcast(new $event_class($this->load('versions')));

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

        return $this->content_elements->filter(function ($content_element) {
            return Version::find($content_element->pivot->version_id)->published_at ? false : true;
        })->count() ? true : false;
    }
}
