<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function query()
    {
        return $this->model->newQuery();
    }

    public function all(array $with = [])
    {
        return $this->query()->with($with)->latest()->get();
    }

    public function paginate(int $perPage = 15, array $with = [])
    {
        return $this->query()->with($with)->latest()->paginate($perPage);
    }

    public function find(int|string $id, array $with = [])
    {
        return $this->query()->with($with)->find($id);
    }

    public function findOrFail(int|string $id, array $with = [])
    {
        return $this->query()->with($with)->findOrFail($id);
    }

    public function create(array $attributes)
    {
        $attributes = $this->handleFileUploads($attributes);
        return $this->model->create($attributes);
    }

    public function update(int|string $id, array $attributes)
    {
        $record = $this->findOrFail($id);
        $attributes = $this->handleFileUploads($attributes, $record);
        $record->update($attributes);
        return $record;
    }

    public function delete(int|string $id): bool
    {
        $record = $this->findOrFail($id);
        return (bool) $record->delete();
    }

    public function activate(int|string $id)
    {
        $record = $this->findOrFail($id);
        $record->update(['is_active' => true]);
        return $record;
    }

    public function deactivate(int|string $id)
    {
        $record = $this->findOrFail($id);
        $record->update(['is_active' => false]);
        return $record;
    }

    /**
     * يعالج رفع الملفات ويستبدل كائن الملف بالمسار.
     *
     * @param array $attributes
     * @param Model|null $record السجل الحالي (يستخدم في التحديث لحذف الملف القديم)
     * @return array
     */
    protected function handleFileUploads(array $attributes, ?Model $record = null): array
    {
        foreach ($attributes as $key => &$value) {
            if ($value instanceof UploadedFile) {
                // في حالة التحديث، احذف الملف القديم إذا كان موجودًا
                if ($record && $record->{$key} && Storage::disk('public')->exists($record->{$key})) {
                    Storage::disk('public')->delete($record->{$key});
                }

                // قم بتخزين الملف الجديد في مجلد يعتمد على اسم جدول النموذج باستخدام اسم ملف فريد UUID
                $filename = (string) Str::uuid() . '.' . $value->getClientOriginalExtension();
                $path = $value->storeAs($this->model->getTable(), $filename, 'public');

                $value = $path; // استبدل كائن الملف بالمسار
            }
        }

        return $attributes;
    }

    /**
     * يخزن ملفًا خاصًا في تخزين محلي (storage/app) باستخدام اسم ملف UUID.
     *
     * @param UploadedFile $file
     * @param string|null $oldPath
     * @param string $directory مجلد داخل storage/app، مثال: 'private' أو 'private/kyc'
     * @return string|null
     */
    protected function storePrivateFile(UploadedFile $file, ?string $oldPath = null, string $directory = 'private'): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $disk = Storage::disk('local');

        if ($oldPath && $disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }

        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = trim($directory, '/');
        $storedPath = $file->storeAs($fullPath, $filename, 'local');

        return $storedPath;
    }

    /**
     * يحذف ملفًا من التخزين الخاص (local disk) إن وجد.
     */
    protected function deletePrivateFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        $disk = Storage::disk('local');

        if ($disk->exists($path)) {
            return $disk->delete($path);
        }

        return false;
    }
}
