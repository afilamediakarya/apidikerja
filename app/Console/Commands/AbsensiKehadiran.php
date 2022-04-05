<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
class AbsensiKehadiran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absen:kehadiran';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Absen Kehadiran';

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
     * @return int
     */
    public function handle()
    {
        $data =  DB::table('tb_absen')
              ->where('id', 1)
              ->update(['status' => 'alpa']);
        info('tes cron'); 
        return 0;
    }
}
