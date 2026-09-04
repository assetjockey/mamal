<?php

namespace Modules\AppAIQRCodes\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppFiles\Support\FileManager;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use RuntimeException;
use Throwable;

class AIQRCodeImageService
{
    public function __construct(
        protected OptionStore $options,
        protected FileManager $files,
    ) {}

    public function generateForUser(User $user, string $prompt, string $style = 'cinematic', ?string $qrValue = null): array
    {
        if ((string) $this->options->get('ai_qr_status', '1') !== '1') {
            throw new RuntimeException(__('AI QR Code generation is currently disabled.'));
        }

        $creditUser = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;

        if ($creditUser->plan && ! $creditUser->hasPlanFeature('ai_qr_codes')) {
            throw new RuntimeException(__('Your current plan does not allow AI QR Codes.'));
        }

        if (function_exists('credit_service')) {
            credit_service()->ensureCanConsume($creditUser, 'ai_qr_generate');
        }

        $provider = 'replicate';
        $model = $this->replicateModelSlug();
        $startedAt = microtime(true);
        $workspaceOwner = User::query()->findOrFail(TeamWorkspaceAccess::workspaceOwnerUserId($user));

        try {
            $image = $this->generateWithReplicate($prompt, $style, (string) $qrValue);
            $loggedModel = (string) ($image['model'] ?? $model);

            $file = $this->files->storeBinaryFile(
                owner: $workspaceOwner,
                originalName: (($image['complete_qr'] ?? false) ? 'ai-qr-artwork-' : 'ai-qr-background-').now()->format('YmdHis').'.'.$image['extension'],
                mimeType: $image['mime_type'],
                contents: $image['contents'],
                note: $image['prompt'],
            );

            if (function_exists('consume_credits')) {
                consume_credits($creditUser, 'ai_qr_generate', [
                    'feature' => 'ai-qr-codes.generate',
                    'metadata' => [
                        'provider' => $provider,
                        'model' => $loggedModel,
                        'style' => $style,
                        'file_id' => $file->id,
                        'complete_qr' => $image['complete_qr'] ?? false,
                    ],
                ]);
            }

            $this->logUsage($provider, $loggedModel, 'success', $startedAt);

            return [
                'file' => $file,
                'provider' => $provider,
                'model' => $loggedModel,
                'prompt' => $image['prompt'],
                'complete_qr' => $image['complete_qr'] ?? false,
            ];
        } catch (Throwable $exception) {
            $this->logUsage($provider, $model, 'error', $startedAt, $exception->getMessage());

            throw $exception;
        }
    }

    protected function generateWithOpenAi(string $prompt, string $style, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $payload = [
            'model' => $model,
            'prompt' => $this->imagePrompt($prompt, $style),
            'size' => '1024x1024',
        ];

        if (str_starts_with($model, 'dall-e-')) {
            $payload['response_format'] = 'b64_json';
        }

        $response = Http::timeout(180)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/images/generations', $payload);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI image generation request failed.')));
        }

        $b64 = (string) data_get($response->json(), 'data.0.b64_json', '');

        if ($b64 === '') {
            $b64 = (string) data_get($response->json(), 'data.0.image_base64', '');
        }

        if ($b64 === '') {
            throw new RuntimeException(__('AI image generation returned no image data.'));
        }

        $contents = base64_decode($b64, true);

        if ($contents === false) {
            throw new RuntimeException(__('AI image generation returned invalid image data.'));
        }

        return [
            'contents' => $contents,
            'mime_type' => 'image/png',
            'extension' => 'png',
            'prompt' => $payload['prompt'],
        ];
    }

    protected function generateWithGemini(string $prompt, string $style, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_gemini_api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException(__('Gemini API key is missing.'));
        }

        $imagePrompt = $this->imagePrompt($prompt, $style);
        $response = Http::timeout(180)
            ->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [[
                        'text' => $imagePrompt,
                    ]],
                ]],
                'generationConfig' => [
                    'responseModalities' => ['TEXT', 'IMAGE'],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI image generation request failed.')));
        }

        $parts = (array) data_get($response->json(), 'candidates.0.content.parts', []);
        $inlineData = collect($parts)->pluck('inlineData')->filter()->first();
        $b64 = (string) data_get($inlineData, 'data', '');
        $mimeType = (string) data_get($inlineData, 'mimeType', 'image/png');

        if ($b64 === '') {
            throw new RuntimeException(__('AI image generation returned no image data.'));
        }

        $contents = base64_decode($b64, true);

        if ($contents === false) {
            throw new RuntimeException(__('AI image generation returned invalid image data.'));
        }

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
            'extension' => str_contains($mimeType, 'webp') ? 'webp' : 'png',
            'prompt' => $imagePrompt,
        ];
    }

    protected function generateWithReplicate(string $prompt, string $style, string $qrValue): array
    {
        $apiKey = trim((string) $this->options->get('ai_replicate_api_key', ''));
        $configuredSlug = trim((string) $this->options->get('ai_replicate_model_slug', 'zylim0702/qr_code_controlnet'));
        $slug = $this->replicateModelSlug();
        $version = trim((string) $this->options->get('ai_replicate_version_override', ''));

        if ($apiKey === '') {
            throw new RuntimeException(__('Replicate API key is missing.'));
        }

        if ($slug === '' || ! str_contains($slug, '/')) {
            throw new RuntimeException(__('Replicate model slug is missing or invalid.'));
        }

        if (trim($qrValue) === '') {
            throw new RuntimeException(__('QR code URL is missing.'));
        }

        if (strtolower($configuredSlug) === 'qr2ai/mb' && strtolower($slug) === 'zylim0702/qr_code_controlnet') {
            $version = '';
        }

        $imagePrompt = $this->stableDiffusionPrompt($prompt, $style);
        $version = $version !== '' ? $version : $this->resolveReplicateVersion($apiKey, $slug);
        $payload = [
            'version' => $version,
            'input' => $this->replicateInput($imagePrompt, $qrValue, $slug),
        ];

        $response = Http::timeout(240)
            ->withToken($apiKey)
            ->acceptJson()
            ->withHeaders([
                'Prefer' => 'wait=60',
                'Cancel-After' => '5m',
            ])
            ->post('https://api.replicate.com/v1/predictions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('Replicate image generation request failed.')));
        }

        $prediction = $response->json();
        $prediction = $this->waitForReplicatePrediction($prediction, $apiKey);
        $status = (string) data_get($prediction, 'status', '');

        if ($status !== 'succeeded') {
            $error = trim((string) data_get($prediction, 'error', ''));
            throw new RuntimeException($error !== '' ? $error : __('Replicate image generation did not finish successfully.'));
        }

        $output = data_get($prediction, 'output');
        $contents = $this->replicateOutputContents($output);

        return [
            'contents' => $contents,
            'mime_type' => 'image/png',
            'extension' => 'png',
            'prompt' => $imagePrompt,
            'model' => $slug.':'.$version,
            'complete_qr' => true,
        ];
    }

    protected function imagePrompt(string $prompt, string $style): string
    {
        $brief = $this->visualBrief($prompt);
        $styleDirection = match ($style) {
            'nature' => 'Use natural light, organic textures, premium outdoor atmosphere, and clean depth.',
            'city' => 'Use refined urban architecture, night lights or sunrise glow, and polished commercial mood.',
            'product' => 'Use product-advertising lighting, elegant surfaces, and a premium brand campaign look.',
            'minimal' => 'Use clean abstract composition, subtle gradients, and generous negative space.',
            default => 'Use cinematic lighting, rich atmosphere, and a high-end campaign poster look.',
        };
        $variation = $this->variationDirection();

        return trim(implode("\n\n", [
            'Create a premium square background artwork for a digital marketing campaign.',
            $styleDirection,
            $variation,
            'Feature the user visual brief clearly in the scene, especially around the edges and background.',
            'Leave a clean central safe area where a graphic card can be overlaid later, but keep the requested subject visible around that safe area.',
            'Do not draw codes, matrix patterns, barcodes, text, letters, logos, watermarks, UI screens, mockup frames, or scanner marks.',
            'The image must look like a polished marketing visual, not a random gradient.',
            'Make this render a fresh variation. Do not repeat the exact composition of previous generations.',
            'Variation id: '.now()->format('YmdHis').'-'.Str::random(8),
            'User visual brief: '.$brief,
        ]));
    }

    protected function stableDiffusionPrompt(string $prompt, string $style): string
    {
        $brief = $this->visualBrief($prompt);
        $styleDirection = match ($style) {
            'nature' => 'natural scenery, organic textures, warm sunlight, premium outdoor atmosphere',
            'city' => 'refined urban architecture, cinematic city lights, polished commercial mood',
            'product' => 'premium product advertising lighting, elegant reflective surfaces, brand campaign look',
            'minimal' => 'clean minimal composition, crisp contrast, refined abstract forms',
            default => 'cinematic lighting, rich atmosphere, high-end campaign poster look',
        };

        return trim(implode(', ', [
            'scannable artistic QR code',
            'the QR structure must remain clear and readable',
            'the user requested subject must be visible in the QR artwork',
            $styleDirection,
            $brief,
            'sharp square composition',
            'high contrast',
            'professional marketing artwork',
        ]));
    }

    protected function visualBrief(string $prompt): string
    {
        $brief = trim(preg_replace('/\s+/u', ' ', $prompt) ?: '');
        $lower = Str::lower($brief);
        $commandLike = preg_match('/\b(create|make|generate|build|tao|tạo)\b.*\b(qr|qrcode|qr code)\b/u', $lower) === 1
            || preg_match('/\b(qr|qrcode|qr code)\b.*\b(for|cho)\b/u', $lower) === 1;

        if ($brief === '' || $commandLike) {
            return 'Premium SaaS website launch campaign background, modern digital product aesthetic, elegant blue and violet lighting, subtle browser-window inspired shapes, refined glass surfaces, clean central negative space, professional technology brand look.';
        }

        return trim(preg_replace('/\b(qr|qrcode|qr code|barcode|mã qr|ma qr)\b/iu', 'graphic campaign', $brief));
    }

    protected function variationDirection(): string
    {
        $directions = [
            'Composition variation: low-angle cinematic scene with strong foreground depth and a bright atmospheric center.',
            'Composition variation: symmetrical premium poster with soft negative space in the middle and detailed edges.',
            'Composition variation: close-up macro lighting, elegant surfaces, soft glow, and abstract depth.',
            'Composition variation: wide environmental scene, layered background, and dramatic light path toward the center.',
            'Composition variation: modern editorial campaign look with refined color contrast and clean central focus.',
            'Composition variation: immersive 3D advertising backdrop with realistic reflections and subtle motion energy.',
        ];

        return $directions[array_rand($directions)];
    }

    protected function qrControlImageBase64(string $value): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException(__('PHP GD extension is required to generate the Replicate QR control image.'));
        }

        $matrix = Encoder::encode($value, ErrorCorrectionLevel::H(), 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $size = 1024;
        $modules = $matrixSize + ($margin * 2);
        $unit = (int) floor($size / $modules);
        $qrSize = $unit * $modules;
        $offset = (int) floor(($size - $qrSize) / 2);
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y)) {
                    continue;
                }

                imagefilledrectangle(
                    $image,
                    $offset + (($x + $margin) * $unit),
                    $offset + (($y + $margin) * $unit),
                    $offset + (($x + $margin + 1) * $unit) - 1,
                    $offset + (($y + $margin + 1) * $unit) - 1,
                    $black
                );
            }
        }

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);

        if (! is_string($contents) || $contents === '') {
            throw new RuntimeException(__('Could not generate the Replicate QR control image.'));
        }

        return base64_encode($contents);
    }

    protected function replicateInput(string $prompt, string $qrValue, string $slug): array
    {
        $template = trim((string) $this->options->get('ai_replicate_input_template', ''));
        $normalizedSlug = strtolower(trim($slug));

        if ($template === '') {
            $template = json_encode($this->defaultReplicateInputForSlug($normalizedSlug));
        }

        $input = json_decode($template, true);

        if (! is_array($input)) {
            throw new RuntimeException(__('Replicate input template must be a valid JSON object.'));
        }

        if ($normalizedSlug === 'zylim0702/qr_code_controlnet' && ! array_key_exists('url', $input)) {
            $input = $this->defaultReplicateInputForSlug($normalizedSlug);
        }

        if ($normalizedSlug === 'zylim0702/qr_code_controlnet') {
            unset($input['image'], $input['qr_image'], $input['qr_code_image'], $input['qr_code_content']);
        }

        if ($normalizedSlug === 'qr2ai/mb') {
            $input = array_merge([
                'image' => '{{qr_image}}',
                'prompt' => '{{prompt}}',
                'suffix_prompt' => 'highly detailed, sharp focus, premium visual style, scannable QR structure, clean contrast',
                'negative_prompt' => '{{negative_prompt}}',
                'width' => 1024,
                'height' => 1024,
                'generate_square' => true,
                'num_outputs' => 1,
                'num_inference_steps' => 35,
                'guidance_scale' => 7.5,
                'adapter_conditioning_scale' => 0.9,
                'condition_scale' => 0.97,
                'seed' => 0,
                'sampler' => 'Euler a',
                'use_canny' => false,
            ], $input);
            unset($input['qr_image']);
        }

        return $this->replaceReplicatePlaceholders($input, [
            '{{prompt}}' => $prompt,
            '{{negative_prompt}}' => 'blurry, low contrast, unreadable qr code, broken qr code, text, letters, logo, watermark, barcode',
            '{{qr_image}}' => 'data:image/png;base64,'.$this->qrControlImageBase64($qrValue),
            '{{qr_value}}' => $qrValue,
            '{{width}}' => 1024,
            '{{height}}' => 1024,
        ]);
    }

    protected function replicateModelSlug(): string
    {
        $slug = trim((string) $this->options->get('ai_replicate_model_slug', 'zylim0702/qr_code_controlnet'));

        if ($slug === '' || strtolower($slug) === 'qr2ai/mb') {
            return 'zylim0702/qr_code_controlnet';
        }

        return $slug;
    }

    protected function defaultReplicateInputForSlug(string $normalizedSlug): array
    {
        if ($normalizedSlug === 'zylim0702/qr_code_controlnet') {
            return [
                'url' => '{{qr_value}}',
                'prompt' => '{{prompt}}',
                'qr_conditioning_scale' => 1.35,
                'num_outputs' => 1,
                'image_resolution' => 768,
                'scheduler' => 'DDIM',
                'num_inference_steps' => 28,
                'guidance_scale' => 9,
                'eta' => 0,
                'negative_prompt' => '{{negative_prompt}}',
                'guess_mode' => false,
                'disable_safety_check' => false,
            ];
        }

        return [
            'image' => '{{qr_image}}',
            'prompt' => '{{prompt}}',
            'suffix_prompt' => 'highly detailed, sharp focus, premium visual style, scannable QR structure, clean contrast',
            'negative_prompt' => '{{negative_prompt}}',
            'width' => 1024,
            'height' => 1024,
            'generate_square' => true,
            'num_outputs' => 1,
            'num_inference_steps' => 35,
            'guidance_scale' => 7.5,
            'adapter_conditioning_scale' => 0.9,
            'condition_scale' => 0.97,
            'seed' => 0,
            'sampler' => 'Euler a',
            'use_canny' => false,
        ];
    }

    protected function replaceReplicatePlaceholders(mixed $value, array $replacements): mixed
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn (mixed $item): mixed => $this->replaceReplicatePlaceholders($item, $replacements))
                ->all();
        }

        if (! is_string($value)) {
            return $value;
        }

        if (array_key_exists($value, $replacements)) {
            return $replacements[$value];
        }

        return str_replace(array_keys($replacements), array_map('strval', array_values($replacements)), $value);
    }

    protected function waitForReplicatePrediction(array $prediction, string $apiKey): array
    {
        $status = (string) data_get($prediction, 'status', '');
        $getUrl = (string) data_get($prediction, 'urls.get', '');
        $deadline = time() + 240;

        while (in_array($status, ['starting', 'processing'], true) && $getUrl !== '' && time() < $deadline) {
            sleep(2);

            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->acceptJson()
                ->get($getUrl);

            if (! $response->successful()) {
                throw new RuntimeException($this->responseErrorMessage($response, __('Could not fetch Replicate prediction status.')));
            }

            $prediction = $response->json();
            $status = (string) data_get($prediction, 'status', '');
        }

        return $prediction;
    }

    protected function resolveReplicateVersion(string $apiKey, string $slug): string
    {
        $response = Http::timeout(30)
            ->withToken($apiKey)
            ->acceptJson()
            ->get('https://api.replicate.com/v1/models/'.$slug.'/versions');

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('Could not fetch Replicate model versions.')));
        }

        $version = (string) data_get($response->json(), 'results.0.id', '');

        if ($version === '') {
            throw new RuntimeException(__('This Replicate model has no enabled API version. Choose another model or enter a version override.'));
        }

        return $version;
    }

    protected function replicateOutputContents(mixed $output): string
    {
        $url = $this->replicateOutputUrl($output);

        if ($url === '') {
            throw new RuntimeException(__('Replicate returned no image output.'));
        }

        if (str_starts_with($url, 'data:image/')) {
            $b64 = preg_replace('/^data:image\/\w+;base64,/', '', $url) ?: '';
            $contents = base64_decode($b64, true);

            if ($contents === false || $contents === '') {
                throw new RuntimeException(__('Replicate returned invalid image data.'));
            }

            return $contents;
        }

        $response = Http::timeout(120)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException(__('Could not download Replicate image output.'));
        }

        return $response->body();
    }

    protected function replicateOutputUrl(mixed $output): string
    {
        if (is_string($output)) {
            return $output;
        }

        if (is_array($output)) {
            foreach (['image', 'output', 'url'] as $key) {
                $value = data_get($output, $key);

                if (is_string($value) && $value !== '') {
                    return $value;
                }
            }

            foreach ($output as $value) {
                $url = $this->replicateOutputUrl($value);

                if ($url !== '') {
                    return $url;
                }
            }
        }

        return '';
    }

    protected function responseErrorMessage(Response $response, string $fallback): string
    {
        $message = trim((string) data_get($response->json(), 'error.message', ''));

        if ($message === '') {
            $message = trim((string) data_get($response->json(), 'message', ''));
        }

        if ($message === '') {
            $message = trim((string) $response->body());
        }

        return $message === '' ? $fallback : Str::limit($message, 280);
    }

    protected function logUsage(string $provider, string $model, string $status, float $startedAt, ?string $error = null): void
    {
        if (! function_exists('log_ai_usage')) {
            return;
        }

        log_ai_usage(array_filter([
            'provider' => $provider,
            'capability' => 'image',
            'model' => $model,
            'feature' => 'ai-qr-codes.generate',
            'status' => $status,
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'error_message' => $error,
        ]));
    }
}
