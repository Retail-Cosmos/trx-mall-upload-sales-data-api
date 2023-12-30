<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use Illuminate\Console\Command;

class SendSalesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tangent:send-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sales in hourly(00-23) format to Tangent API';

    public function handle()
    {
        //
    }
}
