<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\absen;
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
       
        $dt = date('Y-m-d');
        $absen = '';
        $data = DB::table('tb_pegawai')->whereNotExists(function($query){
            $query->select(DB::raw(1))->from('tb_absen')->whereColumn('tb_absen.id_pegawai','tb_pegawai.id');
        })->get();

        foreach ($data as $key => $value) {
            for ($i=0; $i < 2; $i++) { 
                $absen = new absen();
                $absen->id_pegawai = $value->id;
                $absen->tanggal_absen = $dt;
                $absen->status = 'alpa';
                if ($i == 0) {
                    $absen->jenis = 'checkin';
                    $absen->waktu_absen = '08:00:00';
                }else{
                    $absen->jenis = 'checkout';
                    $absen->waktu_absen = '17:00:00';
                }
                $absen->location_auth = 'valid';
                $absen->face_auth = 'valid';
                $absen->tahun = '2022';
                $absen->save();   
            }
        }

        info('tes cron'); 
        return 0;
    }
}
