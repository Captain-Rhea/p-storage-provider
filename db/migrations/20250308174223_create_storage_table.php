<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStorageTable extends AbstractMigration
{
    public function change(): void
    {
        // Table: api_connection
        $table = $this->table('api_connection', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true])
            ->addColumn('connection_name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('connection_key', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['connection_key'], ['unique' => true, 'name' => 'idx_connection_key'])
            ->create();

        // Table: tb_files
        $table = $this->table('tb_files', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true])
            ->addColumn('group', 'string', ['limit' => 100, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('file_name', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('file_description', 'string', ['limit' => 255, 'null' => true, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('file_path', 'string', ['limit' => 512, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('file_url', 'text', ['null' => true, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('file_size', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('file_type', 'string', ['limit' => 50, 'null' => true, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('created_by', 'string', ['limit' => 100, 'null' => true, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_by', 'string', ['limit' => 100, 'null' => true, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['group'], ['name' => 'idx_group'])
            ->addIndex(['file_type'], ['name' => 'idx_file_type'])
            ->addIndex(['created_at'], ['name' => 'idx_created_at'])
            ->addIndex(['created_by'], ['name' => 'idx_created_by'])
            ->addIndex(['file_name', 'file_description'], ['type' => 'fulltext', 'name' => 'idx_fulltext_file'])
            ->create();

        // Table: tb_files_type_config
        $table = $this->table('tb_files_type_config', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'integer', ['identity' => true])
            ->addColumn('file_type', 'string', ['limit' => 50, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('mime_type', 'string', ['limit' => 255, 'null' => false, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();
    }
}
