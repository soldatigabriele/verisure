<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Setting;
use Faker\Generator as Faker;

$factory->define(Setting::class, function (Faker $faker) {

    return [
        'key' => $faker->word . '_' . $faker->word . '_' . $faker->word,
        'value' => random_int(0, 1),
    ];
});
