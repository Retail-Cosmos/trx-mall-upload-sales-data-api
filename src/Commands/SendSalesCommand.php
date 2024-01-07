<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

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

    protected LoggerInterface $trxLogChannel;

    public function __construct()
    {
        parent::__construct();

        $this->trxLogChannel = Log::channel(config('trx_mall_upload_sales_data_api.log.channel'));
    }

    public function handle()
    {
        $message = 'start sending sales data to tangent api';
        $this->trxLogChannel->info($message);
        $this->info($message);
    }
}
