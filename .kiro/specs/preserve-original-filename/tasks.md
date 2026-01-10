# Implementation Plan: Preserve Original Filename

## Overview

تنفيذ نظام فصل اسم الملف المخزن (UUID) عن اسم الملف المعروض (Original Name) في نظام رفع الملفات.

## Tasks

- [x] 1. Create FilenameHelper class
  - Create `app/Support/FilenameHelper.php`
  - Implement `sanitizeForDisplay()` method for safe display
  - Implement `getFallbackName()` for empty filenames
  - Implement `ensureValidName()` for edge cases
  - _Requirements: 3.1, 3.2, 6.1, 6.2, 6.3_

- [ ]* 1.1 Write property test for sanitization idempotence
  - **Property 5: Sanitization Idempotence**
  - **Validates: Requirements 3.1, 3.2, 3.3**

- [ ]* 1.2 Write property test for sanitization safety
  - **Property 2: Sanitization Safety**
  - **Validates: Requirements 3.1, 3.2**

- [x] 2. Create database migration for certificates table
  - Add `document_scan_copy_original_name` column
  - Make it nullable for backward compatibility
  - _Requirements: 4.1, 4.2_

- [x] 3. Update BaseRepository to store original filename
  - [x] 3.1 Add `modelHasColumn()` helper method
    - Check if model's table has a specific column
    - _Requirements: 4.3_

  - [x] 3.2 Modify `handleFileUploads()` method
    - Capture original filename from UploadedFile
    - Sanitize using FilenameHelper
    - Store original name in `{field}_original_name` column if exists
    - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3_

- [ ]* 3.3 Write property test for original name preservation
  - **Property 1: Original Name Preservation**
  - **Validates: Requirements 2.1, 2.2, 2.3**

- [ ]* 3.4 Write property test for UUID storage uniqueness
  - **Property 3: UUID Storage Uniqueness**
  - **Validates: Requirements 1.1, 1.2**

- [x] 4. Update Certificate model
  - Add `document_scan_copy_original_name` to fillable array
  - _Requirements: 4.1_

- [x] 5. Update CertificateDTO
  - Include `document_scan_copy_original_name` in DTO
  - _Requirements: 1.3_

- [x] 6. Update CertificateService download method
  - [x] 6.1 Modify `streamDocument()` to use original filename
    - Use original_name for Content-Disposition header
    - Support UTF-8 filenames (RFC 5987)
    - Fallback to stored filename if original_name is null
    - _Requirements: 2.4_

- [ ]* 6.2 Write property test for download name consistency
  - **Property 4: Download Name Consistency**
  - **Validates: Requirements 2.4**

- [x] 7. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Update frontend to display original filename
  - [x] 8.1 Update Certificate views to show original filename
    - Show original_name in Index, Show, Edit views
    - Fallback to path basename if original_name is null
    - _Requirements: 1.4, 2.4_

- [x] 9. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- The implementation maintains backward compatibility with existing files
