<?php

namespace App\Console\Commands;

use App\Services\Contracts\ClientTrafficServiceInterface;
use Illuminate\Console\Command;

class TransferClientToOtherInboundCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbound:transfer {uuid} {inboundId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer client to other inbounds';



    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var ClientTrafficServiceInterface $clientTrafficService */
        $clientTrafficService = resolve(ClientTrafficServiceInterface::class);
        $clientTrafficService->transferUser($this->argument('uuid'),$this->argument('inboundId'));
    }
}
