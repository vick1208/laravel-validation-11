<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

class ValidatorTest extends TestCase
{
    public function testValidator()
    {
        $data = [
            "username" => "admin",
            "password" => 123445,
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);

        assertNotNull($validator);

        assertTrue($validator->passes());
        assertFalse($validator->fails());
    }
    public function testInvalidValidator()
    {
        $data = [
            "username" => "",
            "password" => null,
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorValidationException()
    {
        $data = [
            "username" => null,
            "password" => null
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        assertNotNull($validator);

        try {
            $validator->validate();
            self::fail('Validation Exception Not Thrown');
        } catch (ValidationException $exception) {
            assertNotNull($exception->validator);
            $message = $exception->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }
    public function testValidationRules()
    {

        App::setLocale('id');

        $data = [
            "username" => "ek",
            "password" => "ek",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorValidData()
    {
        $data = [
            "username" => "admin@example.com",
            "password" => "test1234",
            "admin" => true,
            "others" => "xxxx1234",
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => "required|min:6|max:20"
        ];

        $validator = Validator::make($data, $rules);
        assertNotNull($validator);

        try {
            $valid = $validator->validate();
            Log::info(json_encode($valid, JSON_PRETTY_PRINT));
        } catch (ValidationException $exception) {
            assertNotNull($exception->validator);
            $message = $exception->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }

    public function testValidationInlineMessage()
    {
        $data = [
            "username" => "ek",
            "password" => "ek",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $messages = [
            "required" => ":attribute wajib diisi",
            "email" => ":attribute wajib berupa email",
            "min" => ":attribute minimal :min karakter",
            "max" => ":attribute maksimal :max karakter",
        ];

        $validator = Validator::make($data, $rules, $messages);

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
}
