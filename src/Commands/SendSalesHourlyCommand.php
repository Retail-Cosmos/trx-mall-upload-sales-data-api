<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use Illuminate\Console\Command;

class SendSalesHourlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tangent:send-sales-hourly';

    /**
     * The console command description.
     */
    protected $description = 'Send sales in hourly(00-23) format to Tangent API';

    /**
     * The console command description.
     *
     * @var string
     */
    public function handle()
    {
        //
    }
}
