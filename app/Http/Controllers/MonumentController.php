<?php

namespace App\Http\Controllers;

use App\Models\Monument;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonumentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        Monument::selectRaw('id, name, image, ST_AsGeoJSON(geom) as geom')
            ->chunk(100, function ($monuments) use (&$geojson) {
                $monuments->each(function ($monument) use (&$geojson) {
                    $geojson['features'][] = [
                        'type' => 'Feature',
                        'properties' => [
                            'name' => $monument->name,
                            'image' => $monument->image,
                        ],
                        'geometry' => json_decode($monument->geom, true),
                    ];
                });
            });

        return Inertia::render('Dashboard', [
            'geojson' => json_encode($geojson),
        ]);
    }
}
