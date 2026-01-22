<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attachment Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file attachment limits and allowed types for chat messages.
    | These settings control what files users can upload in conversations.
    |
    */

    'attachments' => [
        // Maximum number of files that can be attached to a single message
        'max_files_per_message' => env('CHAT_MAX_FILES', 5),

        // Maximum file size in megabytes for each attachment
        'max_file_size_mb' => env('CHAT_MAX_FILE_SIZE_MB', 25),

        // Allowed MIME types for file uploads
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Limits
    |--------------------------------------------------------------------------
    |
    | Configure message sending limits for clients and consultants.
    | Clients are limited to a specific number of out-of-session messages,
    | while consultants have unlimited messaging.
    |
    */

    'limits' => [
        // Maximum number of out-of-session messages a client can send
        // (applies to messages sent before or after the booking session window)
        'client_out_of_session_messages' => 2,

        // Maximum length of message body text in characters
        'max_message_length' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Configure pagination settings for message listings.
    |
    */

    'pagination' => [
        // Default number of messages per page when listing conversation messages
        'messages_per_page' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where and how chat attachments are stored.
    | Attachments must be stored on a private disk to ensure security.
    |
    */

    'storage' => [
        // Storage disk for chat attachments (must be private, not publicly accessible)
        'disk' => 'private',

        // Path within the storage disk where attachments will be stored
        'path' => 'chat-attachments',
    ],

];
