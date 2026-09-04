# Overlay fonts

The video overlay pipeline (`App\Services\AiStudio\VideoOverlayService`)
uses FFmpeg's `drawtext` filter to burn user-typed headlines and CTAs
into generated videos. The bundled FFmpeg static binary in
`vendor/ffmpeg/` was built without system fonts, so the studio ships
its own.

## What's bundled

Three weights of [Inter](https://rsms.me/inter/), the same family used
across the studio UI. All three are required — `php artisan
ai-studio:check-ffmpeg` will report any missing file.

| File | Used by | Why this weight |
|---|---|---|
| `Inter-Black.ttf`     | Headline overlay (top band)  | Maximum visual impact at large display sizes |
| `Inter-ExtraBold.ttf` | CTA pill (bottom band)        | Heavy enough to read on a busy frame, slightly lighter than Black so the CTA doesn't overpower the headline |
| `Inter-Bold.ttf`      | Legacy / fallback default     | Used when an overlay element doesn't declare its own `font_path`, or when one of the heavier weights is missing |

Inter is licensed under the SIL Open Font License (OFL), which permits
free use, redistribution, and modification — including bundling with a
commercial product.

## Configuration

The font paths are wired through `config/ai-studio.php`:

```php
'overlay' => [
    'font_path' => resource_path('fonts/Inter-Bold.ttf'),

    'fonts' => [
        'bold'       => resource_path('fonts/Inter-Bold.ttf'),
        'extra_bold' => resource_path('fonts/Inter-ExtraBold.ttf'),
        'black'      => resource_path('fonts/Inter-Black.ttf'),
    ],

    'headline' => [
        'font_path' => resource_path('fonts/Inter-Black.ttf'),
        // ...
    ],

    'cta' => [
        'font_path' => resource_path('fonts/Inter-ExtraBold.ttf'),
        // ...
    ],
],
```

If `headline.font_path` or `cta.font_path` doesn't resolve, the service
silently falls back to `overlay.font_path` (Inter Bold). That way a
partial font drop still produces overlaid videos rather than failing.

## Using a different font family

You can swap in any TTF/OTF the FFmpeg build can read. CJK fonts work
too — the bundled binary includes `libharfbuzz` for proper shaping.

Two paths to override:

**1. Drop replacement files into this folder** with the same names
(`Inter-Black.ttf`, `Inter-ExtraBold.ttf`, `Inter-Bold.ttf`). The studio
will pick them up without any code change.

**2. Point at a different file via env:**

```env
AI_STUDIO_OVERLAY_FONT=/absolute/path/to/your-font.ttf
```

…or edit the `font_path` keys in `config/ai-studio.php` directly.

## Verification

After placing the fonts, run:

```bash
php artisan ai-studio:check-ffmpeg
```

Expected output for the font rows:

```
OK  overlay.font_path (legacy default) · .../Inter-Bold.ttf
OK  overlay.headline.font_path         · .../Inter-Black.ttf
OK  overlay.cta.font_path              · .../Inter-ExtraBold.ttf
```
