# Requirements Document

## Introduction

هذه الميزة تهدف إلى الحفاظ على اسم الملف الأصلي الذي يرفعه المستخدم للعرض، مع استخدام اسم داخلي آمن (UUID) للتخزين الفعلي. هذا النهج يوفر:
- **الأمان**: تجنب مشاكل الأسماء الخبيثة أو غير الآمنة
- **عدم التعارض**: UUID يضمن عدم تكرار الأسماء
- **تجربة مستخدم أفضل**: المستخدم يرى اسم ملفه الأصلي
- **قابلية التوسع**: سهولة إضافة ميزات مستقبلية

## Glossary

- **File_Upload_System**: نظام رفع الملفات في BaseRepository المسؤول عن تخزين الملفات
- **Original_Filename**: اسم الملف الأصلي الذي يختاره المستخدم عند الرفع (للعرض)
- **Stored_Filename**: اسم الملف المخزن فعلياً على القرص (UUID-based)
- **Display_Filename**: الاسم المعروض للمستخدم (الاسم الأصلي)
- **Storage_Disk**: قرص التخزين (public أو local/private)
- **File_Metadata**: البيانات الوصفية للملف تشمل الاسم الأصلي والمسار المخزن

## Requirements

### Requirement 1: Dual Filename Storage

**User Story:** As a system architect, I want to separate the stored filename from the display filename, so that the system is secure, scalable, and user-friendly.

#### Acceptance Criteria

1. WHEN a user uploads a file, THE File_Upload_System SHALL store the file using a UUID-based Stored_Filename for security
2. WHEN a user uploads a file, THE File_Upload_System SHALL capture and store the Original_Filename as metadata for display purposes
3. THE File_Upload_System SHALL return both the Stored_Filename (path) and the Original_Filename in the response
4. WHEN displaying file information to users, THE system SHALL show the Original_Filename (Display_Filename)

### Requirement 2: Original Filename Capture

**User Story:** As a user, I want to see my original filename when viewing uploaded files, so that I can easily identify them.

#### Acceptance Criteria

1. WHEN the Original_Filename contains Arabic characters, THE File_Upload_System SHALL preserve those characters in the stored metadata
2. WHEN the Original_Filename contains English characters, THE File_Upload_System SHALL preserve those characters in the stored metadata
3. THE File_Upload_System SHALL preserve the original file extension in the metadata
4. WHEN downloading a file, THE system SHALL use the Original_Filename as the download filename

### Requirement 3: Filename Sanitization for Display

**User Story:** As a system administrator, I want display filenames to be sanitized for safe rendering, so that the UI remains secure.

#### Acceptance Criteria

1. WHEN storing the Original_Filename, THE File_Upload_System SHALL sanitize it by removing potentially harmful characters for display
2. WHEN the Original_Filename contains HTML/script tags, THE File_Upload_System SHALL escape or remove them
3. THE sanitized Display_Filename SHALL maintain readability while ensuring safe rendering

### Requirement 4: Database Schema Update

**User Story:** As a developer, I want the database to store both filenames, so that I can retrieve and display them correctly.

#### Acceptance Criteria

1. THE database schema SHALL include a column for the Original_Filename alongside the existing file path column
2. WHEN migrating existing data, THE system SHALL use the stored filename as the Original_Filename for backward compatibility
3. THE File_Upload_System SHALL support models with and without the Original_Filename column (graceful degradation)

### Requirement 5: Storage Compatibility

**User Story:** As a developer, I want the dual filename system to work with both public and private storage, so that the feature is consistent.

#### Acceptance Criteria

1. WHEN storing files on the public Storage_Disk, THE File_Upload_System SHALL apply the same dual filename logic
2. WHEN storing files on the private (local) Storage_Disk, THE File_Upload_System SHALL apply the same dual filename logic
3. THE File_Upload_System SHALL maintain backward compatibility with existing file retrieval methods

### Requirement 6: Edge Cases

**User Story:** As a system, I want to handle edge cases gracefully, so that the upload process never fails.

#### Acceptance Criteria

1. IF the Original_Filename is empty, THEN THE File_Upload_System SHALL use a default name like "unnamed_file"
2. IF the Original_Filename exceeds 255 characters, THEN THE File_Upload_System SHALL truncate it while preserving the extension
3. WHEN the filename contains only the extension (e.g., `.pdf`), THE File_Upload_System SHALL prepend "file" to the name
