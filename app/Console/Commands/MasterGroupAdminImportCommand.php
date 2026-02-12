<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MasterGroupAdminImportCommand extends Command
{
    protected $signature = 'master:group_admin_import';
    protected $description = 'Import complete operator structure with customers';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('This command will be implemented later.');
    }
}
