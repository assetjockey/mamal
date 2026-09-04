<?php

namespace App\Livewire\User\Dashboard;

use App\Models\AdCreative;
use App\Models\AdCopy;
use App\Models\Brand;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class UserDashboard extends Component
{
    public int $creditBalance = 0;
    public int $totalImages = 0;
    public int $totalVideos = 0;
    public int $totalCopies = 0;

    // 14-day burn sparkline
    public array $burnSeries = [];
    public float $burnLast7 = 0;
    public float $burnPrev7 = 0;
    public float $burnTrend = 0.0;       // % change 7d vs previous 7d
    public float $dailyAverage = 0;      // avg credits/day over last 14d
    public ?int $projectedDaysLeft = null;

    // Costs (for onboarding and clarity)
    public int $imageCost = 1;
    public int $videoCost = 5;
    public int $copyCost = 1;

    public function mount(CreditServiceInterface $creditService): void
    {
        $user = auth()->user();

        $this->creditBalance = $creditService->getBalance($user);

        // Per-unit credit rates for the configured default models.
        $features = \App\Models\FeatureSetting::first();
        $this->imageCost = $creditService->getRate('image', $features?->default_image_model);
        $this->videoCost = $creditService->getRate('video', $features?->default_video_model);
        $this->copyCost  = $creditService->getRate('copy', \App\Services\AdCopy\Support\EngineRegistry::defaultModel($features?->default_copy_engine ?? 'openai'));

        $this->totalImages = AdCreative::where('user_id', $user->id)->images()->completed()->count();
        $this->totalVideos = AdCreative::where('user_id', $user->id)->videos()->completed()->count();
        $this->totalCopies = AdCopy::where('user_id', $user->id)->where('status', 'completed')->count();

        $this->loadBurnInsights($user->id);
    }

    /**
     * Aggregates 14 days of credit usage from completed ad assets AND ad copies
     * into a normalized sparkline + trend metrics. Both generation surfaces draw
     * from the same balance, so they need to live on the same chart.
     */
    protected function loadBurnInsights(int $userId): void
    {
        $start = Carbon::now()->subDays(13)->startOfDay();

        // Image / video generations — use completed_at, that's when the credit was actually spent.
        $assetRows = AdCreative::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $start)
            ->selectRaw('DATE(completed_at) as day, SUM(credits) as credits')
            ->groupBy('day')
            ->pluck('credits', 'day')
            ->toArray();

        // Ad copies — no `completed_at` column, but rows are inserted as `completed`
        // by CopyGenerator at the moment of charge, so created_at is equivalent.
        $copyRows = AdCopy::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(credits) as credits')
            ->groupBy('day')
            ->pluck('credits', 'day')
            ->toArray();

        $series = [];
        $totals = [];

        for ($i = 0; $i < 14; $i++) {
            $date = Carbon::now()->subDays(13 - $i)->toDateString();
            $credits = (float) ($assetRows[$date] ?? 0) + (float) ($copyRows[$date] ?? 0);
            $series[] = [
                'date' => $date,
                'credits' => $credits,
                'label' => Carbon::parse($date)->format('M j'),
            ];
            $totals[] = $credits;
        }

        $this->burnSeries = $series;
        $this->burnLast7 = array_sum(array_slice($totals, 7, 7));
        $this->burnPrev7 = array_sum(array_slice($totals, 0, 7));
        $this->burnTrend = $this->burnPrev7 > 0
            ? round((($this->burnLast7 - $this->burnPrev7) / $this->burnPrev7) * 100, 1)
            : ($this->burnLast7 > 0 ? 100.0 : 0.0);

        $sum14 = array_sum($totals);
        $this->dailyAverage = round($sum14 / 14, 2);
        $this->projectedDaysLeft = $this->dailyAverage > 0
            ? (int) floor($this->creditBalance / $this->dailyAverage)
            : null;
    }

    public function getRecentAssetsProperty()
    {
        return AdCreative::where('user_id', auth()->id())
            ->completed()
            ->latest()
            ->take(8)
            ->get();
    }

    public function getInProgressProperty()
    {
        return AdCreative::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->take(4)
            ->get();
    }

    public function getBrandsProperty()
    {
        return Brand::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->latest()
            ->take(4)
            ->get();
    }

    public function getDefaultBrandProperty(): ?Brand
    {
        return Brand::where('user_id', auth()->id())
            ->where('is_default', true)
            ->first()
            ?? Brand::where('user_id', auth()->id())->latest()->first();
    }

    public function getRecentCopiesProperty()
    {
        return AdCopy::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Ready-made ad reference slots for the dashboard Preset Library.
     *
     * This is a fixed set of 30 shapes (the majority vertical / mobile-screen
     * tall). Each slot shows its image when a matching file exists in
     * /public/assets/presets/ — either named after the slot id
     * ({id}.{png,jpg,jpeg,webp,gif,svg}) or after its 1-based index
     * ({1..30}.{ext}); otherwise it renders as an empty bordered box with a
     * transparent background. Drop a file named after the slot id or its index
     * to fill it — no other config needed.
     *
     * Selecting a slot that has an image sends the user into the Image Studio
     * with that image pre-loaded as a reference image.
     */
    public function getPresetGalleryProperty(): array
    {
        // [width, height] ratios — interleaved so tall and wide slots mix
        // together rather than clustering. 18 vertical + 12 square/landscape.
        $shapes = [
            [9, 16],   [1, 1],    [4, 5],    [16, 9],   [2, 3],
            [3, 2],    [9, 19.5], [1, 1],    [3, 4],    [2, 2],
            [4, 5],    [4, 3],    [9, 20],   [1, 1],    [2, 3],
            [16, 9],   [5, 8],    [2, 1],    [3, 4],    [3, 1],
            [10, 16],  [1, 1],    [4, 5],    [9, 16],   [3, 5],
            [1, 1],    [4, 7],    [1, 2],    [9, 16],   [1, 2.1],
        ];

        $dir = public_path('assets/presets');

        return array_map(function (array $shape, int $i) use ($dir) {
            $id = 'preset-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);

            // Resolve an image file for this slot if one was dropped in.
            // Accept both the padded slot id ("preset-01.png") and the plain
            // 1-based index ("1.png") so dropped-in files don't need renaming.
            $url = null;
            $relPath = null;
            if (is_dir($dir)) {
                $basenames = [$id, (string) ($i + 1)];
                foreach ($basenames as $basename) {
                    foreach (['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'] as $ext) {
                        $candidate = $dir.DIRECTORY_SEPARATOR.$basename.'.'.$ext;
                        if (is_file($candidate)) {
                            $relPath = "assets/presets/{$basename}.{$ext}";
                            $url = asset($relPath);
                            break 2;
                        }
                    }
                }
            }

            [$w, $h] = $shape;

            // These are previews — keep boxes from getting absurdly tall.
            // Clamp the padding-bottom ratio so the tallest mobile shapes
            // still read as "vertical" without dominating the gallery.
            $ratioPct = min(round(($h / max($w, 0.01)) * 100, 2), 150);

            return [
                'id'       => $id,
                'url'      => $url,                                  // null → empty bordered box
                'path'     => $relPath,
                'ratioPct' => $ratioPct,                            // padding-bottom %
                'vertical' => $h > $w,
            ];
        }, $shapes, array_keys($shapes));
    }

    /**
     * Flattened preset library with extra UI metadata, ready for the gallery.
     */
    public function getPresetLibraryProperty(): array
    {
        $imagePresets = config('ai-studio.presets.image', []);
        $videoPresets = config('ai-studio.presets.video', []);

        $items = [];

        // Image — grouped
        $groupMeta = [
            'meta'        => ['group' => 'social',  'gradient' => 'from-indigo-500 to-slate-900'],
            'tiktok'      => ['group' => 'social',  'gradient' => 'from-zinc-900 to-slate-700'],
            'x_twitter'   => ['group' => 'social',  'gradient' => 'from-zinc-800 to-slate-900'],
            'linkedin'    => ['group' => 'social',  'gradient' => 'from-indigo-600 to-slate-900'],
            'pinterest'   => ['group' => 'social',  'gradient' => 'from-rose-600 to-slate-900'],
            'snap_reddit' => ['group' => 'social',  'gradient' => 'from-amber-500 to-slate-900'],
            'youtube'     => ['group' => 'video',   'gradient' => 'from-red-600 to-slate-900'],
            'google_ads'  => ['group' => 'display', 'gradient' => 'from-emerald-500 to-slate-900'],
            'web_display' => ['group' => 'display', 'gradient' => 'from-amber-500 to-slate-900'],
            'email'       => ['group' => 'display', 'gradient' => 'from-indigo-500 to-slate-900'],
        ];

        foreach ($imagePresets as $groupKey => $presets) {
            $meta = $groupMeta[$groupKey] ?? ['group' => 'social', 'gradient' => 'from-indigo-500 via-violet-500 to-fuchsia-500'];
            foreach ($presets as $p) {
                $items[] = array_merge($p, [
                    'type'     => 'image',
                    'group'    => $meta['group'],
                    'gradient' => $meta['gradient'],
                    'route'    => 'user.studio.images',
                    'aspect'   => $this->aspectClass($p['width'], $p['height']),
                ]);
            }
        }

        foreach ($videoPresets as $p) {
            $items[] = array_merge($p, [
                'type'     => 'video',
                'group'    => 'video',
                'gradient' => 'from-violet-600 via-purple-700 to-indigo-800',
                'route'    => 'user.studio.videos',
                'aspect'   => $this->aspectClass($p['width'], $p['height']),
            ]);
        }

        return $items;
    }

    /**
     * Returns a tailwind aspect class approximating the preset's ratio.
     * Keeps the gallery visually honest without shipping inline styles.
     */
    protected function aspectClass(int $width, int $height): string
    {
        $ratio = $width / max($height, 1);
        return match (true) {
            $ratio >= 2.5       => 'aspect-[8/1]',   // skyscraper/leaderboard-ish
            $ratio >= 1.6       => 'aspect-[16/9]',
            $ratio >= 1.1       => 'aspect-[4/3]',
            $ratio >= 0.9       => 'aspect-square',
            $ratio >= 0.7       => 'aspect-[4/5]',
            default             => 'aspect-[9/16]',
        };
    }

    public function getOnboardingProperty(): array
    {
        $user = auth()->user();
        $hasBrand = Brand::where('user_id', $user->id)->exists();
        $hasAsset = AdCreative::where('user_id', $user->id)->where('status', 'completed')->exists();
        $hasCopy  = AdCopy::where('user_id', $user->id)->where('status', 'completed')->exists();

        $steps = [
            [
                'key' => 'brand',
                'label' => __('Create your first brand'),
                'hint' => __('Lock in colors, logo, and voice for consistent ads.'),
                'done' => $hasBrand,
                'route' => route('user.brands.create'),
                'icon' => 'sparkles',
            ],
            [
                'key' => 'image',
                'label' => __('Generate your first image ad'),
                'hint' => __('Start from any preset — takes under a minute.'),
                'done' => AdCreative::where('user_id', $user->id)->images()->completed()->exists(),
                'route' => route('user.studio.images'),
                'icon' => 'image-plus',
            ],
            [
                'key' => 'copy',
                'label' => __('Write ad copy with AI'),
                'hint' => __('Pick a platform, we handle the rest.'),
                'done' => $hasCopy,
                'route' => route('user.copy.studio'),
                'icon' => 'pencil',
            ],
            [
                'key' => 'video',
                'label' => __('Try Video Studio'),
                'hint' => __('Turn your idea into a 5s video in one click.'),
                'done' => AdCreative::where('user_id', $user->id)->videos()->completed()->exists(),
                'route' => route('user.studio.videos'),
                'icon' => 'film',
            ],
        ];

        $done = count(array_filter($steps, fn ($s) => $s['done']));
        $total = count($steps);

        return [
            'steps' => $steps,
            'done' => $done,
            'total' => $total,
            'percent' => (int) round(($done / $total) * 100),
            'visible' => $done < $total,
        ];
    }

    public function render()
    {
        return view('livewire.user.dashboard.index');
    }
}
