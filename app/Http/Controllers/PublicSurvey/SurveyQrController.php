<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicSurvey;

use App\Http\Controllers\Controller;
use App\Surveys\Exceptions\InvalidSurveyLinkException;
use App\Surveys\PublicSurveyGateway;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Response;

/**
 * Renders a QR code (pure-PHP SVG) that encodes ONLY the opaque public campaign URL — no
 * customer data, tenant secret, unprotected id, or health information. Deterministic for the
 * same URL and requires no external service (rule 32; Step 7 §17.4).
 */
final class SurveyQrController extends Controller
{
    public function __construct(private readonly PublicSurveyGateway $gateway) {}

    public function show(string $campaign): Response
    {
        // Only render a QR for a resolvable, active campaign link.
        try {
            $this->gateway->campaignView($campaign);
        } catch (InvalidSurveyLinkException) {
            abort(404);
        }

        $url = route('survey.public.campaign', ['campaign' => $campaign]);

        $renderer = new ImageRenderer(new RendererStyle(256, 1), new SvgImageBackEnd);
        $svg = (new Writer($renderer))->writeString($url);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
