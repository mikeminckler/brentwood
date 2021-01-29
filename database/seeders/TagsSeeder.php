<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Tag;

class TagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    protected $protected_tags = [
        'Admissions',
        'Boarding Student',
        'Day Student',
        'Open House',
    ];

    public function run()
    {
        $tags = collect([

            'Admissions' => [
                'Boarding Student',
                'Day Student',
                'Open House',
            ],

            "Academics" => [
                 "English",
                 "Languages",
                 "Math",
                 "Science",
                 "Social Studies",
            ],

            "Athletics" => [
                 "Basketball",
                 "Field Hockey",
                 "Golf",
                 "Ice Hockey",
                 "Outdoor Pursuits",
                 "Rowing",
                 "Rugby",
                 "Soccer",
                 "Squash",
                 "Tennis",
                 "Volleyball",
            ],

            "Arts" => [
                 "Dance",
                 "Debate",
                 "Musical",
                 "Music",
                 "Photography",
            ],

            "University Counselling" => [
            ],

             "Campus Life" => [
                 "Alex",
                 "Allard",
                 "Ellis",
                 "Hope",
                 "Interhouse",
                 "Mack",
                 "Privett",
                 "Rogers",
                 "Whittall",
             ],

             "Alumni" => [
             ],

        ]);

        foreach ($tags as $parent => $childern) {
            $parent_tag = new Tag;
            $parent_tag->name = $parent;
            $parent_tag->save();

            foreach ($childern as $child) {
                $tag = new Tag;
                $tag->name = $child;
                $tag->parent_tag_id = $parent_tag->id;
                $tag->save();
            }
        }

        foreach ($this->protected_tags as $name) {
            $tag = Tag::where('name', $name)->first();
            if (!$tag) {
                throw \Exception('Could not find tag '.$name);
            }
            $tag->protected = true;
            $tag->save();
        }
    }
}
