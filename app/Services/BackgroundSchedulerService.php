<?php

declare(strict_types=1);

namespace App\Services;

final class BackgroundSchedulerService
{
    private const FREQUENT_INTERVAL_SECONDS = 300;

    public function tick(): void
    {
        $this->runPendingConfirmationEmails();
        $this->runDailyTasks();
    }

    private function runPendingConfirmationEmails(): void
    {
        if ($this->frequentTasksRanRecently()) {
            return;
        }

        if (!$this->acquireLock('frequent.lock')) {
            return;
        }

        try {
            if ($this->frequentTasksRanRecently()) {
                return;
            }

            (new BookingConfirmationEmailService())->sendPendingBatch();
            $this->markFrequentRun();
        } finally {
            $this->releaseLock();
        }
    }

    private function runDailyTasks(): void
    {
        if ($this->alreadyRanToday()) {
            return;
        }

        if (!$this->acquireLock('daily.lock')) {
            return;
        }

        try {
            if ($this->alreadyRanToday()) {
                return;
            }

            (new BookingLifecycleService())->runDaily();
            $this->markRanToday();
        } finally {
            $this->releaseLock();
        }
    }

    private function schedulerDir(): string
    {
        $dir = base_path('storage/scheduler');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    private function lockPath(string $filename): string
    {
        return $this->schedulerDir() . '/' . $filename;
    }

    private function lastRunPath(): string
    {
        return $this->schedulerDir() . '/last-daily-run.txt';
    }

    private function lastFrequentRunPath(): string
    {
        return $this->schedulerDir() . '/last-frequent-run.txt';
    }

    private function acquireLock(string $filename): bool
    {
        $handle = @fopen($this->lockPath($filename), 'c+');
        if ($handle === false) {
            return false;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return false;
        }

        $this->lockHandle = $handle;

        return true;
    }

    /** @var resource|null */
    private $lockHandle = null;

    private function releaseLock(): void
    {
        if (!is_resource($this->lockHandle)) {
            return;
        }

        flock($this->lockHandle, LOCK_UN);
        fclose($this->lockHandle);
        $this->lockHandle = null;
    }

    private function alreadyRanToday(): bool
    {
        $path = $this->lastRunPath();
        if (!is_file($path)) {
            return false;
        }

        $stored = trim((string) file_get_contents($path));

        return $stored === date('Y-m-d');
    }

    private function markRanToday(): void
    {
        file_put_contents($this->lastRunPath(), date('Y-m-d'));
    }

    private function frequentTasksRanRecently(): bool
    {
        $path = $this->lastFrequentRunPath();
        if (!is_file($path)) {
            return false;
        }

        $last = (int) trim((string) file_get_contents($path));

        return $last > 0 && (time() - $last) < self::FREQUENT_INTERVAL_SECONDS;
    }

    private function markFrequentRun(): void
    {
        file_put_contents($this->lastFrequentRunPath(), (string) time());
    }
}
