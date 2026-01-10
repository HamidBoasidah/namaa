<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Consultant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition()
    {
        // create an Arabic original filename (words in Arabic)
        $arabicWords = ['شهادة', 'وثيقة', 'مستند', 'هوية', 'جواز', 'تصريح', 'سند'];
        $originalBase = $this->faker->randomElement($arabicWords) . '_' . $this->faker->randomElement($arabicWords);
        $originalName = $originalBase . '.jpg';

        return [
            'consultant_id' => Consultant::factory(),
            'status' => 'pending',
            'rejected_reason' => null,
            'is_verified' => $this->faker->boolean(30),
            'verified_at' => $this->faker->boolean(30) ? now() : null,
            // store a placeholder filename (we don't write actual files during seeding)
            'document_scan_copy' => $this->faker->randomElement(['Certificates']) . '/' . Str::uuid() . '.jpg',
            // original uploaded filename (Arabic)
            'document_scan_copy_original_name' => $originalName,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
