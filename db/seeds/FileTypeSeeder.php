<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class FileTypeSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            ['file_type' => 'image', 'mime_type' => 'image/jpeg', 'description' => 'JPEG image'],
            ['file_type' => 'image', 'mime_type' => 'image/png', 'description' => 'PNG image'],
            ['file_type' => 'image', 'mime_type' => 'image/webp', 'description' => 'WebP image'],
            ['file_type' => 'image', 'mime_type' => 'image/gif', 'description' => 'GIF image'],
            ['file_type' => 'image', 'mime_type' => 'image/svg+xml', 'description' => 'SVG image'],
            ['file_type' => 'document', 'mime_type' => 'application/pdf', 'description' => 'PDF document'],
            ['file_type' => 'document', 'mime_type' => 'application/msword', 'description' => 'Microsoft Word document (.doc)'],
            ['file_type' => 'document', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'description' => 'Word (.docx)'],
            ['file_type' => 'spreadsheet', 'mime_type' => 'application/vnd.ms-excel', 'description' => 'Microsoft Excel (.xls)'],
            ['file_type' => 'spreadsheet', 'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'description' => 'Excel (.xlsx)'],
            ['file_type' => 'presentation', 'mime_type' => 'application/vnd.ms-powerpoint', 'description' => 'PowerPoint (.ppt)'],
            ['file_type' => 'audio', 'mime_type' => 'audio/mpeg', 'description' => 'MP3 audio'],
            ['file_type' => 'audio', 'mime_type' => 'audio/wav', 'description' => 'WAV audio'],
            ['file_type' => 'audio', 'mime_type' => 'audio/ogg', 'description' => 'OGG audio'],
            ['file_type' => 'video', 'mime_type' => 'video/mp4', 'description' => 'MP4 video'],
            ['file_type' => 'video', 'mime_type' => 'video/webm', 'description' => 'WebM video'],
            ['file_type' => 'video', 'mime_type' => 'video/ogg', 'description' => 'OGG video'],
            ['file_type' => 'archive', 'mime_type' => 'application/zip', 'description' => 'ZIP archive'],
            ['file_type' => 'text', 'mime_type' => 'text/plain', 'description' => 'Plain text files (.txt)'],
        ];

        $this->table('tb_files_type_config')->insert($data)->save();
    }
}
