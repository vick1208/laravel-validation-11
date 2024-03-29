<?php

namespace Tests\Feature;

use App\Rules\RegistrationRule;
use App\Rules\Uppercase;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidationValidator;
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
    public function testValidatorAdditionalValidation()
    {

        $data = [
            "username" => "eko@mail.com",
            "password" => "eko@mail.com",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        $validator->after(function (ValidationValidator $validator) {
            $data = $validator->getData();
            if ($data['username'] == $data['password']) {
                $validator->errors()->add("password", "Password harus berbeda dengan username");
            }
        });

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
    public function testValidatorCustomRule()
    {

        $data = [
            "username" => "eko@mail.com",
            "password" => "eko@mail.com",
        ];
        $rules = [
            "username" => ["required", "email", "max:100", new Uppercase()],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()],
        ];

        $validator = Validator::make($data, $rules);

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
    public function testValidatorCustomFunctionRule()
    {

        $data = [
            "username" => "eko@mail.com",
            "password" => "eko@mail.com",
        ];
        $rules = [
            "username" => ["required", "email", "max:100", function (string $attribute, string $value, Closure $fail) {
                if (strtoupper($value) != $value) {
                    $fail("The $attribute must be UPPERCASE.");
                }
            }],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()],
        ];

        $validator = Validator::make($data, $rules);

        assertNotNull($validator);

        assertTrue($validator->fails());
        assertFalse($validator->passes());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
    public function testValidatorRuleClasses()
    {

        $data = [
            "username" => "Santi",
            "password" => "test1222!!",
        ];
        $rules = [
            "username" => ["required", new In(["Budi", "Eko", "Santi"])],
            "password" => ["required", Password::min(6)->letters()->numbers()->symbols()]
        ];

        $validator = Validator::make($data, $rules);

        assertNotNull($validator);

        assertTrue($validator->passes());
    }
    public function testNestedArray()
    {
        $data = [
            "name" => [
                "first" => "Sumarjo",
                "last" => "Kurniawan"
            ],
            "address" => [
                "street" => "Jalan Gunung",
                "city" => "Bogor",
                "country" => "Indonesia"
            ]
        ];

        $rules = [
            "name.first" => ["required", "max:90"],
            "name.last" => ["max:90"],
            "address.street" => ["max:200"],
            "address.city" => ["required", "max:100"],
            "address.country" => ["required", "max:100"],
        ];

        $validator = Validator::make($data, $rules);

        assertTrue($validator->passes());
    }
    public function testNestedArrayIndexed()
    {
        $data = [
            "name" => [
                "first" => "Sumarjo",
                "last" => "Kurniawan"
            ],
            "address" => [
                [
                    "street" => "Jalan Gunung",
                    "city" => "Bogor",
                    "country" => "Indonesia"
                ],
                [
                    "street" => "Jalan Kemiri",
                    "city" => "Salatiga",
                    "country" => "Indonesia"
                ]
            ]

        ];

        $rules = [
            "name.first" => ["required", "max:90"],
            "name.last" => ["max:90"],
            "address.*.street" => ["max:200"],
            "address.*.city" => ["required", "max:100"],
            "address.*.country" => ["required", "max:100"],
        ];

        $validator = Validator::make($data, $rules);

        assertTrue($validator->passes());
    }
}
