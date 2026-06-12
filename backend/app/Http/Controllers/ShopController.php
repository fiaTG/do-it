<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopController extends Controller
{
    /**
     * Verfügbare Geschäfte (für die Auswahl in der Einkaufsliste).
     */
    public function index(): AnonymousResourceCollection
    {
        return ShopResource::collection(Shop::orderBy('name')->get());
    }
}
