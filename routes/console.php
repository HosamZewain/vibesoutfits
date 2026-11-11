<?php

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

Artisan::command('vibe:admin {email?}', function (): int {
    $email = Str::lower(trim($this->argument('email') ?: $this->ask('Admin email')));
    $password = $this->secret('New password (12 characters minimum)');
    $validator = Validator::make(compact('email', 'password'), ['email' => 'required|email:rfc|max:150', 'password' => 'required|string|min:12|max:72']);
    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return 1;
    }

    $user = User::firstOrNew(['email' => $email]);
    $user->forceFill(['name' => $user->name ?? config('app.name').' Admin', 'password' => $password, 'is_admin' => true])->save();
    $this->info('Admin saved: '.$email);

    return 0;
})->purpose('Create an admin or reset an admin password');

Artisan::command('vibe:prune-uploads', function (): void {
    $disk = Storage::disk('local');
    $cutoff = now()->subDays(7)->timestamp;
    $count = 0;
    foreach ($disk->files('customer-artwork') as $file) {
        if ($disk->lastModified($file) < $cutoff && ! OrderItem::where('upload_path', $file)->exists()) {
            $count += (int) $disk->delete($file);
        }
    }
    $this->info('Removed '.$count.' abandoned cart uploads.');
})->purpose('Remove customer uploads abandoned in old carts')->daily()->withoutOverlapping();
