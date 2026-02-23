<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Services\TmdbService;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function show(int $id): Response
    {
        $data = $this->tmdb->getPersonDetails($id);

        abort_if(empty($data['id']), 404);

        return Inertia::render('Person', [
            'person' => $this->resource->formatPerson($data),
        ]);
    }
}
