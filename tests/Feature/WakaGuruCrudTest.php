<?php

namespace Tests\Feature;

use App\Models\guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WakaGuruCrudTest extends TestCase
{
    use RefreshDatabase;

    private function waka(): User
    {
        return User::create([
            'name' => 'Waka Test',
            'email' => 'waka-crud@test.com',
            'password' => 'password',
            'role' => 'waka',
        ]);
    }

    public function test_waka_can_list_guru_menu(): void
    {
        $this->actingAs($this->waka())
            ->get('/waka/guru/data')
            ->assertOk()
            ->assertSee('Data Guru');
    }

    public function test_waka_can_create_update_and_delete_guru(): void
    {
        $waka = $this->waka();

        $this->actingAs($waka)
            ->post('/waka/guru', [
                'name' => 'Guru Baru',
                'email' => 'guru-baru@test.com',
                'password' => 'password123',
                'nip' => '1234567890',
                'no_hp' => '08123456789',
            ])
            ->assertRedirect(route('waka.guru.data'));

        $created = User::where('email', 'guru-baru@test.com')->firstOrFail();
        $this->assertSame('guru', $created->role);
        $profile = guru::where('user_id', $created->id)->firstOrFail();
        $this->assertSame('1234567890', $profile->nip);

        $this->actingAs($waka)
            ->get(route('waka.guru.edit', $profile))
            ->assertOk()
            ->assertSee('Guru Baru');

        $this->actingAs($waka)
            ->put(route('waka.guru.update', $profile), [
                'name' => 'Guru Diperbarui',
                'email' => 'guru-baru@test.com',
                'password' => '',
                'nip' => '1234567890',
                'no_hp' => '08999999999',
            ])
            ->assertRedirect(route('waka.guru.data'));

        $this->assertSame('Guru Diperbarui', $created->fresh()->name);
        $this->assertSame('08999999999', $profile->fresh()->no_hp);

        $this->actingAs($waka)
            ->delete(route('waka.guru.destroy', $profile))
            ->assertRedirect(route('waka.guru.data'));

        $this->assertDatabaseMissing('guru', ['id' => $profile->id]);
        $this->assertDatabaseMissing('users', ['id' => $created->id]);
    }

    public function test_waka_cannot_manage_guru_with_invalid_data(): void
    {
        $this->actingAs($this->waka())
            ->post('/waka/guru', [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
            ])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
