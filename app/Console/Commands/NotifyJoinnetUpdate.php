<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp;
use Storage;
use Telegram;

class NotifyJoinnetUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:joinnet-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '檢查 Joinnet 版本，有新版本發送通知到 telegram';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!Storage::disk('local')->exists('joinnet-latest-version')) {
            Storage::disk('local')->put('joinnet-latest-version', '');
        }

        $client = new GuzzleHttp\Client();

        $response = $client->request('GET', 'http://www.joinnet.tw/download_joinnet.php', ['allow_redirects' => false]);

        $file_locate = explode('/', $response->getHeaderLine('Location'));

        $file_version = $file_locate[count($file_locate) - 2];

        $last_version = Storage::disk('local')->get('joinnet-latest-version');

        if ($file_version != $last_version) {
            Telegram::sendMessage([
                'chat_id' => env('ETUTOR_GROUP'),
                'text' => 'Joinnet 版本更新為 ' . $file_version . '，請抽空更新 ^^' . PHP_EOL . PHP_EOL . '#joinnet'
            ]);

            Storage::disk('local')->put('joinnet-latest-version', $file_version);
        }

        $this->info($file_version);

        return $file_version;
    }
}
