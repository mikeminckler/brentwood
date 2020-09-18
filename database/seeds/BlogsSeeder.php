<?php

use Illuminate\Database\Seeder;

use App\Blog;
use App\FileUpload;
use App\ContentElement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class BlogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Blog::truncate();

        $blogs = collect(DB::connection('www_brentwood')->select(
            "
                SELECT * 
                FROM tt_news
            "
        ));

        $blogs = $blogs->sortBy('datetime');

        $this->command->getOutput()->progressStart($blogs->count());
        
        foreach ($blogs as $data) {
            $this->command->getOutput()->progressAdvance();
            //$this->command->info('UID: '.$data->uid);

            $sort_order = 3; // we reserve sort order 1 & 2 for banner and first paragraph
            $title_used = false;
            $banner = false;
            $paragraph = false;

            $input = [
                'name' => $data->title,
                'author' => $data->author,
                'content_elements' => [],
                'unlisted' => $data->hidden === 1 || $data->deleted === 1 ? true : false,
            ];

            if (Validator::make($input, [
                'name' => 'required',
            ])->valid()) {
                //$this->command->info('CREATED: '.$data->title);

                $blog = (new Blog)->savePage(null, $input);

                if ($data->image) {
                    $images = collect();
                    if (Str::contains($data->image, ',')) {
                        $data_images = collect(explode(',', $data->image));
                    } else {
                        $data_images = collect($data->image);
                    }

                    foreach ($data_images as $data_index => $data_image) {
                        $url = 'https://www.brentwood.bc.ca/uploads/pics/'.$data_image;
                        $images->push($url);
                    }

                    $this->createImage($blog, $images, $banner ? $sort_order : 1);

                    if ($banner) {
                        $sort_order++;
                    } else {
                        $banner = true;
                    }
                }

                if ($data->bodytext) {
                    if ($data->title) {
                        $title_used = true;
                    }

                    $this->createTextBlock($blog, null, $data->bodytext, $paragraph ? $sort_order : 2);

                    if ($paragraph) {
                        $sort_order++;
                    } else {
                        $paragraph = true;
                    }
                }

                if ($data->tx_rgnewsce_ce) {
                    //$this->command->info('EXTENDED: '.$data->tx_rgnewsce_ce);
                    foreach (explode(',', $data->tx_rgnewsce_ce) as $tt_content_uid) {
                        $tt_content_images = collect(DB::connection('www_brentwood')->select(
                            "SELECT * FROM sys_file_reference
                            WHERE uid_foreign = ".$tt_content_uid."
                            AND fieldname = 'image'"
                        ));

                        if ($tt_content_images->count()) {
                            $images = collect();
                            foreach ($tt_content_images as $tt_image) {
                                $image = collect(DB::connection('www_brentwood')->select(
                                    "SELECT * FROM sys_file WHERE uid = ".$tt_image->uid_local
                                ))->first();

                                $url = 'https://www.brentwood.bc.ca/fileadmin'.$image->identifier;
                                $images->push($url);
                            }

                            $this->createImage($blog, $images, $banner ? $sort_order : 1);
                            if ($banner) {
                                $sort_order++;
                            } else {
                                $banner = true;
                            }
                        }

                        $tt_content = collect(DB::connection('www_brentwood')->select(
                            "SELECT * 
                             FROM tt_content
                             WHERE uid = ".$tt_content_uid
                        ))->first();

                        if ($tt_content) {
                            $this->createTextBlock($blog, $title_used ? $tt_content->header : null, $tt_content->bodytext, $paragraph ? $sort_order : 2);
                            $title_used = true;
                            if ($paragraph) {
                                $sort_order++;
                            } else {
                                $paragraph = true;
                            }
                        } else {
                            //$this->command->error('NOT FOUND EXTENDED TT_CONTENT: '.$tt_content_uid);
                        }
                    }
                }


                $tags = collect(DB::connection('www_brentwood')->select(
                    "SELECT *
                    FROM tt_news_cat_mm
                    WHERE uid_local = ".$data->uid
                ));

                if ($tags->count()) {
                    foreach ($tags as $tag_data) {
                        $tag = collect(DB::connection('www_brentwood')->select(
                            "SELECT *
                            FROM tt_news_cat
                            WHERE uid = ".$tag_data->uid_foreign
                        ))->first();

                        if ($tag) {
                            $blog->addTag($tag->title);
                        }
                    }
                }

                $blog->publish();
                $blog->refresh();
                $version = $blog->publishedVersion;
                $version->published_at = Carbon::parse($data->datetime);
                $version->save();
            }
        }

        $this->command->getOutput()->progressFinish();
    }

    protected function createTextBlock($blog, $title = null, $bodytext, $sort_order)
    {
        $text_block = (new ContentElement)->saveContentElement(null, [
            'type' => 'text-block',
            'content' => [
                'id' => 0,
                'header' => $title,
                'body' => $bodytext,
                'full_width' => 0,
            ],
            'pivot' => [
                'contentable_id' => $blog->id,
                'contentable_type' => 'blog',
                'expandable' => 0,
                'sort_order' => $sort_order,
                'unlisted' => 0,
            ],
        ]);
    }

    protected function createImage($blog, $urls, $sort_order)
    {
        $photos = collect();

        foreach ($urls as $index => $url) {
            $info = pathinfo($url);
            $contents = file_get_contents($url);
            $file = '/tmp/' . $info['basename'];
            file_put_contents($file, $contents);

            $uploaded_file = new UploadedFile($file, $info['basename']);
            $file_upload = (new FileUpload)->saveFile($uploaded_file);

            $photo = [
                'alt' => "",
                'description' => "",
                'file_upload' => $file_upload,
                'fill' => true,
                'id' => 0,
                'large' => null,
                'link' => null,
                'name' => "",
                'offsetX' => 50,
                'offsetY' => 50,
                'sort_order' => $index + 1,
                'span' => 1,
                'stat_name' => null,
                'stat_number' => null,
            ];
            $photos->push($photo);
        }

        $image = (new ContentElement)->saveContentElement(null, [
            'type' => 'photo-block',
            'content' => [
                'id' => 0,
                'body' => '',
                'columns' => $urls->count() === 4 ? 2 : ($urls->count() > 2 ? 3 : $urls->count()),
                'header' => '',
                'height' => 50,
                'id' => 0,
                'padding' => 0,
                'photos' => $photos,
                'show_text' => 0,
                'text_order' => 1,
                'text_span' => 1,
                'text_style' => '',
            ],
            'pivot' => [
                'contentable_id' => $blog->id,
                'contentable_type' => 'blog',
                'expandable' => 0,
                'sort_order' => $sort_order,
                'unlisted' => 0,
            ],
        ]);
    }
}
