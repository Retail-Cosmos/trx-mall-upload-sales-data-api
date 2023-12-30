<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use Illuminate\Console\Command;

class TrxMallUploadSalesDataApiCommand extends Command
{
    public $signature = 'trx-mall-upload-sales-data-api';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
