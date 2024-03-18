<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

class ValidatorTest extends TestCase
{
    public function testValidator(){
        $data = [
            "username" => "admin",
            "password" => 123445,
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data,$rules);

        assertNotNull($validator);

        assertTrue($validator->passes());
        assertFalse($validator->fails());

    }
    public function testInvalidValidator(){
        $data = [
            "username" => "",
            "password" => null,
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data,$rules);

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));

    }
}
