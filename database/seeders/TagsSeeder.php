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
    public function run()
    {
        $names = collect([
             //"Torch - Featured",
             //"Torch Issue 35",
             //"Torch - Arts",
             //"Torch - Campus Life",
             //"Torch - Academics",
             //"Torch - Athletics",
             "Athletics",
             "Tennis",
             "Soccer",
             //"Girls Soccer",
             "Rowing",
             "Academics",
             "Volleyball",
             "Field Hockey",
             //"Boys Rugby",
             "Arts",
             //"Boys Basketball",
             //"Featured",
             //"Girls Rugby",
             "University Counselling",
             "Campus Life",
             "Ice Hockey",
             //"Girls Basketball",
             "Outdoor Pursuits",
             //"Administration",
             "Alumni",
             //"Sustainability",
             //"Admissions",
             //"Brentonian",
             "Interhouse",
             "Squash",
             //"Parent Blog",
             //"Head Of School",
             //"Torch - Head Of School",
             //"Torch - Admissions",
             //"Torch Issue 1",
             //"Torch Issue 2",
             //"Torch Issue 3",
             //"Torch Issue 4",
             //"Torch Issue 5",
             //"Torch Issue 6",
             //"Torch Issue 7",
             //"Torch Issue 8",
             //"Leadership",
             //"Torch Issue 9",
             //"Torch Issue 10",
             //"Torch Issue 11",
             //"Torch Issue 12",
             //"Torch Issue 13",
             "Debate",
             //"Torch Issue 14",
             //"Peru 2014",
             //"Torch Issue 15",
             //"Torch Issue 16",
             //"Torch Issue 17",
             //"B.e.a.t.",
             //"Torch Issue 18",
             "Musical",
             //"Torch Issue 19",
             "Social Studies",
             //"Boys Soccer",
             //"Torch Issue 20",
             //"Visual Arts",
             "English",
             "Music",
             //"Torch Issue 21",
             "Science",
             "Math",
             //"Torch Issue 22",
             //"Torch Issue 23",
             //"Torch Issue 24",
             "Languages",
             "Rugby",
             "Alex",
             "Hope",
             //"Torch Issue 25",
             "Photography",
             "Dance",
             //"Torch - Issue 26",
             //"Faculty & Staff",
             //"Torch Issue 27",
             "Mack",
             "Ellis",
             "Allard",
             "Privett",
             "Rogers",
             "Whittall",
             //"Torch Issue 28",
             //"Business & It",
             //"Torch Issue 29",
             //"Torch Issue 30",
             "Basketball",
             //"Torch Issue 31",
             //"Torch Issue 32",
             //"Torch Issue 33",
             //"Torch Issue 34",
             //"Torch Issue 36",
             //"Torch Issue 37",
             //"Torch Issue 38",
             //"The Torch",
             "Golf",
        ])->sort();

        foreach ($names as $name) {
            $tag = new Tag;
            $tag->name = $name;
            $tag->save();
        }
    }
}
