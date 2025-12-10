<?php

namespace App\Http\Controllers;

use App\Models\UMKM;
use App\Models\User;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(30); // Set execution time limit to 30 seconds
        // Query untuk mengambil layanan dengan relasi UMKM
        $query = \App\Models\Layanan::with('umkm.user');

        // Search: cari di nama layanan, deskripsi, atau nama UMKM
        if ($request->filled('search')) {
            $kw = trim($request->search);
            $query->where(function ($q) use ($kw) {
                $q->where('nama', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%")
                  ->orWhereHas('umkm', function ($qq) use ($kw) {
                      $qq->where('nama', 'like', "%{$kw}%")
                         ->orWhere('jenis_umkm', 'like', "%{$kw}%");
                  });
            });
        }

        // Filter by kategori UMKM
        if ($request->filled('kategori')) {
            $query->whereHas('umkm', function ($q) use ($request) {
                $q->where('jenis_umkm', $request->kategori);
            });
        }

        // Favorit saya: hanya jika login dan punya favorit
        if ($request->boolean('favorite') && Auth::check()) {
            $userId = Auth::id();
            $userFavoriteUmkmIds = collect(Auth::user()->favorites ?? [])
                ->map(fn ($v) => (int) $v)->filter()->values()->all();

            if (!empty($userFavoriteUmkmIds)) {
                // Use whereIn with explicit table prefix to avoid ambiguous column error
                $query->whereHas('umkm', function ($q) use ($userId, $userFavoriteUmkmIds) {
                    // Get the model instance to access table name and primary key
                    $model = $q->getModel();
                    $tableName = $model->getTable();
                    $primaryKey = $model->getKeyName();
                    $qualifiedKeyName = $tableName . '.' . $primaryKey;
                    
                    $q->where(function ($qq) use ($userId, $userFavoriteUmkmIds, $qualifiedKeyName) {
                        // For JSON column, no prefix needed (already scoped to umkm table)
                        // For ID column, use qualified column name (umkm.id) to avoid ambiguity with pivot table
                        $qq->whereJsonContains('favorite', $userId)
                           ->orWhereIn($qualifiedKeyName, $userFavoriteUmkmIds);
                    });
                });
            } else {
                // Jika tidak ada favorit, return empty
                $query->whereRaw('1 = 0');
            }
        }

        // Jarak: hanya jika lat & lng terisi DAN distance terisi
        if ($request->filled('user_lat') && $request->filled('user_lng') && $request->filled('distance')) {
            $userLat = $request->user_lat;
            $userLng = $request->user_lng;
            $maxDistance = $request->input('distance', 10);

            $query->whereHas('umkm', function ($q) use ($userLat, $userLng, $maxDistance) {
                $q->whereRaw("
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) + sin(radians(?)) *
                    sin(radians(latitude)))) <= ?
                ", [$userLat, $userLng, $userLat, $maxDistance]);
            });
        }

        // Jika ini adalah rekomendasi berdasarkan jarak, tambahkan sorting berdasarkan jarak
        if ($request->boolean('recommendation') && $request->filled('user_lat') && $request->filled('user_lng')) {
            $userLat = $request->user_lat;
            $userLng = $request->user_lng;
            
            // Tambahkan filter berdasarkan preferensi user jika login
            if (Auth::check()) {
                $user = Auth::user();
                $userFavorites = $user->favorites ?? [];
                
                if (!empty($userFavorites)) {
                    // Prioritaskan jenis UMKM favorit user
                    $favoriteUmkm = UMKM::whereIn('id', $userFavorites)->get();
                    $favoriteJenis = $favoriteUmkm->pluck('jenis_umkm')->unique()->toArray();
                    
                    if (!empty($favoriteJenis)) {
                        $query->whereHas('umkm', function ($q) use ($favoriteJenis) {
                            $q->whereIn('jenis_umkm', $favoriteJenis);
                        });
                    }
                }
            }
        }

        $layanan = $query->paginate(12)->appends($request->query());

        // Get categories with count (berdasarkan layanan)
        $categories = \App\Models\UMKM::selectRaw('jenis_umkm, COUNT(DISTINCT layanan.id) as count')
            ->join('layanan_umkm', 'umkm.id', '=', 'layanan_umkm.umkm_id')
            ->join('layanan', 'layanan_umkm.layanan_id', '=', 'layanan.id')
            ->groupBy('jenis_umkm')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->jenis_umkm,
                    'count' => $item->count
                ];
            });

        // Just pass coordinates to view - address will be loaded via JavaScript on client-side
        // This makes page load much faster
        $layanan->getCollection()->transform(function ($item) {
            $umkm = $item->umkm->first();
            
            if (!$umkm) {
                $item->umkm_latitude = null;
                $item->umkm_longitude = null;
                return $item;
            }
            
            // Store coordinates for JavaScript reverse geocoding
            $item->umkm_latitude = $umkm->latitude;
            $item->umkm_longitude = $umkm->longitude;
            $item->umkm_id = $umkm->id;
            
            // Calculate rating for layanan
            $item->rating_layanan = Comment::where('layanan_id', $item->id)->avg('rating') ?? 0;
            
            // Calculate rating for UMKM (komentar untuk UMKM, bukan layanan)
            $item->rating_umkm = Comment::where('umkm_id', $umkm->id)
                ->whereNull('layanan_id')
                ->avg('rating') ?? 0;
            
            return $item;
        });

        // Pass user location ke view untuk menghitung jarak
        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');

        return view('user.katalog', compact('layanan', 'categories', 'userLat', 'userLng'));
    }

    public function show($id)
    {
        set_time_limit(30); // Set execution time limit to 30 seconds
        $umkm = UMKM::with('user', 'layanan', 'keuntungan')->findOrFail($id);
        
        // Increment views
        $umkm->increment('views');
        
        // Get similar UMKM based on category (jenis_umkm)
        $similarUmkm = UMKM::where('jenis_umkm', $umkm->jenis_umkm)
            ->where('id', '!=', $umkm->id) // Exclude current UMKM
            ->with('user', 'layanan')
            ->limit(4) // Limit to 4 similar UMKM
            ->get();
        
        // Get comments with user relation, ordered by newest first
        $comments = Comment::where('umkm_id', $umkm->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get average rating
        $averageRating = Comment::where('umkm_id', $umkm->id)->avg('rating') ?? 0;
        $totalComments = Comment::where('umkm_id', $umkm->id)->count();
        
        // Get user's comment if authenticated
        $userComment = null;
        if (Auth::check()) {
            $userComment = Comment::where('umkm_id', $umkm->id)
                ->where('user_id', Auth::id())
                ->first();
        }
        
        // Address will be loaded via JavaScript from coordinates
        // No need to fetch from API on server-side - makes page load faster
        
        return view('user.detail-umkm', compact('umkm', 'similarUmkm', 'comments', 'averageRating', 'totalComments', 'userComment'));
    }
    
    /**
     * Helper method to get quick location name (city, province) - faster for katalog
     */
    private function getQuickLocationName($latitude, $longitude)
    {
        $locationName = null;
        
        // Try OpenStreetMap first (faster, free, no API key needed)
        try {
            $response = Http::timeout(5)->retry(2, 100)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
                'zoom' => 10, // Lower zoom for faster response
                'addressdetails' => 1,
                'accept-language' => 'id',
            ]);

            $data = $response->json();
            if (isset($data['address'])) {
                $address = $data['address'];
                $cityName = $address['city'] 
                    ?? $address['town'] 
                    ?? $address['municipality']
                    ?? $address['county']
                    ?? null;
                $state = $address['state'] ?? $address['region'] ?? null;
                
                // Handle Jakarta
                if ($state && (stripos($state, 'Jakarta') !== false || stripos($state, 'DKI') !== false)) {
                    $state = 'DKI Jakarta';
                }
                
                if ($cityName && $state) {
                    $locationName = $cityName . ', ' . $state;
                } elseif ($cityName) {
                    $locationName = $cityName;
                } elseif ($state) {
                    $locationName = $state;
                }
            }
        } catch (\Exception $e) {
            // Silent fail for quick lookup
        }
        
        return $locationName;
    }
    
    /**
     * Helper method to get full address from coordinates
     */
    private function getFullAddressFromCoordinates($latitude, $longitude)
    {
        $fullAddress = null;
        $locationName = null;
        $formattedAddress = null; // Store formatted address as fallback
        
        \Log::info('Getting address for coordinates', ['lat' => $latitude, 'lng' => $longitude]);
        
        // Try Google Maps first (if API key available)
        $apiKey = config('services.google_maps.api_key');
        if ($apiKey) {
            try {
                $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => $latitude . ',' . $longitude,
                    'key' => $apiKey,
                    'language' => 'id',
                    'result_type' => 'street_address|premise|subpremise|route|sublocality|sublocality_level_1|sublocality_level_2|administrative_area_level_3|administrative_area_level_2|administrative_area_level_1|postal_code',
                ]);

                $data = $response->json();
                \Log::info('Google Maps response', ['status' => $data['status'] ?? 'unknown', 'results_count' => count($data['results'] ?? [])]);
                
                if (isset($data['status']) && ($data['status'] === 'OK' || $data['status'] === 'ZERO_RESULTS') && !empty($data['results'])) {
                    // Use the first result (most accurate)
                    $result = $data['results'][0];
                    $addressComponents = $result['address_components'] ?? [];
                    $formattedAddress = $result['formatted_address'] ?? null;
                    
                    // CRITICAL: Always use formatted_address if available, even if components are empty
                    if ($formattedAddress && !$fullAddress) {
                        $fullAddress = $formattedAddress;
                        $locationName = $formattedAddress;
                    }
                    
                    // Initialize address parts
                    $streetNumber = null;
                    $route = null;
                    $subpremise = null; // Blok/Apartment
                    $premise = null; // Nama gedung/tempat
                    $kelurahan = null;
                    $kecamatan = null;
                    $city = null;
                    $province = null;
                    $postalCode = null;
                    $rt = null;
                    $rw = null;
                    
                    // Extract all address components
                    foreach ($addressComponents as $component) {
                        $types = $component['types'];
                        $name = $component['long_name'];
                        $shortName = $component['short_name'] ?? $name;
                        
                        if (in_array('street_number', $types)) {
                            $streetNumber = $name;
                        } elseif (in_array('route', $types)) {
                            $route = $name;
                        } elseif (in_array('subpremise', $types)) {
                            $subpremise = $name; // Blok, No. Unit, dll
                        } elseif (in_array('premise', $types)) {
                            $premise = $name; // Nama gedung/tempat
                        } elseif (in_array('sublocality_level_2', $types)) {
                            // Bisa jadi RW atau area lebih kecil
                            if (preg_match('/RW\s*\d+/i', $name, $matches)) {
                                $rw = $matches[0];
                            } else {
                                $kelurahan = $name;
                            }
                        } elseif (in_array('sublocality_level_1', $types) || in_array('neighborhood', $types)) {
                            if (preg_match('/RT\s*\d+/i', $name, $matches)) {
                                $rt = $matches[0];
                            } elseif (preg_match('/RW\s*\d+/i', $name, $matches)) {
                                $rw = $matches[0];
                            } else {
                                $kelurahan = $name;
                            }
                        } elseif (in_array('administrative_area_level_4', $types)) {
                            $kelurahan = $name;
                        } elseif (in_array('administrative_area_level_3', $types) || in_array('sublocality', $types)) {
                            $kecamatan = $name;
                        } elseif (in_array('administrative_area_level_2', $types)) {
                            $city = $name;
                        } elseif (in_array('administrative_area_level_1', $types)) {
                            $province = $name;
                        } elseif (in_array('postal_code', $types)) {
                            $postalCode = $name;
                        }
                    }
                    
                    // Try to extract RT/RW from formatted address if not found
                    if (!$rt || !$rw) {
                        if ($formattedAddress) {
                            // Pattern untuk RT/RW di Indonesia
                            if (preg_match('/RT\s*\.?\s*(\d+)/i', $formattedAddress, $rtMatch)) {
                                $rt = 'RT ' . $rtMatch[1];
                            }
                            if (preg_match('/RW\s*\.?\s*(\d+)/i', $formattedAddress, $rwMatch)) {
                                $rw = 'RW ' . $rwMatch[1];
                            }
                        }
                    }
                    
                    // Handle Jakarta khusus
                    if ($province && (stripos($province, 'jakarta') !== false || 
                        stripos($province, 'dki') !== false ||
                        stripos($province, 'daerah khusus') !== false)) {
                        $province = 'DKI Jakarta';
                        if (!$city) {
                            foreach ($addressComponents as $component) {
                                if (in_array('locality', $component['types']) || 
                                    in_array('sublocality_level_1', $component['types'])) {
                                    $city = $component['long_name'];
                                    break;
                                }
                            }
                        }
                        if (!$city) {
                            $city = 'Jakarta';
                        }
                    }
                    
                    // Build full address dengan urutan: Nomor, Jalan, Blok, RT, RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos
                    $addressParts = [];
                    if ($premise) $addressParts[] = $premise;
                    if ($streetNumber) $addressParts[] = 'No. ' . $streetNumber;
                    if ($subpremise) $addressParts[] = $subpremise;
                    if ($route) $addressParts[] = $route;
                    if ($rt) $addressParts[] = $rt;
                    if ($rw) $addressParts[] = $rw;
                    if ($kelurahan) $addressParts[] = 'Kel. ' . $kelurahan;
                    if ($kecamatan) $addressParts[] = 'Kec. ' . $kecamatan;
                    if ($city) $addressParts[] = $city;
                    if ($province) $addressParts[] = $province;
                    if ($postalCode) $addressParts[] = $postalCode;
                    
                    if (!empty($addressParts)) {
                        $fullAddress = implode(', ', $addressParts);
                    } elseif ($formattedAddress) {
                        // Use formatted address if we can't build from components
                        $fullAddress = $formattedAddress;
                    }
                    
                    \Log::info('Google Maps address extracted', [
                        'fullAddress' => $fullAddress,
                        'formattedAddress' => $formattedAddress,
                        'components' => [
                            'streetNumber' => $streetNumber,
                            'route' => $route,
                            'kelurahan' => $kelurahan,
                            'kecamatan' => $kecamatan,
                            'city' => $city,
                            'province' => $province
                        ]
                    ]);
                    
                    if ($city && $province) {
                        $locationName = $city . ', ' . $province;
                    } elseif ($city) {
                        $locationName = $city;
                    } elseif ($province) {
                        $locationName = $province;
                    } elseif ($formattedAddress) {
                        // Use formatted address as location name if no city/province
                        $locationName = $formattedAddress;
                    }
                    
                    // If we have formatted address but no fullAddress, use formatted address
                    if (!$fullAddress && $formattedAddress) {
                        $fullAddress = $formattedAddress;
                    }
                } else {
                    \Log::warning('Google Maps geocoding failed', ['status' => $data['status'] ?? 'unknown', 'error' => $data['error_message'] ?? 'No error message']);
                    // Try to get ANY address from results, even if status is not OK
                    if (!empty($data['results'])) {
                        foreach ($data['results'] as $result) {
                            if (isset($result['formatted_address'])) {
                                $formattedAddress = $result['formatted_address'];
                                $fullAddress = $formattedAddress;
                                $locationName = $formattedAddress;
                                \Log::info('Using formatted_address from non-OK status result', ['address' => $fullAddress]);
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Google Maps reverse geocoding exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        } else {
            \Log::info('Google Maps API key not configured, using OpenStreetMap');
        }
        
        // Fallback to OpenStreetMap - ALWAYS try if we don't have fullAddress yet
        if (!$fullAddress) {
            try {
                \Log::info('Trying OpenStreetMap reverse geocoding', ['lat' => $latitude, 'lng' => $longitude]);
                $response = Http::timeout(15)->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18, // Higher zoom for more detail
                    'addressdetails' => 1,
                    'accept-language' => 'id',
                    'namedetails' => 1, // Include named details
                ]);

                $data = $response->json();
                \Log::info('OpenStreetMap response', [
                    'has_address' => isset($data['address']), 
                    'display_name' => $data['display_name'] ?? 'N/A',
                    'error' => $data['error'] ?? null
                ]);
                
                // CRITICAL: Always use display_name if available, even if address components are missing
                if (isset($data['display_name']) && $data['display_name']) {
                    if (!$fullAddress) {
                        $fullAddress = $data['display_name'];
                        $locationName = $data['display_name'];
                        \Log::info('Using OpenStreetMap display_name', ['address' => $fullAddress]);
                    }
                }
                
                if (isset($data['address'])) {
                    $address = $data['address'];
                    $displayName = $data['display_name'] ?? null;
                    
                    $houseNumber = $address['house_number'] ?? null;
                    $road = $address['road'] ?? null;
                    $suburb = $address['suburb'] ?? $address['neighbourhood'] ?? $address['village'] ?? null;
                    $cityDistrict = $address['city_district'] ?? $address['suburb'] ?? null;
                    $cityName = $address['city'] 
                        ?? $address['town'] 
                        ?? $address['municipality']
                        ?? $address['county']
                        ?? null;
                    $state = $address['state'] ?? $address['region'] ?? null;
                    $postcode = $address['postcode'] ?? null;
                    $quarter = $address['quarter'] ?? null; // Bisa jadi kelurahan
                    $residential = $address['residential'] ?? null;
                    $hamlet = $address['hamlet'] ?? null;
                    
                    // Extract RT/RW from display name if available
                    $rt = null;
                    $rw = null;
                    if ($displayName) {
                        if (preg_match('/RT\s*\.?\s*(\d+)/i', $displayName, $rtMatch)) {
                            $rt = 'RT ' . $rtMatch[1];
                        }
                        if (preg_match('/RW\s*\.?\s*(\d+)/i', $displayName, $rwMatch)) {
                            $rw = 'RW ' . $rwMatch[1];
                        }
                    }
                    
                    // Determine kelurahan and kecamatan
                    if (!$suburb) {
                        $suburb = $quarter ?? $residential ?? $hamlet;
                    }
                    if (!$cityDistrict && $suburb) {
                        // Sometimes city_district is the kecamatan
                        $cityDistrict = $address['city_district'];
                    }
                    
                    // Handle Jakarta khusus
                    if ($state && (stripos($state, 'Jakarta') !== false || 
                        stripos($state, 'DKI') !== false ||
                        stripos($state, 'Daerah Khusus') !== false)) {
                        $state = 'DKI Jakarta';
                        if (!$cityName) {
                            $cityName = $address['locality'] 
                                ?? $address['suburb']
                                ?? $address['city_district']
                                ?? 'Jakarta';
                        }
                    } elseif ($state && stripos($state, 'Jawa') !== false && 
                            ($cityName && stripos($cityName, 'Jakarta') !== false)) {
                        $state = 'DKI Jakarta';
                    }
                    
                    // Build full address
                    $addressParts = [];
                    if ($houseNumber) $addressParts[] = 'No. ' . $houseNumber;
                    if ($road) $addressParts[] = $road;
                    if ($rt) $addressParts[] = $rt;
                    if ($rw) $addressParts[] = $rw;
                    if ($suburb) $addressParts[] = 'Kel. ' . $suburb;
                    if ($cityDistrict && $cityDistrict != $suburb) $addressParts[] = 'Kec. ' . $cityDistrict;
                    if ($cityName) $addressParts[] = $cityName;
                    if ($state) $addressParts[] = $state;
                    if ($postcode) $addressParts[] = $postcode;
                    
                    if (!empty($addressParts)) {
                        $fullAddress = implode(', ', $addressParts);
                    } elseif ($displayName) {
                        // Use display name from OpenStreetMap if we can't build from components
                        $fullAddress = $displayName;
                    }
                    
                    \Log::info('OpenStreetMap address extracted', [
                        'fullAddress' => $fullAddress,
                        'displayName' => $displayName,
                        'components' => [
                            'houseNumber' => $houseNumber,
                            'road' => $road,
                            'suburb' => $suburb,
                            'cityDistrict' => $cityDistrict,
                            'cityName' => $cityName,
                            'state' => $state
                        ]
                    ]);
                    
                    if ($cityName && $state) {
                        $locationName = $cityName . ', ' . $state;
                    } elseif ($cityName) {
                        $locationName = $cityName;
                    } elseif ($state) {
                        $locationName = $state;
                    } elseif ($displayName) {
                        // Use display name as location name if no city/state
                        $locationName = $displayName;
                    }
                    
                    // If we have display name but no fullAddress, use display name
                    if (!$fullAddress && $displayName) {
                        $fullAddress = $displayName;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('OpenStreetMap reverse geocoding exception: ' . $e->getMessage(), [
                    'lat' => $latitude, 
                    'lng' => $longitude,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // CRITICAL FALLBACK: Always try to get SOMETHING from the coordinates
        // If we still don't have fullAddress, try formattedAddress or locationName
        if (!$fullAddress) {
            if ($formattedAddress) {
                $fullAddress = $formattedAddress;
                if (!$locationName) {
                    $locationName = $formattedAddress;
                }
                \Log::info('Using formattedAddress as final fallback', ['address' => $fullAddress]);
            } elseif ($locationName) {
                $fullAddress = $locationName;
                \Log::info('Using locationName as final fallback', ['address' => $fullAddress]);
            } else {
                // Last resort: Try one more time with simpler OpenStreetMap request
                try {
                    \Log::warning('Attempting final OpenStreetMap request with simpler parameters', ['lat' => $latitude, 'lng' => $longitude]);
                    $response = Http::timeout(10)->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'zoom' => 10, // Lower zoom for broader area
                        'addressdetails' => 0, // Don't need details, just display_name
                    ]);
                    
                    $data = $response->json();
                    if (isset($data['display_name']) && $data['display_name']) {
                        $fullAddress = $data['display_name'];
                        $locationName = $data['display_name'];
                        \Log::info('Final OpenStreetMap request succeeded', ['address' => $fullAddress]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Final OpenStreetMap request failed: ' . $e->getMessage());
                }
            }
        }
        
        // Log final result
        if ($fullAddress) {
            \Log::info('Final address result', ['fullAddress' => $fullAddress, 'locationName' => $locationName]);
        } else {
            \Log::error('CRITICAL: No address found for coordinates after ALL attempts', [
                'lat' => $latitude, 
                'lng' => $longitude,
                'formattedAddress' => $formattedAddress,
                'locationName' => $locationName
            ]);
        }
        
        // Ensure we never return empty string
        if ($fullAddress === '') {
            $fullAddress = null;
        }
        if ($locationName === '') {
            $locationName = null;
        }
        
        return [
            'fullAddress' => $fullAddress,
            'locationName' => $locationName
        ];
    }

    public function showLayanan($id)
    {
        set_time_limit(30); // Set execution time limit to 30 seconds
        $layanan = \App\Models\Layanan::with('umkm.user')->findOrFail($id);
        
        // Increment views for layanan
        $layanan->increment('views');
        
        // Get the first UMKM associated with this layanan
        $umkm = $layanan->umkm->first();
        
        if (!$umkm) {
            abort(404, 'UMKM tidak ditemukan untuk layanan ini');
        }
        
        // Increment views for UMKM as well (when viewing layanan, also count as UMKM view)
        $umkm->increment('views');
        
        // Get similar layanan based on category (jenis_umkm) - KEMBALI KE VERSI SEBELUMNYA
        $similarLayanan = \App\Models\Layanan::whereHas('umkm', function($q) use ($umkm) {
                $q->where('jenis_umkm', $umkm->jenis_umkm);
            })
            ->where('id', '!=', $layanan->id) // Exclude current layanan
            ->with('umkm.user')
            ->limit(8)
            ->get()
            ->map(function($item) {
                $similarUmkm = $item->umkm->first();
                
                // Calculate rating for layanan
                $item->rating_layanan = Comment::where('layanan_id', $item->id)->avg('rating') ?? 0;
                
                // Calculate rating for UMKM
                if ($similarUmkm) {
                    $item->rating_umkm = Comment::where('umkm_id', $similarUmkm->id)
                        ->whereNull('layanan_id')
                        ->avg('rating') ?? 0;
                    $item->umkm_latitude = $similarUmkm->latitude;
                    $item->umkm_longitude = $similarUmkm->longitude;
                }
                
                return $item;
            });
        
        // Get comments for this layanan with user relation, ordered by newest first
        $commentsLayanan = Comment::where('layanan_id', $layanan->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate average rating and total comments for layanan
        $averageRatingLayanan = Comment::where('layanan_id', $layanan->id)->avg('rating') ?? 0;
        $totalCommentsLayanan = Comment::where('layanan_id', $layanan->id)->count();
        
        // Calculate average rating for UMKM (komentar untuk UMKM, bukan layanan)
        $averageRatingUmkm = Comment::where('umkm_id', $umkm->id)
            ->whereNull('layanan_id')
            ->avg('rating') ?? 0;
        
        // Get user's comment if authenticated
        $userCommentLayanan = null;
        if (Auth::check()) {
            $userCommentLayanan = Comment::where('layanan_id', $layanan->id)
                ->where('user_id', Auth::id())
                ->first();
        }
        
        // Address will be loaded via JavaScript from coordinates
        // No need to fetch from API on server-side
        
        return view('user.detail-layanan', compact('layanan', 'umkm', 'similarLayanan', 'commentsLayanan', 'averageRatingLayanan', 'totalCommentsLayanan', 'userCommentLayanan', 'averageRatingUmkm'));
    }

    public function toggleFavorite($id)
    {
        $user = Auth::user();
        $favorites = $user->favorites ?? [];
        $umkm = UMKM::find($id);
        
        if (!$umkm) {
            return response()->json(['success' => false, 'message' => 'UMKM tidak ditemukan']);
        }
        
        if (in_array($id, $favorites)) {
            // Remove from favorites
            $favorites = array_diff($favorites, [$id]);
            $message = 'UMKM dihapus dari favorit';
            
            // Update UMKM favorite data
            $umkmFavorites = $umkm->favorite ?? [];
            $umkmFavorites = array_diff($umkmFavorites, [$user->id]);
            $umkm->update([
                'favorite' => array_values($umkmFavorites),
                'favorit_count' => max(0, $umkm->favorit_count - 1)
            ]);
        } else {
            // Add to favorites
            $favorites[] = $id;
            $message = 'UMKM ditambahkan ke favorit';
            
            // Update UMKM favorite data
            $umkmFavorites = $umkm->favorite ?? [];
            if (!in_array($user->id, $umkmFavorites)) {
                $umkmFavorites[] = $user->id;
                $umkm->update([
                    'favorite' => array_values($umkmFavorites),
                    'favorit_count' => $umkm->favorit_count + 1
                ]);
            }
        }
        
        $user->update(['favorites' => array_values($favorites)]);
        
        $isFavorited = in_array($id, array_values($favorites));
        
        return response()->json([
            'success' => true, 
            'message' => $message,
            'is_favorited' => $isFavorited
        ]);
    }

    public function getRecommendations(Request $request)
    {
        $user = Auth::user();
        $userFavorites = $user->favorites ?? [];
        
        // Get metadata from user's favorite UMKM
        $favoriteUmkm = UMKM::whereIn('id', $userFavorites)->get();
        
        // Create metadata for AI recommendation
        $metadata = [
            'favorite_jenis_umkm' => $favoriteUmkm->pluck('jenis_umkm')->unique()->toArray(),
            'favorite_layanan' => $favoriteUmkm->flatMap->layanan->pluck('nama')->unique()->toArray(),
            'user_preferences' => $this->analyzeUserPreferences($favoriteUmkm),
        ];
        
        // Get AI recommendations based on metadata
        $recommendations = $this->getAIRecommendations($metadata, $request->user_lat ?? null, $request->user_lng ?? null);
        
        return response()->json($recommendations);
    }

    private function analyzeUserPreferences($favoriteUmkm)
    {
        $preferences = [
            'preferred_jenis' => $favoriteUmkm->groupBy('jenis_umkm')->map->count()->sortDesc()->keys()->first(),
            'preferred_layanan' => $favoriteUmkm->flatMap->layanan->groupBy('nama')->map->count()->sortDesc()->keys()->take(3)->toArray(),
            'price_range' => $this->calculatePriceRange($favoriteUmkm),
        ];
        
        return $preferences;
    }

    private function calculatePriceRange($favoriteUmkm)
    {
        $prices = $favoriteUmkm->flatMap->layanan->pluck('price');
        
        if ($prices->isEmpty()) {
            return ['min' => 0, 'max' => 1000000];
        }
        
        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    private function getAIRecommendations($metadata, $userLat = null, $userLng = null)
    {
        $query = UMKM::with('user', 'layanan');
        
        // Filter by preferred jenis UMKM
        if (!empty($metadata['favorite_jenis_umkm'])) {
            $query->whereIn('jenis_umkm', $metadata['favorite_jenis_umkm']);
        }
        
        // Filter by preferred layanan
        if (!empty($metadata['favorite_layanan'])) {
            $query->whereHas('layanan', function($q) use ($metadata) {
                $q->whereIn('nama', $metadata['favorite_layanan']);
            });
        }
        
        // Filter by price range
        if (isset($metadata['user_preferences']['price_range'])) {
            $priceRange = $metadata['user_preferences']['price_range'];
            $query->whereHas('layanan', function($q) use ($priceRange) {
                $q->whereBetween('price', [$priceRange['min'], $priceRange['max']]);
            });
        }
        
        // Filter by distance if user location is provided
        if ($userLat && $userLng) {
            $query->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) <= 15
            ", [$userLat, $userLng, $userLat]);
        }
        
        $recommendations = $query->limit(6)->get();
        
        return [
            'recommendations' => $recommendations,
            'metadata' => $metadata,
        ];
    }

    public function getDistance($umkmId, $userLat, $userLng)
    {
        $umkm = UMKM::findOrFail($umkmId);
        
        $distance = $this->calculateDistance(
            $userLat, $userLng,
            $umkm->latitude, $umkm->longitude
        );
        
        return response()->json(['distance' => round($distance, 2)]);
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    private function getLocationName($latitude, $longitude, $fallbackAddress = null)
    {
        $locationName = null;
        
        if (!$latitude || !$longitude) {
            return $fallbackAddress ?? 'Lokasi tidak tersedia';
        }
        
        // Try OpenStreetMap Nominatim (gratis)
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->retry(2, 100)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
                'zoom' => 10,
                'addressdetails' => 1,
                'accept-language' => 'id',
            ]);

            $data = $response->json();
            if (isset($data['address'])) {
                $address = $data['address'];
                
                // Extract city/district - cek berbagai kemungkinan field
                $city = $address['city'] 
                    ?? $address['town'] 
                    ?? $address['municipality']
                    ?? $address['county']
                    ?? $address['suburb']
                    ?? $address['city_district']
                    ?? $address['locality']
                    ?? null;
                
                $province = $address['state'] ?? $address['region'] ?? null;
                
                // Handle Jakarta khusus - cek berdasarkan koordinat atau nama
                $isJakartaArea = ($latitude >= -6.4 && $latitude <= -6.1 && 
                                 $longitude >= 106.7 && $longitude <= 106.95);
                
                if ($isJakartaArea || 
                    ($province && (stripos($province, 'Jakarta') !== false || 
                                   stripos($province, 'DKI') !== false)) ||
                    ($city && stripos($city, 'Jakarta') !== false)) {
                    
                    $province = 'DKI Jakarta';
                    
                    // Extract nama wilayah Jakarta (Jakarta Pusat, Jakarta Selatan, dll)
                    if (!$city || stripos($city, 'Jakarta') === false) {
                        // Coba berbagai field untuk mendapatkan nama wilayah Jakarta
                        $city = $address['suburb'] 
                            ?? $address['city_district']
                            ?? $address['locality']
                            ?? $address['municipality']
                            ?? 'Jakarta';
                        
                        // Jika masih tidak ada "Jakarta" di nama, tambahkan
                        if (stripos($city, 'Jakarta') === false) {
                            // Coba extract dari display_name atau formatted address
                            if (isset($data['display_name'])) {
                                $displayName = $data['display_name'];
                                // Extract "Jakarta Pusat", "Jakarta Selatan", dll dari display_name
                                if (preg_match('/Jakarta\s+(Pusat|Selatan|Utara|Barat|Timur|Kepulauan)/i', $displayName, $matches)) {
                                    $city = $matches[0];
                                } else {
                                    $city = 'Jakarta';
                                }
                            } else {
                                $city = 'Jakarta';
                            }
                        }
                    }
                }
                
                // Format: Kota/Kabupaten, Provinsi
                if ($city && $province) {
                    return $city . ', ' . $province;
                } elseif ($city) {
                    return $city;
                } elseif ($province) {
                    return $province;
                } elseif (isset($data['display_name'])) {
                    // Fallback to display name
                    return $data['display_name'];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Reverse geocoding failed: ' . $e->getMessage());
        }
        
        return $fallbackAddress ?? 'Lokasi tersedia';
    }

    /**
     * Store a new comment for UMKM
     */
    public function storeComment(Request $request, $umkmId)
    {
        // Only authenticated users can comment
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk memberikan komentar.'
            ], 401);
        }

        // Validate request
        $request->validate([
            'comment' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Check if user already commented on this UMKM
        $existingComment = Comment::where('umkm_id', $umkmId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingComment) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan komentar untuk UMKM ini. Silakan edit komentar Anda yang sudah ada.'
            ], 400);
        }

        // Create comment
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'umkm_id' => $umkmId,
            'comment' => $request->comment,
            'rating' => $request->rating,
        ]);

        // Load user relation
        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil ditambahkan.',
            'comment' => $comment
        ], 201);
    }

    /**
     * Update a comment
     */
    public function updateComment(Request $request, $commentId)
    {
        // Only authenticated users can update
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk mengedit komentar.'
            ], 401);
        }

        // Find comment
        $comment = Comment::findOrFail($commentId);

        // Check if user owns this comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit komentar ini.'
            ], 403);
        }

        // Validate request
        $request->validate([
            'comment' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Update comment
        $comment->update([
            'comment' => $request->comment,
            'rating' => $request->rating,
        ]);

        // Load user relation
        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil diperbarui.',
            'comment' => $comment
        ]);
    }

    /**
     * Delete a comment
     */
    public function deleteComment($commentId)
    {
        // Only authenticated users can delete
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk menghapus komentar.'
            ], 401);
        }

        // Find comment
        $comment = Comment::findOrFail($commentId);

        // Check if user owns this comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini.'
            ], 403);
        }

        // Delete comment
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil dihapus.'
        ]);
    }

    /**
     * Store a new comment for Layanan
     */
    public function storeCommentLayanan(Request $request, $layananId)
    {
        // Only authenticated users can comment
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk memberikan komentar.'
            ], 401);
        }

        // Validate request
        try {
            $validated = $request->validate([
                'comment' => 'required|string|min:10|max:1000',
                'rating' => 'required|integer|min:1|max:5',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        // Check if layanan exists and get associated UMKM
        $layanan = \App\Models\Layanan::with('umkm')->findOrFail($layananId);
        
        // Get the first UMKM associated with this layanan
        $umkm = $layanan->umkm->first();
        
        if (!$umkm) {
            return response()->json([
                'success' => false,
                'message' => 'UMKM tidak ditemukan untuk layanan ini.'
            ], 404);
        }

        // Check if user already commented on this layanan
        $existingComment = Comment::where('layanan_id', $layananId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingComment) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan komentar untuk layanan ini. Silakan edit komentar Anda yang sudah ada.'
            ], 400);
        }

        // Create comment with both layanan_id and umkm_id
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'umkm_id' => $umkm->id,
            'layanan_id' => $layananId,
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
        ]);

        // Load user relation
        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil ditambahkan.',
            'comment' => $comment
        ], 201);
    }

    /**
     * Update a comment for Layanan
     */
    public function updateCommentLayanan(Request $request, $commentId)
    {
        // Only authenticated users can update
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk mengedit komentar.'
            ], 401);
        }

        // Find comment
        $comment = Comment::findOrFail($commentId);

        // Check if comment is for layanan
        if (!$comment->layanan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Komentar ini bukan untuk layanan.'
            ], 400);
        }

        // Check if user owns this comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit komentar ini.'
            ], 403);
        }

        // Validate request
        try {
            $validated = $request->validate([
                'comment' => 'required|string|min:10|max:1000',
                'rating' => 'required|integer|min:1|max:5',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        // Update comment
        $comment->update([
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
        ]);

        // Load user relation
        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil diperbarui.',
            'comment' => $comment
        ]);
    }

    /**
     * Delete a comment for Layanan
     */
    public function deleteCommentLayanan($commentId)
    {
        // Only authenticated users can delete
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login untuk menghapus komentar.'
            ], 401);
        }

        // Find comment
        $comment = Comment::findOrFail($commentId);

        // Check if comment is for layanan
        if (!$comment->layanan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Komentar ini bukan untuk layanan.'
            ], 400);
        }

        // Check if user owns this comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini.'
            ], 403);
        }

        // Delete comment
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil dihapus.'
        ]);
    }

    /**
     * Show laporan bug page - hanya untuk user yang sudah login
     */
    public function laporan()
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu untuk membuat laporan.');
        }
        
        return view('user.laporan');
    }

    /**
     * Show history laporan user
     */
    public function historyLaporan()
    {
        // Hanya untuk user yang sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu untuk melihat history laporan.');
        }

        $user = Auth::user();
        
        // Get laporan berdasarkan user_id yang sedang login (pastikan tidak nabrak dengan akun lain)
        $reports = Report::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('user.history-laporan', compact('reports'));
    }

    /**
     * Submit laporan bug - disimpan ke database untuk dilihat admin
     */
    public function submitLaporan(Request $request)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login terlebih dahulu untuk membuat laporan.'
            ], 401);
        }

        $user = Auth::user();

        // Validate request (email tidak perlu divalidasi karena otomatis dari user yang login)
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:bug,fitur,pertanyaan,lainnya',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:10|max:2000',
        ]);

        // Simpan laporan ke database - email otomatis dari user yang login
        $report = Report::create([
            'user_id' => $user->id, // Simpan user_id untuk memastikan tidak nabrak dengan akun lain
            'nama' => $request->nama,
            'email' => $user->email, // Email otomatis dari user yang login, tidak bisa diubah
            'kategori' => $request->kategori,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'status' => 'pending', // Status awal: belum selesai
        ]);
        
        // Tentukan route history berdasarkan role user
        $historyRoute = $user->role === 'umkm' 
            ? route('umkm.history.laporan') 
            : route('user.history.laporan');
        
        return response()->json([
            'success' => true,
            'message' => 'Laporan Anda berhasil dikirim. Terima kasih atas feedback Anda!',
            'redirect' => $historyRoute
        ]);
    }

    /**
     * Show edit profile form for user
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('user.edit-profile', compact('user'));
    }

    /**
     * Update user's own profile (username, password only - email cannot be changed)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $request->name,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('user.edit.profile')->with('success', 'Profil berhasil diperbarui!');
    }

    public function account()
    {
        $user = Auth::user();
        
        // Get following list
        $following = $user->following ?? collect([]);
        
        // Get liked videos
        $likedVideos = $user->likedVideos()->with('umkm')->latest('pivot_created_at')->get();
        
        return view('user.account', compact('user', 'following', 'likedVideos'));
    }
}
