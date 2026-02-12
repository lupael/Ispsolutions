<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RestartFreeRadiusCommand extends Command
{
    protected $signature = 'restart:freeradius';
    protected $description = 'Restart FreeRADIUS service';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Restarting FreeRADIUS...');
        $process = new Process(['sudo', 'systemctl', 'restart', 'freeradius']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Failed to restart FreeRADIUS.');
            $this->error($process->getErrorOutput());
            return;
        }

        $this->info('FreeRADIUS restarted successfully.');
    }
}
