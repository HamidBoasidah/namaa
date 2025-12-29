<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;



class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // لا حاجة لعمليات bind يدوية عند استخدام أصناف ملمّحة بشكل صريح (Concrete classes).
        // يعتمد Laravel تلقائياً على Service Container لتهيئة الأصناف وحقن اعتماداتها
        // عندما تكون المُنشئات مَلمَّحة (e.g. Service __construct(Repo $repo), Repo __construct(Model $model)).

        // إذا كنت لاحقاً ستستخدم واجهات (Interfaces) وتريد ربطها بتنفيذاتها (Implementations)،
        // عندها يمكنك إضافة bind هنا بصورة ديناميكية أو باستخدام مصفوفات mapping.
    }
}
