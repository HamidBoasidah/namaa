<?php

namespace App\Http\Controllers;

use App\Services\LandingPageService;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    public function __construct(
        private LandingPageService $landingPageService
    ) {}

    public function index(): Response
    {
        $data = $this->landingPageService->getLandingPageData('home');

        if (!$data) {
            abort(404);
        }

        return Inertia::render('Landing/Index', [
            'page' => $data['page'],
            'sections' => $data['sections'],
        ]);
    }

    public function show(string $slug): Response
    {
        $data = $this->landingPageService->getLandingPageData($slug);

        if (!$data) {
            abort(404);
        }

        return Inertia::render('Landing/Index', [
            'page' => $data['page'],
            'sections' => $data['sections'],
        ]);
    }
}
