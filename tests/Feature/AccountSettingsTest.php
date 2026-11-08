<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('owner_name', 100)->nullable();
            $table->string('shop_name', 150);
            $table->string('email', 120)->unique();
            $table->string('password_hash');
            $table->rememberToken();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('datasets', function (Blueprint $table) {
            $table->bigIncrements('dataset_id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->foreign('user_id')->references('user_id')->on('users')->restrictOnDelete();
        });
        $this->withoutVite();
    }

    private function account(string $email = 'owner@example.com'): User
    {
        return User::create([
            'owner_name' => 'Chủ cửa hàng',
            'shop_name' => 'Cửa hàng mẫu',
            'email' => $email,
            'password_hash' => Hash::make('current-password'),
        ]);
    }

    public function test_guests_cannot_access_or_change_account_settings(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'), [])->assertRedirect(route('login'));
        $this->put(route('password.update'), [])->assertRedirect(route('login'));
        $this->delete(route('profile.destroy'), [])->assertRedirect(route('login'));
    }

    public function test_account_page_uses_business_layout_and_escapes_user_information(): void
    {
        $user = $this->account();
        $user->update(['shop_name' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('Thông tin tài khoản')->assertSee('Đổi mật khẩu')
            ->assertSee('Đăng xuất')->assertSee('Tổng quan kinh doanh')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_owner_can_update_profile_without_changing_another_user_or_password(): void
    {
        $user = $this->account();
        $other = $this->account('other@example.com');
        $originalHash = $user->password_hash;

        $this->actingAs($user)->patch(route('profile.update'), [
            'owner_name' => 'Nguyễn An', 'shop_name' => 'Cửa hàng An', 'email' => 'an@example.com',
            'user_id' => $other->getKey(), 'password_hash' => 'injected',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('users', [
            'user_id' => $user->getKey(), 'owner_name' => 'Nguyễn An',
            'shop_name' => 'Cửa hàng An', 'email' => 'an@example.com', 'password_hash' => $originalHash,
        ]);
        $this->assertSame('other@example.com', $other->fresh()->email);
    }

    public function test_owner_can_keep_their_existing_email(): void
    {
        $user = $this->account();

        $this->actingAs($user)->patch(route('profile.update'), [
            'owner_name' => 'Tên mới', 'shop_name' => $user->shop_name, 'email' => $user->email,
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $this->assertSame('Tên mới', $user->fresh()->owner_name);
    }

    public function test_duplicate_email_does_not_change_profile(): void
    {
        $user = $this->account();
        $other = $this->account('other@example.com');

        $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [
            'owner_name' => 'Changed', 'shop_name' => 'Changed', 'email' => $other->email,
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors([
            'email' => 'Email này đã được sử dụng bởi tài khoản khác.',
        ]);

        $this->assertSame('owner@example.com', $user->fresh()->email);
        $this->assertSame('Chủ cửa hàng', $user->fresh()->owner_name);
    }

    public function test_missing_profile_fields_are_rejected(): void
    {
        $user = $this->account();

        $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [])
            ->assertRedirect(route('profile.edit'))->assertSessionHasErrors(['owner_name', 'shop_name', 'email']);

        $this->assertSame('Cửa hàng mẫu', $user->fresh()->shop_name);
    }

    public function test_password_change_persists_hash_and_allows_login_with_new_password(): void
    {
        $user = $this->account();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'current-password', 'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password_hash));
        $this->assertFalse(Hash::check('current-password', $user->fresh()->password_hash));
        $this->post(route('logout'))->assertRedirect('/');
        $this->post(route('login'), ['email' => $user->email, 'password' => 'new-password-123'])
            ->assertSessionHasNoErrors()->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_current_password_cannot_change_password(): void
    {
        $user = $this->account();
        $originalHash = $user->password_hash;

        $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'wrong-password', 'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrorsIn('updatePassword', [
            'current_password' => 'Mật khẩu hiện tại không đúng.',
        ]);

        $this->assertSame($originalHash, $user->fresh()->password_hash);
    }

    public function test_mismatched_password_confirmation_is_rejected(): void
    {
        $user = $this->account();
        $originalHash = $user->password_hash;

        $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'current-password', 'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrorsIn('updatePassword', ['password']);

        $this->assertSame($originalHash, $user->fresh()->password_hash);
    }

    public function test_logout_clears_selected_dataset_and_protects_profile(): void
    {
        $user = $this->account();

        $this->actingAs($user)->withSession(['dataset_id' => 42])->post(route('logout'))
            ->assertRedirect('/')->assertSessionMissing('dataset_id');

        $this->assertGuest();
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_account_with_owned_datasets_cannot_be_deleted_and_remains_signed_in(): void
    {
        $user = $this->account();
        DB::table('datasets')->insert(['user_id' => $user->getKey(), 'name' => 'Business data']);

        $this->actingAs($user)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'current-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrorsIn('userDeletion', ['password']);

        $this->assertModelExists($user);
        $this->assertDatabaseCount('datasets', 1);
        $this->assertAuthenticatedAs($user);
    }

    public function test_account_without_data_can_be_deleted_after_password_confirmation(): void
    {
        $user = $this->account();

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'current-password'])
            ->assertSessionHasNoErrors()->assertRedirect('/');

        $this->assertModelMissing($user);
        $this->assertGuest();
    }

    public function test_linked_records_prevent_deletion_without_logging_out_the_owner(): void
    {
        $user = $this->account();
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uploaded_by');
            $table->foreign('uploaded_by')->references('user_id')->on('users')->restrictOnDelete();
        });
        DB::table('import_logs')->insert(['uploaded_by' => $user->getKey()]);

        $this->actingAs($user)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'current-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrorsIn('userDeletion', ['password']);

        $this->assertModelExists($user);
        $this->assertDatabaseCount('import_logs', 1);
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_cannot_delete_account(): void
    {
        $user = $this->account();

        $this->actingAs($user)->from(route('profile.edit'))->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasErrorsIn('userDeletion', ['password']);

        $this->assertModelExists($user);
        $this->assertAuthenticatedAs($user);
    }
}
