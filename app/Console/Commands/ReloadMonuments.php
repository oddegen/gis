<?php

namespace App\Console\Commands;

use App\Models\Monument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReloadMonuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gis:reload-monuments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate the monuments table and reload the geojson';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $geojson = file_get_contents(resource_path('/geojson/monuments.geojson'));

        Monument::truncate();

        $features = collect(json_decode($geojson, true)['features'])->each(function ($feature) {
            Monument::create([
                'name' => $feature['properties']['name'],
                'image' => $feature['properties']['image'],
                'geom' => DB::raw("ST_GeomFromGeoJSON('".json_encode($feature['geometry'])."')"),
            ]);
        });

        $this->info($features->count().' monuments reloaded');

        return 0;
    }
}
