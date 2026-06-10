<?php

namespace App\Usecases;

use App\Http\Presenter\Response;
use App\Entities\ResponseEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbsensiUsecase extends Usecase
{
    public string $className = "AbsensiUsecase";

    public function getSetting(): array
    {
        $funcName = $this->className . ".getSetting";

        try {
            $data = DB::table('absensi_settings')->first();

            return Response::buildSuccess([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function updateSetting(Request $request): array
    {
        $funcName = $this->className . ".updateSetting";

        $validator = Validator::make($request->all(), [
            'jam_masuk'           => 'required',
            'jam_pulang'          => 'required|after:jam_masuk',
            'toleransi_terlambat' => 'required|integer|min:0',
        ], [
            'jam_pulang.after' => 'Jam pulang harus lebih besar dari jam masuk.'
        ]);

        $validator->validate();

        try {
            $existing = DB::table('absensi_settings')->first();

            if ($existing) {
                DB::table('absensi_settings')
                    ->where('id', $existing->id)
                    ->update([
                        'jam_masuk'           => $request->jam_masuk,
                        'jam_pulang'          => $request->jam_pulang,
                        'toleransi_terlambat' => $request->toleransi_terlambat,
                        'updated_at'          => now(),
                    ]);
            } else {
                DB::table('absensi_settings')->insert([
                    'jam_masuk'           => $request->jam_masuk,
                    'jam_pulang'          => $request->jam_pulang,
                    'toleransi_terlambat' => $request->toleransi_terlambat,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getAllAbsensi(array $filter = []): array
    {
        $funcName = $this->className . ".getAllAbsensi";

        try {
            $absensiSetting = DB::table('absensi_settings')->first();
            $jamMasuk       = $absensiSetting->jam_masuk ?? '08:00:00';
            $toleransiMenit = $absensiSetting->toleransi_terlambat ?? 30;
            $batasTerlambat = date('H:i:s', strtotime($jamMasuk) + ($toleransiMenit * 60));

            $query = DB::table("absensi_member as a")
                ->leftJoin("members as m", "m.id", "=", "a.member_id")
                ->leftJoin("member_categories as mc", "mc.id", "=", "m.category_id")
                ->leftJoin("data_wajah_member as dw", "dw.member_id", "=", "m.id");

            if (!empty($filter['id'])) {
                $query->where("a.id", $filter['id']);
            }

            if (!empty($filter['keyword'])) {
                $query->where(function ($q) use ($filter) {
                    $q->where("m.name", "like", "%" . $filter['keyword'] . "%")
                        ->orWhere("mc.name", "like", "%" . $filter['keyword'] . "%")
                        ->orWhere("a.tanggal_absensi", "like", "%" . $filter['keyword'] . "%");
                });
            }

            if (!empty($filter['category_id'])) {
                $query->where("m.category_id", $filter['category_id']);
            }

            if (!empty($filter['member_id'])) {
                $query->where("a.member_id", $filter['member_id']);
            }

            if (!empty($filter['tanggal_absensi'])) {
                $query->where("a.tanggal_absensi", $filter['tanggal_absensi']);
            }

            if (!empty($filter['tanggal'])) {
                $query->where("a.tanggal_absensi", $filter['tanggal']);
            }

            if (!empty($filter['bulan'])) {
                $query->whereMonth("a.tanggal_absensi", date('m', strtotime($filter['bulan'])))
                    ->whereYear("a.tanggal_absensi", date('Y', strtotime($filter['bulan'])));
            }

            if (!empty($filter['status_kehadiran'])) {
                if ($filter['status_kehadiran'] == 'terlambat') {
                    $query->where("a.status_kehadiran", "hadir")
                        ->where(function ($q) use ($batasTerlambat) {
                            $q->where("a.status_final", "terlambat")
                                ->orWhere(function ($q2) use ($batasTerlambat) {
                                    $q2->whereNull("a.status_final")
                                        ->where("a.jam_masuk", ">", $batasTerlambat);
                                });
                        });
                } else {
                    $query->where(function ($q) use ($filter) {
                        $q->where("a.status_final", $filter['status_kehadiran'])
                            ->orWhere(function ($q2) use ($filter) {
                                $q2->whereNull("a.status_final")
                                    ->where("a.status_kehadiran", $filter['status_kehadiran']);
                            });
                    });
                }
            }

            $select = [
                "a.id",
                "a.member_id",
                "m.name as member_name",
                "mc.name as category_name",
                "dw.foto_wajah as foto_registrasi",       // foto registrasi (untuk perbandingan)
                "a.foto_absensi",      // foto saat absensi (yang ditampilkan)
                "a.foto_pulang",       // foto saat pulang
                "a.tanggal_absensi",
                "a.jam_masuk",
                "a.jam_pulang",
                "a.status_kehadiran",
                "a.status_final",
                "a.catatan",
                "a.created_at",
            ];

            $query->orderBy("a.tanggal_absensi", "desc")
                ->orderBy("a.jam_masuk", "desc");

            if (!empty($filter['for_export'])) {
                $data = $query->get($select);
                $collection = $data;
            } else {
                $data = $query->paginate(20, $select);
                $collection = $data->getCollection();
            }

            // Hanya kalkulasi ulang untuk data LAMA yang belum punya status_final di DB
            $collection->transform(function ($item) use ($batasTerlambat) {
                if (!empty($item->status_final)) {
                    // Sudah ada status_final tersimpan → pakai langsung, tidak dihitung ulang
                    return $item;
                }

                // Data lama belum punya status_final → kalkulasi sebagai fallback
                $statusFinal = $item->status_kehadiran;

                if (
                    $item->status_kehadiran === 'hadir' &&
                    !empty($item->jam_masuk) &&
                    strtotime($item->jam_masuk) > strtotime($batasTerlambat)
                ) {
                    $statusFinal = 'terlambat';
                }

                $item->status_final = $statusFinal;

                return $item;
            });


            if (!empty($filter['status_kehadiran']) && $filter['status_kehadiran'] === 'hadir') {
                $collection = $collection->filter(fn($item) => $item->status_final === 'hadir');
            }

            if (!empty($filter['for_export'])) {
                $data = $collection; // pakai collection yang sudah difilter
            } else {
                $data->setCollection($collection);
            }

            return Response::buildSuccess([
                "list" => $data,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }
    public function createAbsensi(Request $request): array
    {
        $funcName = $this->className . ".createAbsensi";

        $validator = Validator::make($request->all(), [
            "member_id" => "required|exists:members,id",
            "tanggal_absensi" => "required|date",
            "jam_masuk" => "nullable",
            "jam_pulang" => "nullable",
            "status_kehadiran" => "required|in:hadir,izin,sakit,alpha",
            "foto_absensi" => "nullable|string",
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            $statusFinal = $request->status_kehadiran;

            if ($request->status_kehadiran === 'hadir' && !empty($request->jam_masuk)) {
                $setting        = DB::table('absensi_settings')->first();
                $jamMasuk       = $setting->jam_masuk ?? '07:00:00';

                if (strtotime($request->jam_masuk) > strtotime($jamMasuk)) {
                    $statusFinal = 'terlambat';
                }
            }

            DB::table("absensi_member")->updateOrInsert(
                [
                    "member_id" => $request->member_id,
                    "tanggal_absensi" => $request->tanggal_absensi,
                ],
                [
                    "jam_masuk" => $request->jam_masuk,
                    "jam_pulang" => $request->jam_pulang,
                    "status_kehadiran" => $request->status_kehadiran,
                    "status_final"     => $statusFinal,
                    "foto_absensi" => $request->foto_absensi,
                    "updated_at" => now(),
                    "created_at" => now(),
                ]
            );

            DB::commit();

            return Response::buildSuccessCreated();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function updateAbsensi(Request $request, int $id): array
    {
        $funcName = $this->className . ".updateAbsensi";

        $validator = Validator::make($request->all(), [
            'status_kehadiran' => 'required|in:izin,sakit,alpha',
            'catatan'          => 'nullable|string|max:255',
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            DB::table('absensi_member')
                ->where('id', $id)
                ->update([
                    'status_kehadiran' => $request->status_kehadiran,
                    'status_final'     => $request->status_kehadiran,
                    'catatan'          => $request->catatan ?: null,
                    'updated_at'       => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Scan masuk: buat atau update record absensi
     */
    public function scanMasuk(Request $request): array
    {
        $funcName = $this->className . ".scanMasuk";

        $validator = Validator::make($request->all(), [
            "member_id"    => "required|exists:members,id",
            "jam_masuk"    => "nullable",
            "foto_absensi" => "nullable|string",
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            $today = now()->timezone('Asia/Jakarta')->toDateString();
            $jamMasuk = $request->jam_masuk ?? now()->timezone('Asia/Jakarta')->toTimeString();
            
            // Ambil setting untuk menentukan status_final
            $setting = DB::table('absensi_settings')->first();
            $jamMasukSetting = $setting->jam_masuk ?? '07:00:00';
            $toleransiMenit = $setting->toleransi_terlambat ?? 30;
            $batasTerlambat = date('H:i:s', strtotime($jamMasukSetting) + ($toleransiMenit * 60));
            
            if (strtotime($jamMasuk) > strtotime($batasTerlambat)) {
                DB::rollback();
                return Response::buildErrorService("Batas waktu absensi masuk telah berakhir.");
            }

            // Tentukan status_final
            $statusFinal = 'hadir';
            if (strtotime($jamMasuk) > strtotime($jamMasukSetting)) {
                $statusFinal = 'terlambat';
            }

            // Cek apakah sudah ada record hari ini
            $existing = DB::table('absensi_member')
                ->where('member_id', $request->member_id)
                ->where('tanggal_absensi', $today)
                ->first();

            if ($existing) {
                // Jika sudah ada record, update hanya jika statusnya bukan alpha/izin/sakit
                if (in_array($existing->status_kehadiran, ['alpha', 'izin', 'sakit']) || $existing->status_final == 'alpha') {
                    DB::rollback();
                    if ($existing->status_kehadiran === 'alpha') {
                        return Response::buildErrorService("Absensi hari ini sudah berstatus alpha karena melewati batas toleransi keterlambatan");
                    }
                    return Response::buildErrorService(
                        "Tidak dapat scan masuk karena status hari ini adalah {$existing->status_kehadiran}"
                    );
                }

                // Update record yang sudah ada
                DB::table('absensi_member')
                    ->where('id', $existing->id)
                    ->update([
                        'jam_masuk'        => $jamMasuk,
                        'status_kehadiran' => 'hadir',
                        'status_final'     => $statusFinal,
                        'foto_absensi'     => $request->foto_absensi ?? $existing->foto_absensi,
                        'updated_at'       => now(),
                    ]);
            } else {
                // Buat record baru
                DB::table('absensi_member')->insert([
                    'member_id'        => $request->member_id,
                    'tanggal_absensi'  => $today,
                    'jam_masuk'        => $jamMasuk,
                    'status_kehadiran' => 'hadir',
                    'status_final'     => $statusFinal,
                    'foto_absensi'     => $request->foto_absensi,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            return Response::buildSuccess(
                message: "Scan masuk berhasil dicatat. Status: " . ($statusFinal == 'terlambat' ? 'Terlambat' : 'Hadir')
            );
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Scan pulang: update jam_pulang pada record absensi yang sudah ada hari ini
     */
    public function scanPulang(Request $request): array
    {
        $funcName = $this->className . ".scanPulang";

        $validator = Validator::make($request->all(), [
            "member_id"   => "required|exists:members,id",
            "foto_pulang" => "nullable|string",
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            $today = now()->timezone('Asia/Jakarta')->toDateString();
            $jamPulang = now()->timezone('Asia/Jakarta')->toTimeString();

            // Cek absen hari ini
            $absensi = DB::table('absensi_member')
                ->where('member_id', $request->member_id)
                ->where('tanggal_absensi', $today)
                ->first();

            if (!$absensi) {
                DB::rollback();
                return Response::buildErrorService(
                    "Anda belum melakukan absensi masuk sehingga belum dapat melakukan absensi pulang."
                );
            }

            if (in_array($absensi->status_kehadiran, ['alpha', 'izin', 'sakit']) || $absensi->status_final == 'alpha') {
                DB::rollback();
                if ($absensi->status_kehadiran === 'alpha') {
                    return Response::buildErrorService("Absensi hari ini sudah berstatus alpha karena melewati batas toleransi keterlambatan");
                }
                return Response::buildErrorService("Status hari ini adalah " . $absensi->status_kehadiran . ", tidak dapat melakukan absen pulang.");
            }

            if (!empty($absensi->jam_pulang)) {
                DB::rollback();
                return Response::buildErrorService("Anda sudah melakukan absensi pulang hari ini.");
            }

            $setting = DB::table('absensi_settings')->first();
            $jamPulangSetting = $setting->jam_pulang ?? '15:00:00';
            
            if (strtotime($jamPulang) < strtotime($jamPulangSetting)) {
                DB::rollback();
                return Response::buildErrorService("Belum waktunya absensi pulang. Jam pulang: " . date('H:i', strtotime($jamPulangSetting)));
            }

            DB::table('absensi_member')
                ->where('id', $absensi->id)
                ->update([
                    'jam_pulang'   => $jamPulang,
                    'foto_pulang' => $request->foto_pulang ?? null,
                    'updated_at'   => now(),
                ]);

            DB::table('log_absensi_face_recognition')->insert([
                'member_id' => $request->member_id,
                'tanggal' => $today,
                'waktu' => $jamPulang,
                'status_liveness' => 1,
                'hasil_pengenalan' => 'berhasil',
                'nama_perangkat' => 'Mesin Absensi / Scan Biasa',
                'created_at' => now()
            ]);

            DB::commit();

            return Response::buildSuccess(
                message: "Scan pulang berhasil dicatat."
            );
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ['func_name' => $funcName]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function registerFace(Request $request): array
    {
        $funcName = $this->className . ".registerFace";

        $validator = Validator::make($request->all(), [
            "member_id" => "required|exists:members,id",
            "file" => "required|file|mimes:jpeg,png,jpg|max:5120"
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {
            // Dapatkan file foto
            $file = $request->file('file');
            
            // Simpan foto lokal ke storage public/wajah
            $fileName = time() . '_' . $request->member_id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('wajah', $fileName, 'public');
            $foto_wajah = 'wajah/' . $fileName;

            // Hit Python API
            // URL default adalah localhost:8000, atau sesuaikan dengan .env
            $apiUrl = env('FACE_LIVENESS_API_URL', 'http://127.0.0.1:8000');
            
            // Forward multipart file ke Python API
            $response = \Illuminate\Support\Facades\Http::attach(
                'file',
                file_get_contents($file->getRealPath()),
                $fileName
            )->post($apiUrl . '/face/register-face', [
                'member_id' => $request->member_id
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['status']) && $result['status'] == 'registered') {
                    // Python API berhasil ekstrak wajah dan simpan ke database
                    // Update field foto_wajah di database
                    DB::table("data_wajah_member")
                        ->where("member_id", $request->member_id)
                        ->update([
                            "foto_wajah" => $foto_wajah,
                            "status_aktif" => 1,
                            "updated_at" => now()
                        ]);
                        
                    DB::commit();

                    return Response::buildSuccess(
                        message: "Registrasi wajah berhasil"
                    );
                } else {
                    DB::rollback();
                    return [
                        'error' => true,
                        'message' => $result['message'] ?? 'Gagal mendeteksi wajah'
                    ];
                }
            } else {
                DB::rollback();
                $errorMsg = "Gagal menghubungi server pengenal wajah";
                if ($response->serverError() || $response->clientError()) {
                    $resData = $response->json();
                    if (isset($resData['detail'])) {
                        $errorMsg = is_string($resData['detail']) ? $resData['detail'] : json_encode($resData['detail']);
                    }
                }
                return [
                    'error' => true,
                    'message' => $errorMsg
                ];
            }

        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName
            ]);

            return [
                'error' => true,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    public function scanFace(Request $request): array
    {
        $funcName = $this->className . ".scanFace";

        $validator = Validator::make($request->all(), [
            "file" => "required|file|mimes:jpeg,png,jpg|max:5120"
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        DB::beginTransaction();

        try {
            $file = $request->file('file');
            
            $apiUrl = env('FACE_LIVENESS_API_URL', 'http://127.0.0.1:8000');
            
            $postData = [];
            if ($request->has('session_id')) {
                $postData['session_id'] = $request->session_id;
            }

            // Kirim gambar ke Python API untuk diabsen
            $response = \Illuminate\Support\Facades\Http::attach(
                'file',
                file_get_contents($file->getRealPath()),
                'scan.jpg'
            )->post($apiUrl . '/face/absen', $postData);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status'])) {
                    if ($result['status'] == 'waiting_blink') {
                        DB::rollback();
                        return ['waiting_blink' => true, 'message' => 'Silakan kedipkan mata Anda...'];
                    }
                    if ($result['status'] == 'spoof') {
                        DB::rollback();
                        return ['error' => true, 'message' => 'Liveness check gagal. Wajah terdeteksi palsu!'];
                    }
                    if ($result['status'] == 'no_face') {
                        DB::rollback();
                        return ['error' => true, 'message' => 'Wajah tidak terdeteksi.'];
                    }
                    if ($result['status'] == 'unknown') {
                        DB::rollback();
                        return ['error' => true, 'message' => 'Wajah tidak dikenali atau belum diregistrasi.'];
                    }
                    if ($result['status'] == 'success') {
                        $memberId = $result['member_id'];
                        
                        $member = DB::table('members')->where('id', $memberId)->first();
                        if (!$member) {
                            DB::rollback();
                            return ['error' => true, 'message' => 'Data pegawai tidak ditemukan.'];
                        }

                        $today = now()->timezone('Asia/Jakarta')->toDateString();
                        $nowTime = now()->timezone('Asia/Jakarta')->toTimeString();
                        
                        // Cek absen hari ini
                        $absensiToday = DB::table('absensi_member')
                            ->where('member_id', $memberId)
                            ->where('tanggal_absensi', $today)
                            ->first();

                        $msg = "";
                        
                        if (!$absensiToday) {
                            // Belum absen -> Masuk
                            $setting = DB::table('absensi_settings')->first();
                            $jamMasuk = $setting->jam_masuk ?? '07:00:00';
                            $toleransiMenit = $setting->toleransi_terlambat ?? 30;
                            $batasTerlambat = date('H:i:s', strtotime($jamMasuk) + ($toleransiMenit * 60));
                            $jamPulang = $setting->jam_pulang ?? '15:00:00';

                            // 1. Jika guru/tendik belum melakukan absensi masuk, tidak boleh melakukan scan pulang (jika sudah masuk jam pulang)
                            if (strtotime($nowTime) >= strtotime($jamPulang)) {
                                DB::rollback();
                                return ['error' => true, 'message' => 'Belum melakukan absensi masuk sehingga tidak dapat melakukan absensi pulang'];
                            }

                            if (strtotime($nowTime) > strtotime($batasTerlambat)) {
                                DB::rollback();
                                return ['error' => true, 'message' => 'Batas waktu absensi masuk telah berakhir. Anda sudah tercatat sebagai alpha.'];
                            }

                            $statusFinal = 'hadir';
                            if (strtotime($nowTime) > strtotime($jamMasuk)) {
                                $statusFinal = 'terlambat';
                            }

                            // Simpan foto absen masuk
                            $fileName = time() . '_masuk_' . $memberId . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('absensi', $fileName, 'public');

                            DB::table('absensi_member')->insert([
                                'member_id' => $memberId,
                                'tanggal_absensi' => $today,
                                'jam_masuk' => $nowTime,
                                'status_kehadiran' => 'hadir',
                                'status_final' => $statusFinal,
                                'foto_absensi' => 'absensi/' . $fileName,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $msg = "Absen Masuk Berhasil: " . $member->name;
                        } else {
                            // Sudah ada absen -> Cek apakah status alpha/izin/sakit
                            if (in_array($absensiToday->status_kehadiran, ['alpha', 'izin', 'sakit']) || $absensiToday->status_final == 'alpha') {
                                DB::rollback();
                                if ($absensiToday->status_kehadiran === 'alpha') {
                                    return ['error' => true, 'message' => 'Absensi hari ini sudah berstatus alpha karena melewati batas toleransi keterlambatan'];
                                }
                                return ['error' => true, 'message' => 'Status Anda hari ini adalah ' . $absensiToday->status_kehadiran . '. Tidak dapat melakukan absensi.'];
                            }

                            // Cek apakah jam pulang masih kosong
                            if (empty($absensiToday->jam_pulang)) {
                                $setting = DB::table('absensi_settings')->first();
                                $jamPulang = $setting->jam_pulang ?? '15:00:00';
                                
                                // 3. Jika belum waktunya pulang
                                if (strtotime($nowTime) < strtotime($jamPulang)) {
                                    DB::rollback();
                                    return ['error' => true, 'message' => 'Belum memasuki jam pulang'];
                                }

                                // Update jam pulang
                                $fileName = time() . '_pulang_' . $memberId . '.' . $file->getClientOriginalExtension();
                                $path = $file->storeAs('absensi', $fileName, 'public');

                                DB::table('absensi_member')->where('id', $absensiToday->id)->update([
                                    'jam_pulang' => $nowTime,
                                    'foto_pulang' => 'absensi/' . $fileName,
                                    'updated_at' => now()
                                ]);

                                // Insert log absensi hanya ketika sudah absensi pulang
                                DB::table('log_absensi_face_recognition')->insert([
                                    'member_id' => $memberId,
                                    'tanggal' => $today,
                                    'waktu' => $nowTime,
                                    'nilai_ear' => $result['ear'] ?? null,
                                    'skor_liveness' => $result['liveness_score'] ?? null,
                                    'status_liveness' => 1,
                                    'hasil_pengenalan' => 'berhasil',
                                    'nama_perangkat' => 'Camera Utama',
                                    'created_at' => now()
                                ]);

                                $msg = "Absen Pulang Berhasil: " . $member->name;
                            } else {
                                // 4. Jika guru/tendik sudah scan pulang
                                DB::rollback();
                                return ['error' => true, 'message' => 'Anda sudah scan pulang hari ini'];
                            }
                        }

                        DB::commit();
                        return [
                            'success' => true,
                            'message' => $msg
                        ];
                    }
                }
                
                DB::rollback();
                return ['error' => true, 'message' => 'Gagal mendeteksi wajah (Respons tidak valid)'];
            } else {
                DB::rollback();
                return ['error' => true, 'message' => 'Gagal menghubungi server pengenal wajah.'];
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), ["func_name" => $funcName]);
            return ['error' => true, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }

    public function getFaceData(int $memberID): array
    {
        try {
            $data = DB::table("data_wajah_member")
                ->where("member_id", $memberID)
                ->where("status_aktif", 1)
                ->first();

            return Response::buildSuccess([
                "data" => $data ? (array) $data : null
            ]);
        } catch (\Exception $e) {
            return Response::buildErrorService($e->getMessage());
        }
    }
    public function deleteAbsensi(int $id): array
    {
        $funcName = $this->className . ".deleteAbsensi";

        try {
            $data = DB::table("absensi_member")
                ->where("id", $id)
                ->first();

            if (!$data) {
                return Response::buildErrorService("Data absensi tidak ditemukan");
            }

            DB::table("absensi_member")
                ->where("id", $id)
                ->delete();

            return Response::buildSuccess();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function createLogFace(Request $request): array
    {
        try {
            DB::table("log_absensi_face_recognition")->insert([
                "member_id" => $request->member_id ?? null,
                "tanggal" => now()->toDateString(),
                "waktu" => now()->toTimeString(),
                "nilai_ear" => $request->nilai_ear ?? null,
                "skor_liveness" => $request->skor_liveness ?? null,
                "status_liveness" => $request->status_liveness ?? 0,
                "hasil_pengenalan" => $request->hasil_pengenalan,
                "nama_perangkat" => $request->nama_perangkat ?? null,
                "created_at" => now()
            ]);

            return Response::buildSuccess();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return Response::buildErrorService($e->getMessage());
        }
    }
    public function exportToExcel(Request $req, string $baseRedirect)
    {
        $result = $this->getAllAbsensi(
            array_merge($req->all(), [
                'for_export' => true,
            ])
        );

        $list = $result['data']['list'] ?? [];

        if (empty($list) || count($list) < 1) {
            return redirect()
                ->intended($baseRedirect)
                ->with('error', ResponseEntity::getNotFoundMsg("Data Absensi"));
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'No',
            'Nama',
            'Kategori',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Status Kehadiran',
            'Catatan',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $no = 1;
        $row = 2;

        foreach ($list as $d) {
            $statusText = match ($d->status_final ?? $d->status_kehadiran ?? '') {
                'hadir' => 'Hadir',
                'terlambat' => 'Terlambat',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'alpha' => 'Alpha',
                default => '-',
            };

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $d->member_name ?? '-');
            $sheet->setCellValue("C{$row}", $d->category_name ?? '-');
            $sheet->setCellValue("D{$row}", !empty($d->tanggal_absensi) ? date('d/m/Y', strtotime($d->tanggal_absensi)) : '-');
            $sheet->setCellValue("E{$row}", !empty($d->jam_masuk) ? date('H:i', strtotime($d->jam_masuk)) : '-');
            $sheet->setCellValue("F{$row}", !empty($d->jam_pulang) ? date('H:i', strtotime($d->jam_pulang)) : '-');
            $sheet->setCellValue("G{$row}", $statusText);
            $sheet->setCellValue("H{$row}", $d->catatan ?? '-');
            $row++;
        }

        $lastRow = $row - 1;

        $sheet->getStyle("A1:H1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // =============================================
        // GENERATE NAMA FILE BERDASARKAN FILTER
        // =============================================
        $fileName = $this->generateExportFileName($req);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generate nama file export berdasarkan filter yang dipilih
     */
    private function generateExportFileName(Request $req): string
    {
        $filters = [];
        $dateStr = date('d-m-Y');

        // 1. Filter berdasarkan nama
        if (!empty($req->keyword)) {
            $filters[] = 'Nama_' . $this->sanitizeFileName($req->keyword);
        }

        // 2. Filter berdasarkan kategori
        if (!empty($req->category_id)) {
            $category = DB::table('member_categories')->where('id', $req->category_id)->first();
            if ($category) {
                $filters[] = $this->sanitizeFileName($category->name);
            }
        }

        // 3. Filter berdasarkan status kehadiran
        if (!empty($req->status_kehadiran)) {
            $statusMap = [
                'hadir' => 'Hadir',
                'terlambat' => 'Terlambat',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'alpha' => 'Alpha'
            ];
            $statusName = $statusMap[$req->status_kehadiran] ?? $req->status_kehadiran;
            $filters[] = 'Status_' . $this->sanitizeFileName($statusName);
        }

        // 4. Filter berdasarkan bulan
        if (!empty($req->bulan)) {
            $bulanMap = [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember'
            ];

            $bulanInput = $req->bulan;
            // Format input bisa YYYY-MM atau MM
            if (strlen($bulanInput) == 7) {
                // Format YYYY-MM
                $tahun = substr($bulanInput, 0, 4);
                $bulan = substr($bulanInput, 5, 2);
                $bulanNama = $bulanMap[$bulan] ?? $bulan;
                $filters[] = "Bulan_{$bulanNama}_{$tahun}";
            } else {
                // Format MM atau lainnya
                $bulanNama = $bulanMap[$bulanInput] ?? $bulanInput;
                $filters[] = "Bulan_{$bulanNama}";
            }
        }

        // 5. Filter berdasarkan tanggal spesifik
        if (!empty($req->tanggal)) {
            $tanggal = date('d-m-Y', strtotime($req->tanggal));
            $filters[] = "Tanggal_{$tanggal}";
        }

        // 6. Filter berdasarkan rentang tanggal (jika ada)
        if (!empty($req->start_date) && !empty($req->end_date)) {
            $start = date('d-m-Y', strtotime($req->start_date));
            $end = date('d-m-Y', strtotime($req->end_date));
            $filters[] = "Periode_{$start}_sampai_{$end}";
        } elseif (!empty($req->start_date)) {
            $start = date('d-m-Y', strtotime($req->start_date));
            $filters[] = "Mulai_{$start}";
        } elseif (!empty($req->end_date)) {
            $end = date('d-m-Y', strtotime($req->end_date));
            $filters[] = "Sampai_{$end}";
        }

        // 7. Filter berdasarkan member_id (jika spesifik orang)
        if (!empty($req->member_id)) {
            $member = DB::table('members')->where('id', $req->member_id)->first();
            if ($member) {
                $filters[] = $this->sanitizeFileName($member->name);
            }
        }

        // Generate nama file
        if (count($filters) > 0) {
            // Jika ada filter: [Absensi] - Filter1_Filter2_Filter3 - tanggal.xlsx
            $filterString = implode('_', $filters);
            $fileName = "[Absensi] {$filterString} - {$dateStr}.xlsx";
        } else {
            // Jika tidak ada filter: [Absensi] Data Absensi - tanggal.xlsx
            $fileName = "[Absensi] Data Absensi - {$dateStr}.xlsx";
        }

        // Batasi panjang nama file (max 255 karakter)
        if (strlen($fileName) > 250) {
            $fileName = "[Absensi] " . substr($filterString, 0, 200) . " - {$dateStr}.xlsx";
        }

        return $fileName;
    }

    /**
     * Sanitasi nama file (hapus karakter yang tidak valid)
     */
    private function sanitizeFileName(string $name): string
    {
        // Hapus karakter yang tidak valid untuk nama file di Windows/Linux
        $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|', ' ', "\t", "\n", "\r"];
        $sanitized = str_replace($invalidChars, '_', $name);

        // Hapus karakter non-ASCII dan biarkan hanya alfanumerik, underscore, dash, titik
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-.]/u', '_', $sanitized);

        // Hapus underscore berlebih
        $sanitized = preg_replace('/_+/', '_', $sanitized);

        // Trim underscore di awal/akhir
        $sanitized = trim($sanitized, '_');

        // Jika hasil kosong, beri default
        if (empty($sanitized)) {
            $sanitized = 'data';
        }

        return $sanitized;
    }

    /**
     * Export Excel khusus pegawai — hanya data milik pegawai yang login
     */
    public function exportToExcelPegawai(array $filter, string $baseRedirect, string $namaAnggota): mixed
    {
        $result = $this->getAllAbsensi(
            array_merge($filter, ['for_export' => true])
        );

        $list = $result['data']['list'] ?? [];

        if (empty($list) || count($list) < 1) {
            return redirect()
                ->intended($baseRedirect)
                ->with('error', ResponseEntity::getNotFoundMsg("Data Absensi"));
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi Saya');

        // ── Header tabel mulai baris 1 ──
        $headers = [
            'No',
            'Nama',
            'Kategori',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Status Kehadiran',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // ── Data mulai baris 2 ──
        $no  = 1;
        $row = 2;

        foreach ($list as $d) {
            $statusText = match ($d->status_final ?? $d->status_kehadiran ?? '') {
                'hadir'     => 'Hadir',
                'terlambat' => 'Terlambat',
                'izin'      => 'Izin',
                'sakit'     => 'Sakit',
                'alpha'     => 'Alpha',
                default     => '-',
            };

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $d->member_name   ?? '-');
            $sheet->setCellValue("C{$row}", $d->category_name ?? '-');
            $sheet->setCellValue("D{$row}", !empty($d->tanggal_absensi) ? date('d/m/Y', strtotime($d->tanggal_absensi)) : '-');
            $sheet->setCellValue("E{$row}", !empty($d->jam_masuk)       ? date('H:i',   strtotime($d->jam_masuk))       : '-');
            $sheet->setCellValue("F{$row}", !empty($d->jam_pulang)      ? date('H:i',   strtotime($d->jam_pulang))      : '-');
            $sheet->setCellValue("G{$row}", $statusText);

            // Nomor urut di tengah
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $row++;
        }

        $lastRow = $row - 1;

        // ── Style header tabel (baris 1) ──
        $sheet->getStyle("A1:G1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 12,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ── Border seluruh tabel ──
        if ($lastRow >= 1) {
            $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // ── Auto size semua kolom ──
        foreach (range('A', 'G') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        // ── Nama file: nama pegawai + bulan filter (jika ada) ──
        $namaSanitized = $this->sanitizeFileName($namaAnggota);
        $bulanStr      = '';

        if (!empty($filter['bulan'])) {
            $bulanMap = [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember',
            ];
            $tahun    = substr($filter['bulan'], 0, 4);
            $bulan    = substr($filter['bulan'], 5, 2);
            $bulanStr = '_' . ($bulanMap[$bulan] ?? $bulan) . '_' . $tahun;
        }

        $fileName = "[Absensi] {$namaSanitized}{$bulanStr} - " . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
