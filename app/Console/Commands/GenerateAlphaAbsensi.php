<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateAlphaAbsensi extends Command
{
    protected $signature   = 'absensi:generate-alpha';
    protected $description = 'Generate status Alpha untuk Guru/Tendik yang belum absen setelah melewati batas toleransi terlambat';

    public function handle()
    {
        $now   = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Lewati Sabtu & Minggu
        if ($now->isWeekend()) {
            $this->info("Hari ini weekend, skip.");
            return;
        }

        // Ambil setting dari database
        $setting = DB::table('absensi_settings')->first();
        
        if (!$setting) {
            $this->error("Setting absensi tidak ditemukan di database.");
            return;
        }

        // Hitung batas waktu toleransi terlambat
        $jamMasuk = $setting->jam_masuk; // misal: '07:00:00'
        $toleransiMenit = (int) $setting->toleransi_terlambat; // misal: 30
        
        // Gabungkan jam masuk + toleransi (contoh: '07:00:00' + 30 menit = '07:30:00')
        $batasTerlambat = Carbon::createFromFormat('H:i:s', $jamMasuk, 'Asia/Jakarta')
            ->addMinutes($toleransiMenit);
        
        $batasTerlambatString = $batasTerlambat->format('H:i:s');
        
        // Cek apakah sekarang sudah melewati batas toleransi
        $waktuSekarang = Carbon::now('Asia/Jakarta');
        $batasWaktuHariIni = Carbon::now('Asia/Jakarta')->setTimeFromTimeString($batasTerlambatString);
        
        if ($waktuSekarang->lt($batasWaktuHariIni)) {
            $this->info("Belum melewati batas toleransi terlambat ({$batasTerlambatString}), skip generate alpha.");
            return;
        }

        $this->info("Memproses generate alpha untuk tanggal {$today} (Batas toleransi: {$batasTerlambatString})");

        // Ambil semua member Guru/Tendik aktif
        $members = DB::table('members as m')
            ->leftJoin('member_categories as mc', 'mc.id', '=', 'm.category_id')
            ->whereNull('m.deleted_at')
            ->whereIn('mc.name', ['Guru', 'Tendik'])
            ->select('m.id as member_id', 'm.name')
            ->get();

        // Ambil member_id yang sudah absen hari ini
        $absenHariIni = DB::table('absensi_member')
            ->where('tanggal_absensi', $today)
            ->pluck('member_id')
            ->toArray();

        $inserts = [];
        $insertedCount = 0;

        foreach ($members as $member) {
            if (!in_array($member->member_id, $absenHariIni)) {
                $inserts[] = [
                    'member_id'        => $member->member_id,
                    'tanggal_absensi'  => $today,
                    'jam_masuk'        => null,
                    'jam_pulang'       => null,
                    'status_kehadiran' => 'alpha',
                    'status_final'     => 'alpha',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                $insertedCount++;
                $this->info("Alpha: {$member->name}");
            }
        }

        if (!empty($inserts)) {
            // Insert bulk for better performance
            DB::table('absensi_member')->insert($inserts);
        }

        $this->info("Selesai. {$insertedCount} data alpha di-generate untuk {$today}.");
    }
}