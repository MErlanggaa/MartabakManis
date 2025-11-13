<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleMapsController extends Controller
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    public function geocode(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
        ]);

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $request->address,
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                $location = $data['results'][0]['geometry']['location'];
                
                return response()->json([
                    'success' => true,
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'formatted_address' => $data['results'][0]['formatted_address'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Alamat tidak ditemukan',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari alamat',
            ], 500);
        }
    }

    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => $request->latitude . ',' . $request->longitude,
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                return response()->json([
                    'success' => true,
                    'formatted_address' => $data['results'][0]['formatted_address'],
                    'address_components' => $data['results'][0]['address_components'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi tidak ditemukan',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari lokasi',
            ], 500);
        }
    }

    public function getNearbyPlaces(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'integer|min:1|max:50000',
            'type' => 'string|max:100',
        ]);

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'location' => $request->latitude . ',' . $request->longitude,
                'radius' => $request->radius ?? 1000,
                'type' => $request->type ?? 'store',
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($data['status'] === 'OK') {
                return response()->json([
                    'success' => true,
                    'places' => $data['results'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tempat ditemukan',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari tempat',
            ], 500);
        }
    }
}