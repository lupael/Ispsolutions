<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportCustomersFromExcelCommand extends Command
{
    protected $signature = 'import:customers_from_excel';
    protected $description = 'Bulk import customers from Excel file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('This command will be implemented later.');
    }
}
