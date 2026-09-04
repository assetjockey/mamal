<?php

namespace App\Console\Commands\AiStudio;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Verifies that the bundled FFmpeg binary executes on the current host
 * and that all the prerequisites for the video overlay pipeline are met.
 *
 *   php artisan ai-studio:check-ffmpeg
 *
 * Run this once on each new deployment. It catches the typical issues:
 *   - exec / shell_exec disabled
 *   - bundled binary missing or not executable
 *   - bundled binary built for a wrong architecture (32-bit on 64-bit host)
 *   - missing TTF font for drawtext
 *   - drawtext filter unavailable in the FFmpeg build
 */
class CheckFfmpegCommand extends Command
{
    protected $signature = 'ai-studio:check-ffmpeg';

    protected $description = 'Verify the bundled FFmpeg binary and the video overlay pipeline are ready.';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('AI Studio · FFmpeg environment check');
        $this->newLine();

        $allOk = true;

        // 1. exec / shell_exec available
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        $this->row(
            'PHP exec()',
            ! in_array('exec', $disabled, true) && function_exists('exec'),
            in_array('exec', $disabled, true) ? 'disabled in php.ini' : 'enabled',
        ) || $allOk = false;

        $this->row(
            'PHP shell_exec()',
            ! in_array('shell_exec', $disabled, true) && function_exists('shell_exec'),
            in_array('shell_exec', $disabled, true) ? 'disabled in php.ini' : 'enabled',
        ) || $allOk = false;

        $this->row(
            'PHP proc_open() (Symfony Process)',
            ! in_array('proc_open', $disabled, true) && function_exists('proc_open'),
            in_array('proc_open', $disabled, true) ? 'disabled in php.ini' : 'enabled',
        ) || $allOk = false;

        // 2. Bundled FFmpeg binary
        $ffmpegPath = (string) config('laravel-ffmpeg.ffmpeg.binaries');
        $ffprobePath = (string) config('laravel-ffmpeg.ffprobe.binaries');

        $this->row('FFmpeg path', file_exists($ffmpegPath), $ffmpegPath) || $allOk = false;
        $this->row('FFprobe path', file_exists($ffprobePath), $ffprobePath) || $allOk = false;

        if (file_exists($ffmpegPath)) {
            // Make sure the binary is executable on Linux/macOS. No-op on
            // Windows where the .exe extension drives execution.
            if (PHP_OS_FAMILY !== 'Windows' && ! is_executable($ffmpegPath)) {
                @chmod($ffmpegPath, 0755);
                @chmod($ffprobePath, 0755);
            }

            // 3. Run -version to confirm it actually executes on this host.
            $version = $this->runProcess([$ffmpegPath, '-version']);

            if ($version['ok']) {
                $line = strtok($version['stdout'], "\n");
                $this->row('FFmpeg executes', true, $line ?: 'ok');

                // 4. Confirm drawtext filter is available — non-negotiable
                //    for the overlay pipeline.
                $filters = $this->runProcess([$ffmpegPath, '-hide_banner', '-filters']);
                $hasDrawtext = $filters['ok'] && str_contains($filters['stdout'], 'drawtext');
                $this->row(
                    'drawtext filter',
                    $hasDrawtext,
                    $hasDrawtext ? 'available' : 'NOT available — overlay will fail',
                ) || $allOk = false;
            } else {
                $this->row('FFmpeg executes', false, trim($version['stderr']) ?: 'failed to launch');
                $allOk = false;
            }
        }

        // 5. Fonts for drawtext — verify every weight that any overlay
        //    element may need. The headline uses Inter Black, the CTA uses
        //    Inter ExtraBold, and the legacy default is Inter Bold; all three
        //    have to resolve for the pipeline to be considered ready.
        $fontPaths = [
            'overlay.font_path (legacy default)' => (string) config('ai-studio.overlay.font_path'),
            'overlay.headline.font_path' => (string) config('ai-studio.overlay.headline.font_path'),
            'overlay.cta.font_path' => (string) config('ai-studio.overlay.cta.font_path'),
        ];

        foreach ($fontPaths as $label => $path) {
            if ($path === '') {
                $this->row($label, false, 'not configured');
                $allOk = false;

                continue;
            }

            $exists = file_exists($path);
            $this->row(
                $label,
                $exists,
                $exists ? $path : "missing — drop the TTF at {$path}",
            ) || $allOk = false;
        }

        // 6. results disk reachable
        $disk = config('filesystems.disks.results');
        $this->row(
            'Results disk configured',
            is_array($disk),
            $disk ? "type={$disk['driver']}" : 'not configured in filesystems.php',
        ) || $allOk = false;

        // 7. Overlay master switch
        $enabled = (bool) config('ai-studio.overlay.enabled', true);
        $this->row(
            'Overlay enabled',
            $enabled,
            $enabled ? 'on (config/ai-studio.php → overlay.enabled)' : 'OFF — set AI_STUDIO_OVERLAY_ENABLED=true to enable',
        );

        $this->newLine();

        if ($allOk) {
            $this->components->info('All checks passed. Video overlay pipeline is ready.');

            return self::SUCCESS;
        }

        $this->components->error('One or more checks failed. Fix the items above before generating videos with overlays.');

        return self::FAILURE;
    }

    /**
     * Print a status line in the same Laravel-stylised format as the
     * built-in `route:list`-style "task" output.
     */
    protected function row(string $label, bool $ok, string $detail = ''): bool
    {
        $status = $ok ? '<fg=green;options=bold>OK</>' : '<fg=red;options=bold>FAIL</>';
        $line = '  '.$status.'  '.$label;

        if ($detail !== '') {
            $line .= ' <fg=gray>· '.$detail.'</>';
        }

        $this->line($line);

        return $ok;
    }

    /** Run a process and return [ok, stdout, stderr]. */
    protected function runProcess(array $cmd): array
    {
        try {
            $p = new Process($cmd, timeout: 10);
            $p->run();

            return [
                'ok' => $p->isSuccessful(),
                'stdout' => $p->getOutput(),
                'stderr' => $p->getErrorOutput(),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'stdout' => '', 'stderr' => $e->getMessage()];
        }
    }
}
