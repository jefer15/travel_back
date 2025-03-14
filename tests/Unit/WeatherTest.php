<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\TravelController;

class WeatherTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        Http::fake([
            'http://api.weatherapi.com/v1/current.json*' => Http::response([
                'location' => [
                    'name' => 'Bogotá',
                    'country' => 'Colombia'
                ],
                'current' => [
                    'temp_c' => 20,
                    'temp_f' => 68,
                    'condition' => ['text' => 'Soleado'],
                    'humidity' => 50,
                    'wind_kph' => 10
                ]
            ], 200)
        ]);

        $controller = new TravelController();

        $response = $controller->getWeather('Bogotá');

        $responseData = $response->getData(true);

        $this->assertEquals('Bogotá', $responseData['city']);
        $this->assertEquals('Colombia', $responseData['country']);
        $this->assertEquals(20, $responseData['temperature_c']);
        $this->assertEquals(68, $responseData['temperature_f']);
        $this->assertEquals('Soleado', $responseData['condition']);
        $this->assertEquals(50, $responseData['humidity']);
        $this->assertEquals(10, $responseData['wind_kph']);

    }
}
