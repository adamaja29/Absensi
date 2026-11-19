<?php

namespace Tests\Feature;

use App\Models\guru;
use App\Models\IzinGuru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IzinBuktiAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private function seedIzin(string $buktiFile = 'bukti_izin/surat.pdf'): array
    {
        $waka = User::create([
            'name' => 'Waka Test',
            'email' => 'waka-izin@test.com',
            'password' => 'password',
            'role' => 'waka',
        ]);

        $ownerUser = User::create([
            'name' => 'Guru Pemilik',
            'email' => 'guru-pemilik@test.com',
            'password' => 'password',
            'role' => 'guru',
        ]);
        $owner = guru::create(['user_id' => $ownerUser->id, 'nip' => '111']);

        $otherUser = User::create([
            'name' => 'Guru Lain',
            'email' => 'guru-lain@test.com',
            'password' => 'password',
            'role' => 'guru',
        ]);
        $other = guru::create(['user_id' => $otherUser->id, 'nip' => '222']);

        $izin = IzinGuru::create([
            'guru_id' => $owner->id,
            'tanggal_mulai' => '2026-11-20',
            'tanggal_selesai' => '2026-11-20',
            'jenis' => 'sakit',
            'alasan' => 'Demam',
            'bukti_file' => $buktiFile,
            'status' => 'menunggu',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('bukti_izin/surat.pdf', '%PDF-1.4 lampiran uji');

        return compact('waka', 'ownerUser', 'otherUser', 'izin');
    }

    public function test_waka_can_view_guru_attachment(): void
    {
        $data = $this->seedIzin();

        $this->actingAs($data['waka'])
            ->get(route('waka.izin.bukti', $data['izin']))
            ->assertOk();
    }

    public function test_guru_owner_can_view_own_attachment(): void
    {
        $data = $this->seedIzin();

        $this->actingAs($data['ownerUser'])
            ->get(route('guru.izin.bukti', $data['izin']))
            ->assertOk();
    }

    public function test_other_guru_cannot_view_attachment(): void
    {
        $data = $this->seedIzin();

        $this->actingAs($data['otherUser'])
            ->get(route('guru.izin.bukti', $data['izin']))
            ->assertForbidden();
    }

    public function test_missing_attachment_returns_not_found(): void
    {
        $data = $this->seedIzin();

        Storage::disk('public')->delete('bukti_izin/surat.pdf');

        $this->actingAs($data['waka'])
            ->get(route('waka.izin.bukti', $data['izin']))
            ->assertNotFound();
    }

    public function test_waka_page_links_to_secure_attachment_route(): void
    {
        $data = $this->seedIzin();

        $response = $this->actingAs($data['waka'])->get(route('waka.izin.data'));

        $response->assertOk();
        $response->assertSee(route('waka.izin.bukti', $data['izin']), false);
        $response->assertDontSee('storage/'.$data['izin']->bukti_file, false);
    }
}
