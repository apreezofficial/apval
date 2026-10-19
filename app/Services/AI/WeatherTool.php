<?php
declare(strict_types=1);
namespace App\Services\AI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherTool {
    private string $apiKey;
    private string $baseUrl = 'https://api.openweathermap.org/data/2.5';

    public function __construct() {
        $this->apiKey = (string) config('services.openweathermap.api_key', '');
    }

    public function getWeather(string $location): array {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Weather service is currently unavailable. Please try again later.',
                'fallback_advice' => 'For agricultural planning, consider checking local weather forecasts.'
            ];
        }

        try {
            $response = Http::timeout(5)->get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => $location,
                'appid' => $this->apiKey, 
                'units' => 'metric',
            ]);

            if ($response->failed()) {
                return ['success' => false, 'message' => 'Could not retrieve weather data for this location.'];
            }

            $data = $response->json();
            return [
                'success' => true,
                'location' => $data['name'] ?? $location,
                'temperature_c' => round($data['main']['temp'] ?? 0, 1),
                'feels_like_c' => round($data['main']['feels_like'] ?? 0, 1),
                'humidity_percent' => $data['main']['humidity'] ?? null,
                'description' => $data['weather'][0]['description'] ?? 'Unknown',
                'wind_speed_ms' => $data['wind']['speed'] ?? null,
                'agricultural_advice' => $this->generateAgriAdvice($data),
            ];
        } catch (\Exception $e) {
            Log::error('Weather API Error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Weather service temporarily unavailable.'];
        }
    }

    private function generateAgriAdvice(array $data): string {
        $temp = $data['main']['temp'] ?? 25;
        $humidity = $data['main']['humidity'] ?? 50;
        $description = strtolower($data['weather'][0]['description'] ?? '');

        $advice = [];
        if (str_contains($description, 'rain')) {
            $advice[] = "Rain expected — delay fertilizer application and harvesting if possible.";
        }
        if ($temp > 32) {
            $advice[] = "High temperatures — ensure adequate irrigation and consider mulching.";
        }
        if ($humidity > 80) {
            $advice[] = "High humidity increases fungal disease risk — monitor crops closely.";
        }
        if (empty($advice)) {
            $advice[] = "Favorable conditions for most field activities.";
        }
        return implode(' ', $advice);
    }
}