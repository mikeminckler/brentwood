<?php

use Illuminate\Database\Seeder;

use App\Blog;
use App\FileUpload;
use App\ContentElement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

class BlogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $blogs = collect(DB::connection('www_brentwood')->select(
            "SELECT * FROM tt_news WHERE image <> ''"
        ));

        $this->command->getOutput()->progressStart($blogs->count());
        
        foreach ($blogs as $data) {
            $this->command->getOutput()->progressAdvance();
            $input = [
                'name' => $data->title,
                'content_elements' => [],
            ];

            if (Validator::make($input, [
                'name' => 'required'
            ])->valid()) {
                $blog = (new Blog)->savePage(null, $input);

                if ($data->image) {
                    $url = 'https://www.brentwood.bc.ca/uploads/pics/'.$data->image;
                    $info = pathinfo($url);
                    $contents = file_get_contents($url);
                    $file = '/tmp/' . $info['basename'];
                    file_put_contents($file, $contents);

                    $uploaded_file = new UploadedFile($file, $info['basename']);
                    $file_upload = (new FileUpload)->saveFile($uploaded_file);

                    $image = (new ContentElement)->saveContentElement(null, [
                        'type' => 'photo-block',
                        'content' => [
                            'id' => 0,
                            'body' => '',
                            'columns' => 1,
                            'header' => '',
                            'height' => 33,
                            'id' => 0,
                            'padding' => 0,
                            'photos' => [
                                [
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
                                    'sort_order' => 1,
                                    'span' => 1,
                                    'stat_name' => null,
                                    'stat_number' => null,
                                ],
                            ],
                            'show_text' => 0,
                            'text_order' => 1,
                            'text_span' => 1,
                            'text_style' => '',
                        ],
                        'pivot' => [
                            'contentable_id' => $blog->id,
                            'contentable_type' => 'blog',
                            'expandable' => 0,
                            'sort_order' => 1,
                            'unlisted' => 0,
                        ],
                    ]);
                }

                $text_block = (new ContentElement)->saveContentElement(null, [
                    'type' => 'text-block',
                    'content' => [
                        'id' => 0,
                        'header' => '',
                        'body' => $data->bodytext,
                        'full_width' => 0,
                    ],
                    'pivot' => [
                        'contentable_id' => $blog->id,
                        'contentable_type' => 'blog',
                        'expandable' => 0,
                        'sort_order' => 2,
                        'unlisted' => 0,
                    ],
                ]);

                $blog->publish();
            }
        }

        $this->command->getOutput()->progressFinish();
    }
}
