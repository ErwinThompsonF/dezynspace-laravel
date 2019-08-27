<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Countries\Package\Countries;

class CountryController extends Controller
{
    public function Countries()
    {
        $countries = Countries::all()->sortBy('name')->pluck('name.common');
        return response()->json($countries);
    }

    public function CountriesAsia()
    {
        $countries = Countries::where('geo.continent.AS', 'Asia')->sortBy('name')->pluck('name.common');
        return response()->json($countries);
    }

    public function City(Request $request)
    {
        $cities = Countries::where('name.common', $request->city)
        ->first()
        ->hydrateStates()
        ->states
        ->sortBy('name')
        ->pluck('name', 'postal');
        return response()->json($cities);

    }
}
