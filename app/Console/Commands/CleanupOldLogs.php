<?php

namespace App\Console\Commands;

use App\Models\ActivationLog;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldLogs extends Command
{
    protected $signature = 'logs:cleanup {--days=30 : Delete logs older than X days}';
    protected $description = 'Cleanup old logs from database';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $date = now()->subDays($days);
        
        $this->info("Cleaning up logs older than {$days} days...");
        
        $activationLogsDeleted = ActivationLog::where('created_at', '<', $date)->delete();
        $this->info("Deleted {$activationLogsDeleted} old activation logs.");
        
        $auditLogsDeleted = AuditLog::where('created_at', '<', $date)->delete();
        $this->info("Deleted {$auditLogsDeleted} old audit logs.");
        
        $this->cleanupOldSessions($days);
        
        $this->info('Log cleanup completed!');
        
        Log::info('Old logs cleanup completed', [
            'days' => $days,
            'activation_logs_deleted' => $activationLogsDeleted,
            'audit_logs_deleted' => $auditLogsDeleted,
        ]);
    }
    
    private function cleanupOldSessions(int $days): void
    {
        if (config('session.driver') === 'file') {
            $sessionPath = storage_path('framework/sessions');
            if (is_dir($sessionPath)) {
                $files = glob($sessionPath . '/*');
                $cutoff = time() - ($days * 24 * 60 * 60);
                $deleted = 0;
                
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoff) {
                        unlink($file);
                        $deleted++;
                    }
                }
                
                $this->info("Deleted {$deleted} old session files.");
            }
        }
    }
}