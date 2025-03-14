<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;
use App\Models\History;
use Illuminate\Support\Facades\Http;

class TravelController extends Controller
{
    public function getCountries()
    {
        return response()->json(Country::all());
    }

    public function getCitiesByCountry($countryId)
    {
        $country = Country::with('cities')->find($countryId);

        if (!$country) {
            return response()->json(['error' => 'País no encontrado'], 404);
        }

        return response()->json($country->cities);
    }

    public function convertCurrency(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'from_currency' => 'required|string',
            'to_currency' => 'required|string',
        ]);

        // Coloca tu API Key aquí
        $apiKey = env('CHANGE_RATE_API_KEY');
        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$request->from_currency}";

        $response = Http::get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'Error al obtener tasas de cambio'], 500);
        }

        $rates = $response->json()['conversion_rates'];

        if (!isset($rates[$request->to_currency])) {
            return response()->json(['error' => 'Moneda de destino no soportada'], 400);
        }

        $convertedAmount = $request->amount * $rates[$request->to_currency];

        return response()->json([
            'from_currency' => $request->from_currency,
            'to_currency' => $request->to_currency,
            'exchange_rate' => $rates[$request->to_currency],
            'converted_amount' => round($convertedAmount, 2)
        ]);
    }

    public function getWeather($city)
    {
        $apiKey = env('WEATHER_API_KEY');

        $cityTranslations = [
            'Londres' => 'London',
            'Manchester' => 'Manchester',
            'Tokio' => 'Tokyo',
            'Osaka' => 'Osaka',
            'Nueva Delhi' => 'New Delhi',
            'Bombay' => 'Mumbai',
            'Copenhague' => 'Copenhagen',
            'Aarhus' => 'Aarhus'
        ];

        if (array_key_exists($city, $cityTranslations)) {
            $city = $cityTranslations[$city];
        }

        $response = Http::get("http://api.weatherapi.com/v1/current.json", [
            'key' => $apiKey,
            'q' => $city,
            'aqi' => 'no',
            'lang' => 'es'
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'No se pudo obtener el clima'], 500);
        }

        $data = $response->json();

        return response()->json([
            'city' => $data['location']['name'],
            'country' => $data['location']['country'],
            'temperature_c' => $data['current']['temp_c'],
            'temperature_f' => $data['current']['temp_f'],
            'condition' => $data['current']['condition']['text'],
            'humidity' => $data['current']['humidity'],
            'wind_kph' => $data['current']['wind_kph'],
        ]);
    }

    public function getHistory()
    {
        $history = History::with(['user:id,name', 'city:id,name,country_id', 'city.country:id,name'])
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'user_name' => $record->user->name,
                    'city' => $record->city->name,
                    'country' => $record->city->country->name,
                    'budget_cop' => $record->budget_cop,
                    'exchange_rate_currency' => $record->exchange_rate_currency,
                    'converted_amount' => $record->converted_amount,
                    'weather' => $record->weather,
                    'created_at' => $record->created_at
                ];
            });

        return response()->json($history);
    }

    public function storeHistory(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'budget_cop' => 'required|string|max:255',
            'exchange_rate_currency' => 'required|string|max:255',
            'converted_amount' => 'required|string|max:255',
            'weather' => 'required|string|max:255',
        ]);

        $history = History::create([
            'user_id' => auth()->id(),
            'city_id' => $request->city_id,
            'budget_cop' => $request->budget_cop,
            'exchange_rate_currency' => $request->exchange_rate_currency,
            'converted_amount' => $request->converted_amount,
            'weather' => $request->weather,
        ]);

        return response()->json([
            'message' => 'Historial guardado correctamente'
        ], 201);
    }
}
