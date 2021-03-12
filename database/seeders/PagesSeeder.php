<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Page;
use Illuminate\Support\Arr;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $home_page = new Page;
        $home_page->name = 'Home';
        $home_page->slug = '/';
        $home_page->parent_page_id = 0;
        $home_page->sort_order = 1;
        $home_page->footer_color = '218,241,250';
        $home_page->protected = true;
        $home_page->save();
        $home_page->publish();

        // why brentwood

        $why_brentwood = new Page;
        $why_brentwood->name = 'Why Brentwood';
        $why_brentwood->parent_page_id = 1;
        $why_brentwood->sort_order = 1;
        $why_brentwood->save();
        $why_brentwood->publish();

        $tripartite = new Page;
        $tripartite->name = 'Scheduling';
        $tripartite->parent_page_id = $why_brentwood->id;
        $tripartite->sort_order = 1;
        $tripartite->save();
        $tripartite->publish();

        $choose = new Page;
        $choose->name = 'Choice';
        $choose->parent_page_id = $why_brentwood->id;
        $choose->sort_order = 2;
        $choose->save();
        $choose->publish();

        $boarding = new Page;
        $boarding->name = 'Boarding';
        $boarding->parent_page_id = $why_brentwood->id;
        $boarding->sort_order = 3;
        $boarding->save();
        $boarding->publish();

        $support = new Page;
        $support->name = 'Support';
        $support->parent_page_id = $why_brentwood->id;
        $support->sort_order = 4;
        $support->save();
        $support->publish();

        $campus = new Page;
        $campus->name = 'Campus';
        $campus->parent_page_id = $why_brentwood->id;
        $campus->sort_order = 5;
        $campus->save();
        $campus->publish();

        $culture = new Page;
        $culture->name = 'Culture';
        $culture->parent_page_id = $why_brentwood->id;
        $culture->sort_order = 6;
        $culture->save();
        $culture->publish();

        $history = new Page;
        $history->name = 'Legacy';
        $history->parent_page_id = $why_brentwood->id;
        $history->sort_order = 7;
        $history->save();
        $history->publish();

        // Boarding Life

        $boarding_life = new Page;
        $boarding_life->name = 'Boarding';
        $boarding_life->parent_page_id = 1;
        $boarding_life->sort_order = 2;
        $boarding_life->save();
        $boarding_life->publish();

        $living_on_campus = new Page;
        $living_on_campus->name = 'Life On Campus';
        $living_on_campus->parent_page_id = $boarding_life->id;
        $living_on_campus->sort_order = 1;
        $living_on_campus->save();
        $living_on_campus->publish();

        $oceanfront = new Page;
        $oceanfront->name = 'Oceanfront';
        $oceanfront->parent_page_id = $boarding_life->id;
        $oceanfront->sort_order = 2;
        $oceanfront->save();
        $oceanfront->publish();

        $activities = new Page;
        $activities->name = 'Activities';
        $activities->parent_page_id = $boarding_life->id;
        $activities->sort_order = 3;
        $activities->save();
        $activities->publish();

        $leadership = new Page;
        $leadership->name = 'Student Leaders';
        $leadership->parent_page_id = $boarding_life->id;
        $leadership->sort_order = 4;
        $leadership->save();
        $leadership->publish();

        // Academics

        $academics = new Page;
        $academics->name = 'Academics';
        $academics->parent_page_id = 1;
        $academics->sort_order = 3;
        $academics->save();
        $academics->publish();

        $university = new Page;
        $university->name = 'University Placement';
        $university->parent_page_id = $academics->id;
        $university->sort_order = 1;
        $university->save();
        $university->publish();

        $ap = new Page;
        $ap->name = 'Advanced Placement';
        $ap->parent_page_id = $academics->id;
        $ap->sort_order = 2;
        $ap->save();
        $ap->publish();

        $grade12 = new Page;
        $grade12->name = 'Grade 12';
        $grade12->parent_page_id = $academics->id;
        $grade12->sort_order = 3;
        $grade12->save();
        $grade12->publish();

        $grade11 = new Page;
        $grade11->name = 'Grade 11';
        $grade11->parent_page_id = $academics->id;
        $grade11->sort_order = 4;
        $grade11->save();
        $grade11->publish();

        $grade10 = new Page;
        $grade10->name = 'Grade 10';
        $grade10->parent_page_id = $academics->id;
        $grade10->sort_order = 5;
        $grade10->save();
        $grade10->publish();

        $grade9 = new Page;
        $grade9->name = 'Grade 9';
        $grade9->parent_page_id = $academics->id;
        $grade9->sort_order = 6;
        $grade9->save();
        $grade9->publish();

        $grade8 = new Page;
        $grade8->name = 'Grade 8';
        $grade8->parent_page_id = $academics->id;
        $grade8->sort_order = 7;
        $grade8->save();
        $grade8->publish();

        // Athletics

        $athletics = new Page;
        $athletics->name = 'Athletics';
        $athletics->parent_page_id = 1;
        $athletics->sort_order = 4;
        $athletics->save();
        $athletics->publish();

        $basketball = new Page;
        $basketball->name = 'Basketball';
        $basketball->parent_page_id = $athletics->id;
        $basketball->sort_order = 1;
        $basketball->save();
        $basketball->publish();

        $climbing = new Page;
        $climbing->name = 'Climbing';
        $climbing->parent_page_id = $athletics->id;
        $climbing->sort_order = 2;
        $climbing->save();
        $climbing->publish();

        $cross_country = new Page;
        $cross_country->name = 'Cross Country Running';
        $cross_country->parent_page_id = $athletics->id;
        $cross_country->sort_order = 3;
        $cross_country->save();
        $cross_country->publish();

        $cross_training = new Page;
        $cross_training->name = 'Cross Training';
        $cross_training->parent_page_id = $athletics->id;
        $cross_training->sort_order = 4;
        $cross_training->save();
        $cross_training->publish();

        $field_hockey = new Page;
        $field_hockey->name = 'Field Hockey';
        $field_hockey->parent_page_id = $athletics->id;
        $field_hockey->sort_order = 5;
        $field_hockey->save();
        $field_hockey->publish();

        $golf = new Page;
        $golf->name = 'Golf';
        $golf->parent_page_id = $athletics->id;
        $golf->sort_order = 6;
        $golf->save();
        $golf->publish();

        $ice_hockey = new Page;
        $ice_hockey->name = 'Ice Hockey';
        $ice_hockey->parent_page_id = $athletics->id;
        $ice_hockey->sort_order = 7;
        $ice_hockey->save();
        $ice_hockey->publish();

        $outdoor_pursuits = new Page;
        $outdoor_pursuits->name = 'Outdoor Pursuits';
        $outdoor_pursuits->parent_page_id = $athletics->id;
        $outdoor_pursuits->sort_order = 8;
        $outdoor_pursuits->save();
        $outdoor_pursuits->publish();

        $rowing = new Page;
        $rowing->name = 'Rowing';
        $rowing->parent_page_id = $athletics->id;
        $rowing->sort_order = 9;
        $rowing->save();
        $rowing->publish();

        $rugby = new Page;
        $rugby->name = 'Rugby';
        $rugby->parent_page_id = $athletics->id;
        $rugby->sort_order = 10;
        $rugby->save();
        $rugby->publish();

        $soccer = new Page;
        $soccer->name = 'Soccer';
        $soccer->parent_page_id = $athletics->id;
        $soccer->sort_order = 11;
        $soccer->save();
        $soccer->publish();

        $squash = new Page;
        $squash->name = 'Squash';
        $squash->parent_page_id = $athletics->id;
        $squash->sort_order = 12;
        $squash->save();
        $squash->publish();

        $sNc = new Page;
        $sNc->name = 'Strength and Conditioning';
        $sNc->parent_page_id = $athletics->id;
        $sNc->sort_order = 13;
        $sNc->save();
        $sNc->publish();

        $swimming = new Page;
        $swimming->name = 'Swimming';
        $swimming->parent_page_id = $athletics->id;
        $swimming->sort_order = 14;
        $swimming->save();
        $swimming->publish();

        $tennis = new Page;
        $tennis->name = 'Tennis';
        $tennis->parent_page_id = $athletics->id;
        $tennis->sort_order = 15;
        $tennis->save();
        $tennis->publish();

        $volleyball = new Page;
        $volleyball->name = 'Volleyball';
        $volleyball->parent_page_id = $athletics->id;
        $volleyball->sort_order = 16;
        $volleyball->save();
        $volleyball->publish();

        $yoga = new Page;
        $yoga->name = 'Yoga';
        $yoga->parent_page_id = $athletics->id;
        $yoga->sort_order = 17;
        $yoga->save();
        $yoga->publish();

        // Arts

        $arts = new Page;
        $arts->name = 'Arts';
        $arts->parent_page_id = 1;
        $arts->sort_order = 5;
        $arts->save();
        $arts->publish();

        $sculpture = new Page;
        $sculpture->name = '3D Art and Sculpture';
        $sculpture->parent_page_id = $arts->id;
        $sculpture->sort_order = 1;
        $sculpture->save();
        $sculpture->publish();

        $acting = new Page;
        $acting->name = 'Acting & Drama';
        $acting->parent_page_id = $arts->id;
        $acting->sort_order = 2;
        $acting->save();
        $acting->publish();

        $dance = new Page;
        $dance->name = 'Dance';
        $dance->parent_page_id = $arts->id;
        $dance->sort_order = 3;
        $dance->save();
        $dance->publish();

        $debate = new Page;
        $debate->name = 'Debate and Public Speaking';
        $debate->parent_page_id = $arts->id;
        $debate->sort_order = 4;
        $debate->save();
        $debate->publish();

        $painting = new Page;
        $painting->name = 'Drawing and Painting';
        $painting->parent_page_id = $arts->id;
        $painting->sort_order = 5;
        $painting->save();
        $painting->publish();

        $media = new Page;
        $media->name = 'Media Arts';
        $media->parent_page_id = $arts->id;
        $media->sort_order = 6;
        $media->save();
        $media->publish();

        $music = new Page;
        $music->name = 'Music';
        $music->parent_page_id = $arts->id;
        $music->sort_order = 7;
        $music->save();
        $music->publish();

        $musical = new Page;
        $musical->name = 'Musical';
        $musical->parent_page_id = $arts->id;
        $musical->sort_order = 8;
        $musical->save();
        $musical->publish();

        $photography = new Page;
        $photography->name = 'Photography';
        $photography->parent_page_id = $arts->id;
        $photography->sort_order = 9;
        $photography->save();
        $photography->publish();

        $pottery = new Page;
        $pottery->name = 'Pottery';
        $pottery->parent_page_id = $arts->id;
        $pottery->sort_order = 10;
        $pottery->save();
        $pottery->publish();

        $robotics = new Page;
        $robotics->name = 'Robotics';
        $robotics->parent_page_id = $arts->id;
        $robotics->sort_order = 11;
        $robotics->save();
        $robotics->publish();

        $woodwork = new Page;
        $woodwork->name = 'Woodworking';
        $woodwork->parent_page_id = $arts->id;
        $woodwork->sort_order = 12;
        $woodwork->save();
        $woodwork->publish();

        // Admissions

        $admissions = new Page;
        $admissions->name = 'Admissions';
        $admissions->parent_page_id = 1;
        $admissions->sort_order = 6;
        $admissions->protected = true;
        $admissions->save();
        $admissions->publish();

        $apply = new Page;
        $apply->name = 'Apply';
        $apply->parent_page_id = $admissions->id;
        $apply->sort_order = 1;
        $apply->protected = true;
        $apply->save();
        $apply->publish();

        $fees = new Page;
        $fees->name = 'Fees';
        $fees->parent_page_id = $admissions->id;
        $fees->sort_order = 2;
        $fees->save();
        $fees->publish();

        $sessions = new Page;
        $sessions->name = 'Information Sessions';
        $sessions->parent_page_id = $admissions->id;
        $sessions->sort_order = 3;
        $sessions->save();
        $sessions->publish();

        // Portal

        $portal = new Page;
        $portal->name = 'Portal';
        $portal->parent_page_id = 1;
        $portal->sort_order = 7;
        $portal->save();
        $portal->publish();

        // Inquiries
        
        $inquiry = new Page;
        $inquiry->name = 'Inquiry';
        $inquiry->slug = 'inquiry';
        $inquiry->title = 'Create an Inquiry';
        $inquiry->parent_page_id = 1;
        $inquiry->sort_order = 8;
        $inquiry->unlisted = true;
        $inquiry->protected = true;
        $inquiry->save();
        $inquiry->publish();

        $inquiry_content = new Page;
        $inquiry_content->name = 'Inquiry Content';
        $inquiry_content->slug = 'inquiry-content';
        $inquiry_content->parent_page_id = $inquiry->id;
        $inquiry_content->sort_order = 1;
        $inquiry_content->unlisted = true;
        $inquiry_content->protected = true;
        $inquiry_content->save();
        $inquiry_content->publish();

        $register = new Page;
        $register->name = 'Register';
        $register->slug = 'register';
        $register->title = 'Create an Account';
        $register->parent_page_id = 1;
        $register->sort_order = 9;
        $register->unlisted = true;
        $register->protected = true;
        $register->save();
        $register->publish();

        $livestream = new Page;
        $livestream->name = 'Livestream Registration';
        $livestream->slug = 'livestream-register';
        $livestream->parent_page_id = 1;
        $livestream->sort_order = 10;
        $livestream->unlisted = true;
        $livestream->protected = true;
        $livestream->save();
        $livestream->publish();


        $news = new Page;
        $news->name = 'News';
        $news->parent_page_id = 1;
        $news->sort_order = 11;
        $news->unlisted = true;
        $news->save();
        $news->publish();

        $parents = new Page;
        $parents->name = 'Parents';
        $parents->parent_page_id = 1;
        $parents->sort_order = 12;
        $parents->unlisted = true;
        $parents->save();
        $parents->publish();

        $students = new Page;
        $students->name = 'Students';
        $students->parent_page_id = 1;
        $students->sort_order = 13;
        $students->unlisted = true;
        $students->save();
        $students->publish();

        $staff = new Page;
        $staff->name = 'Staff';
        $staff->parent_page_id = 1;
        $staff->sort_order = 14;
        $staff->unlisted = true;
        $staff->save();
        $staff->publish();

        $alumni = new Page;
        $alumni->name = 'Alumni';
        $alumni->parent_page_id = 1;
        $alumni->sort_order = 15;
        $alumni->unlisted = true;
        $alumni->save();
        $alumni->publish();

        $advancement = new Page;
        $advancement->name = 'Advancement';
        $advancement->parent_page_id = 1;
        $advancement->sort_order = 16;
        $advancement->unlisted = true;
        $advancement->save();
        $advancement->publish();

        $employement = new Page;
        $employement->name = 'Employement';
        $employement->parent_page_id = 1;
        $employement->sort_order = 17;
        $employement->unlisted = true;
        $employement->save();
        $employement->publish();

        $rentals = new Page;
        $rentals->name = 'Rentals';
        $rentals->parent_page_id = 1;
        $rentals->sort_order = 18;
        $rentals->unlisted = true;
        $rentals->save();
        $rentals->publish();

        $events = new Page;
        $events->name = 'Events';
        $events->parent_page_id = 1;
        $events->sort_order = 19;
        $events->unlisted = true;
        $events->save();
        $events->publish();
    }
}
