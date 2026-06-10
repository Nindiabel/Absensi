<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Usecases\AbsensiUsecase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

class AbsensiPegawaiTest extends TestCase
{
    protected AbsensiUsecase $absensiUsecase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->absensiUsecase = new AbsensiUsecase();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * TC-WP-01
     * Validasi Menampilkan Riwayat Absensi Sendiri
     */
    public function test_tc_wp_01_validasi_menampilkan_riwayat_absensi_sendiri()
    {
        $settingsBuilder = Mockery::mock(Builder::class);
        $settingsBuilder->shouldReceive('first')->once()->andReturn((object)[]);

        $absensiBuilder = Mockery::mock(Builder::class);
        $absensiBuilder->shouldReceive('leftJoin')->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->with('a.member_id', 5)->andReturnSelf();
        $absensiBuilder->shouldReceive('orderBy')->andReturnSelf();
        
        $paginator = new LengthAwarePaginator(collect([
            (object)[
                'id' => 1, 
                'member_id' => 5, 
                'member_name' => 'Pegawai A', 
                'status_kehadiran' => 'hadir',
                'status_final' => 'hadir'
            ]
        ]), 1, 20);

        $absensiBuilder->shouldReceive('paginate')->andReturn($paginator);

        DB::shouldReceive('table')->with('absensi_settings')->andReturn($settingsBuilder);
        DB::shouldReceive('table')->with('absensi_member as a')->andReturn($absensiBuilder);

        $response = $this->absensiUsecase->getAllAbsensi(['member_id' => 5]);

        $this->assertArrayNotHasKey('error', $response);
        $this->assertEquals(5, $response['data']['list'][0]->member_id);
    }

    /**
     * TC-WP-02
     * Validasi Detail Absensi Milik Sendiri
     */
    public function test_tc_wp_02_validasi_detail_absensi_milik_sendiri()
    {
        $settingsBuilder = Mockery::mock(Builder::class);
        $settingsBuilder->shouldReceive('first')->once()->andReturn((object)[]);

        $absensiBuilder = Mockery::mock(Builder::class);
        $absensiBuilder->shouldReceive('leftJoin')->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->with('a.id', 10)->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->with('a.member_id', 5)->andReturnSelf();
        $absensiBuilder->shouldReceive('orderBy')->andReturnSelf();
        
        $paginator = new LengthAwarePaginator(collect([
            (object)[
                'id' => 10, 
                'member_id' => 5, 
                'member_name' => 'Pegawai A',
                'status_kehadiran' => 'hadir',
                'status_final' => 'hadir'
            ]
        ]), 1, 20);

        $absensiBuilder->shouldReceive('paginate')->andReturn($paginator);

        DB::shouldReceive('table')->with('absensi_settings')->andReturn($settingsBuilder);
        DB::shouldReceive('table')->with('absensi_member as a')->andReturn($absensiBuilder);

        $response = $this->absensiUsecase->getAllAbsensi(['id' => 10, 'member_id' => 5]);

        $this->assertArrayNotHasKey('error', $response);
        $this->assertEquals(10, $response['data']['list'][0]->id);
    }

    /**
     * TC-WP-03
     * Validasi Export Riwayat Absensi
     */
    public function test_tc_wp_03_validasi_export_riwayat_absensi()
    {
        $settingsBuilder = Mockery::mock(Builder::class);
        $settingsBuilder->shouldReceive('first')->andReturn((object)[]);

        $absensiBuilder = Mockery::mock(Builder::class);
        $absensiBuilder->shouldReceive('leftJoin')->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->with('a.member_id', 5)->andReturnSelf();
        $absensiBuilder->shouldReceive('orderBy')->andReturnSelf();
        $absensiBuilder->shouldReceive('get')->andReturn(collect([])); // Di-mock empty agar tidak menjalankan exit() pada Usecase

        DB::shouldReceive('table')->with('absensi_settings')->andReturn($settingsBuilder);
        DB::shouldReceive('table')->with('absensi_member as a')->andReturn($absensiBuilder);

        $response = $this->absensiUsecase->exportToExcelPegawai(['member_id' => 5], '/redirect', 'Pegawai A');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    /**
     * TC-WP-04
     * Validasi Export Riwayat Absensi dengan filter bulan dan status kehadiran
     */
    public function test_tc_wp_04_validasi_export_riwayat_absensi_dengan_filter()
    {
        $settingsBuilder = Mockery::mock(Builder::class);
        $settingsBuilder->shouldReceive('first')->andReturn((object)[]);

        $absensiBuilder = Mockery::mock(Builder::class);
        $absensiBuilder->shouldReceive('leftJoin')->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->andReturnSelf();
        $absensiBuilder->shouldReceive('whereMonth')->andReturnSelf();
        $absensiBuilder->shouldReceive('whereYear')->andReturnSelf();
        $absensiBuilder->shouldReceive('orderBy')->andReturnSelf();
        $absensiBuilder->shouldReceive('get')->andReturn(collect([])); 

        DB::shouldReceive('table')->with('absensi_settings')->andReturn($settingsBuilder);
        DB::shouldReceive('table')->with('absensi_member as a')->andReturn($absensiBuilder);

        $filter = [
            'member_id' => 5,
            'bulan' => '2023-10',
            'status_kehadiran' => 'hadir'
        ];

        $response = $this->absensiUsecase->exportToExcelPegawai($filter, '/redirect', 'Pegawai A');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    /**
     * TC-WP-05
     * Validasi Filter Riwayat Berdasarkan Bulan
     */
    public function test_tc_wp_05_validasi_filter_riwayat_berdasarkan_bulan()
    {
        $settingsBuilder = Mockery::mock(Builder::class);
        $settingsBuilder->shouldReceive('first')->once()->andReturn((object)[]);

        $absensiBuilder = Mockery::mock(Builder::class);
        $absensiBuilder->shouldReceive('leftJoin')->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->with('a.member_id', 5)->andReturnSelf();
        $absensiBuilder->shouldReceive('whereMonth')->andReturnSelf();
        $absensiBuilder->shouldReceive('whereYear')->andReturnSelf();
        $absensiBuilder->shouldReceive('orderBy')->andReturnSelf();
        
        $paginator = new LengthAwarePaginator(collect([]), 0, 20);

        $absensiBuilder->shouldReceive('paginate')->andReturn($paginator);

        DB::shouldReceive('table')->with('absensi_settings')->andReturn($settingsBuilder);
        DB::shouldReceive('table')->with('absensi_member as a')->andReturn($absensiBuilder);

        $response = $this->absensiUsecase->getAllAbsensi(['member_id' => 5, 'bulan' => '2023-10']);

        $this->assertArrayNotHasKey('error', $response);
    }

    /**
     * TC-WP-06
     * Validasi Filter Riwayat Berdasarkan Status kehadiran
     */
    public function test_tc_wp_06_validasi_filter_riwayat_berdasarkan_status_kehadiran()
    {
        $settingsBuilder = Mockery::mock(Builder::class);
        $settingsBuilder->shouldReceive('first')->once()->andReturn((object)[]);

        $absensiBuilder = Mockery::mock(Builder::class);
        $absensiBuilder->shouldReceive('leftJoin')->andReturnSelf();
        $absensiBuilder->shouldReceive('where')->andReturnSelf(); // For member_id and status
        $absensiBuilder->shouldReceive('orderBy')->andReturnSelf();
        
        $paginator = new LengthAwarePaginator(collect([]), 0, 20);

        $absensiBuilder->shouldReceive('paginate')->andReturn($paginator);

        DB::shouldReceive('table')->with('absensi_settings')->andReturn($settingsBuilder);
        DB::shouldReceive('table')->with('absensi_member as a')->andReturn($absensiBuilder);

        $response = $this->absensiUsecase->getAllAbsensi(['member_id' => 5, 'status_kehadiran' => 'hadir']);

        $this->assertArrayNotHasKey('error', $response);
    }
}
