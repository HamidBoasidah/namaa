<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password 
                            {--email= : البريد الإلكتروني للأدمن}
                            {--password=password : كلمة المرور الجديدة}';

    protected $description = 'إعادة تعيين كلمة مرور أدمن (مفيد بعد إصلاح التهشير المزدوج)';

    public function handle(): int
    {
        $email = $this->option('email') ?? env('ADMIN_EMAIL', 'admin@example.com');
        $password = $this->option('password');

        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            $this->error("لا يوجد أدمن بالبريد: {$email}");

            return self::FAILURE;
        }

        $admin->password = $password;
        $admin->save();

        $this->info("تم تحديث كلمة مرور الأدمن ({$email}) بنجاح.");

        return self::SUCCESS;
    }
}
