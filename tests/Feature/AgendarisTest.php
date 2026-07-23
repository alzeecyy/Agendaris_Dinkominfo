<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Agenda;
use App\Models\Presensi;
use App\Models\Notulensi;
use Illuminate\Support\Facades\Hash;

class AgendarisTest extends TestCase
{
    use RefreshDatabase;

    protected $bidangAptika;
    protected $bidangIKP;
    protected $admin;
    protected $sekretarisMaster;
    protected $ketuaMaster;
    protected $sekretarisAptika;
    protected $ketuaAptika;
    protected $staffAptika;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Bidangs
        $this->bidangAptika = Bidang::create([
            'nama' => 'Bidang Aplikasi Informatika',
            'singkatan' => 'Aptika'
        ]);

        $this->bidangIKP = Bidang::create([
            'nama' => 'Bidang Informasi dan Komunikasi Publik',
            'singkatan' => 'IKP'
        ]);

        // Create Default Users
        $password = Hash::make('password');

        $this->admin = User::create([
            'name' => 'Admin User',
            'nip' => 'admin',
            'jabatan' => 'Administrator',
            'bidang_id' => null,
            'role' => 'admin',
            'password' => $password,
            'must_change_password' => false,
            'active' => true,
        ]);

        $this->sekretarisMaster = User::create([
            'name' => 'Sekretaris Master',
            'nip' => 'sekretaris.master',
            'jabatan' => 'Sekretaris Master',
            'bidang_id' => null,
            'role' => 'sekretaris_master',
            'password' => $password,
            'must_change_password' => true,
            'active' => true,
        ]);

        $this->ketuaMaster = User::create([
            'name' => 'Kepala Dinas',
            'nip' => 'ketua.master',
            'jabatan' => 'Kepala Dinas',
            'bidang_id' => null,
            'role' => 'ketua_master',
            'password' => $password,
            'must_change_password' => true,
            'active' => true,
        ]);

        $this->sekretarisAptika = User::create([
            'name' => 'Sekretaris Aptika',
            'nip' => 'sekretaris.aptika',
            'jabatan' => 'Sekretaris Aptika',
            'bidang_id' => $this->bidangAptika->id,
            'role' => 'sekretaris_bidang',
            'password' => $password,
            'must_change_password' => true,
            'active' => true,
        ]);

        $this->ketuaAptika = User::create([
            'name' => 'Ketua Aptika',
            'nip' => 'ketua.aptika',
            'jabatan' => 'Kepala Bidang Aptika',
            'bidang_id' => $this->bidangAptika->id,
            'role' => 'ketua_bidang',
            'password' => $password,
            'must_change_password' => true,
            'active' => true,
        ]);

        $this->staffAptika = User::create([
            'name' => 'Staff Aptika',
            'nip' => 'staff.aptika',
            'jabatan' => 'Staff Aptika',
            'bidang_id' => $this->bidangAptika->id,
            'role' => 'staff',
            'password' => $password,
            'must_change_password' => true,
            'active' => true,
        ]);
    }

    /**
     * Test guest redirection to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    /**
     * Test successful login.
     */
    public function test_user_can_login_with_valid_nip(): void
    {
        $response = $this->post('/login', [
            'nip' => 'admin',
            'password' => 'password'
        ]);

        $this->assertAuthenticatedAs($this->admin);
        $response->assertRedirect(route('home'));
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'nip' => 'admin',
            'password' => 'wrongpassword'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('nip');
    }

    /**
     * Test force password change redirect.
     */
    public function test_force_password_change_middleware_redirects_correctly(): void
    {
        // Login as a user who must change password
        $this->actingAs($this->staffAptika);

        // Try to access dashboard
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('password.change'));

        // Post new password
        $response = $this->post('/change-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        // Check password changed status
        $this->assertFalse($this->staffAptika->fresh()->must_change_password);
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test role-based protection.
     */
    public function test_role_middleware_restricts_access(): void
    {
        // Login as Staff (must_change_password is bypassed in this state after setting it false)
        $this->staffAptika->must_change_password = false;
        $this->staffAptika->save();
        $this->actingAs($this->staffAptika);

        // Try to access admin URL
        $response = $this->get('/admin/users');
        $response->assertStatus(403);
    }

    /**
     * Test agenda CRUD locking for Bidang Secretary allowing other bidangs scope.
     */
    public function test_bidang_secretary_can_select_other_bidang_access(): void
    {
        $this->sekretarisAptika->must_change_password = false;
        $this->sekretarisAptika->save();
        $this->actingAs($this->sekretarisAptika);

        // Post new agenda
        $response = $this->post('/agenda', [
            'judul' => 'Rapat Internal Aptika',
            'tanggal' => '2026-07-15',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
            'lokasi' => 'Ruang Aptika',
            'kategori' => 'rapat',
            'butuh_presensi' => '1',
            // Try to pass IKP bidang_id
            'bidangs' => [$this->bidangIKP->id]
        ]);

        $response->assertRedirect();
        
        $agenda = Agenda::first();
        $this->assertNotNull($agenda);
        
        // Assert hak_akses contains both the selected bidang and the secretary's own bidang
        $this->assertTrue(in_array((string)$this->bidangIKP->id, $agenda->hak_akses));
        $this->assertTrue(in_array((string)$this->sekretarisAptika->bidang_id, $agenda->hak_akses));
        $this->assertCount(2, $agenda->hak_akses);
    }

    /**
     * Test agenda CRUD restricts Bidang Secretary from choosing more than 3 bidangs.
     */
    public function test_bidang_secretary_cannot_select_more_than_three_bidangs(): void
    {
        $this->sekretarisAptika->must_change_password = false;
        $this->sekretarisAptika->save();
        $this->actingAs($this->sekretarisAptika);

        $bidangStatistik = \App\Models\Bidang::create(['nama' => 'Statistik', 'singkatan' => 'Statistik']);
        $bidangPersandian = \App\Models\Bidang::create(['nama' => 'Persandian', 'singkatan' => 'Persandian']);
        
        // 3 Bidangs is allowed
        $responseSuccess = $this->post('/agenda', [
            'judul' => 'Rapat Tiga Bidang Valid',
            'tanggal' => '2026-07-15',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
            'lokasi' => 'Ruang Rapat',
            'kategori' => 'rapat',
            'butuh_presensi' => '1',
            'bidangs' => [
                $this->sekretarisAptika->bidang_id,
                $this->bidangIKP->id,
                $bidangStatistik->id
            ]
        ]);
        $responseSuccess->assertSessionHasNoErrors();

        // 4 Bidangs is blocked (max 3 allowed)
        $responseFail = $this->post('/agenda', [
            'judul' => 'Rapat Empat Bidang Invalid',
            'tanggal' => '2026-07-15',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
            'lokasi' => 'Ruang Rapat',
            'kategori' => 'rapat',
            'butuh_presensi' => '1',
            'bidangs' => [
                $this->sekretarisAptika->bidang_id,
                $this->bidangIKP->id,
                $bidangStatistik->id,
                $bidangPersandian->id
            ]
        ]);
        $responseFail->assertSessionHasErrors('bidangs');
    }

    /**
     * Test presence submission locking.
     */
    public function test_user_attendance_is_locked_after_first_submission(): void
    {
        $this->staffAptika->must_change_password = false;
        $this->staffAptika->save();
        $this->actingAs($this->staffAptika);

        // Create an agenda requiring presence
        $agenda = Agenda::create([
            'judul' => 'Rapat Rutin',
            'tanggal' => \Carbon\Carbon::today()->toDateString(),
            'jam_mulai' => '00:00',
            'jam_selesai' => '23:30',
            'lokasi' => 'Aula',
            'kategori' => 'rapat',
            'hak_akses' => ['semua_orang'],
            'butuh_presensi' => true,
            'sekretaris_id' => $this->sekretarisAptika->id,
        ]);

        // Submit presence as hadir
        $response = $this->post("/agenda/{$agenda->id}/absen", [
            'status' => 'hadir',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertRedirect();
        $this->assertEquals('hadir', Presensi::first()->status);

        // Try to submit again as izin
        $response = $this->post("/agenda/{$agenda->id}/absen", [
            'status' => 'izin',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertSessionHas('error');
        // Status should remain 'hadir' (locked)
        $this->assertEquals('hadir', Presensi::first()->status);
    }

    /**
     * Test dashboard and calendar access for all roles.
     */
    public function test_dashboard_and_calendar_access_for_all_roles(): void
    {
        $users = [
            $this->sekretarisMaster,
            $this->ketuaMaster,
            $this->sekretarisAptika,
            $this->ketuaAptika,
            $this->staffAptika,
        ];

        foreach ($users as $user) {
            $user->must_change_password = false;
            $user->save();

            $response = $this->actingAs($user)->get('/dashboard');
            $response->assertStatus(200);

            $response = $this->actingAs($user)->get('/calendar');
            $response->assertStatus(200);
        }
    }

    /**
     * Test staff in Bidang Sekretariat has cross-bidang agenda access.
     */
    public function test_sekretariat_staff_has_cross_bidang_agenda_access(): void
    {
        $sekretariat = Bidang::create([
            'nama' => 'Sekretariat',
            'singkatan' => 'Sekretariat'
        ]);

        $staffSekretariat = User::create([
            'name' => 'Staff Sekretariat',
            'nip' => 'staff.sekretariat',
            'jabatan' => 'Staff Sekretariat',
            'bidang_id' => $sekretariat->id,
            'role' => 'staff',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'active' => true,
        ]);

        // Create an agenda restricted to Aptika bidang only
        $agendaAptika = Agenda::create([
            'judul' => 'Rapat Rintisan Aptika',
            'tanggal' => '2026-07-25',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'lokasi' => 'Ruang Aptika',
            'kategori' => 'rapat',
            'hak_akses' => [(string)$this->bidangAptika->id],
            'butuh_presensi' => true,
            'sekretaris_id' => $this->sekretarisAptika->id,
        ]);

        // Staff Sekretariat should have access to Aptika agenda
        $this->assertTrue($staffSekretariat->hasAccessToAgenda($agendaAptika));

        // Staff Sekretariat can view detail agenda page for Aptika agenda
        $response = $this->actingAs($staffSekretariat)->get("/agenda/{$agendaAptika->id}");
        $response->assertStatus(200);

        // Staff Sekretariat can access notulensi edit page for Aptika agenda
        $response = $this->actingAs($staffSekretariat)->get("/agenda/{$agendaAptika->id}/notulensi/edit");
        $response->assertStatus(200);
    }
}
