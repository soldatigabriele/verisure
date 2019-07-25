<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Record;
use Faker\Generator as Faker;

$factory->define(Record::class, function (Faker $faker) {

    $possibleStatuses = ['Your Secondary Alarm is activated'];

    return [
        'body' => $faker->sentence(5),
    ];
});
