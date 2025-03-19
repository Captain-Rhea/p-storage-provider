<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStorageTable extends AbstractMigration
{
    public function change(): void
    {
        // สร้างตาราง api_connection
        $table = $this->table('api_connection', ['id' => 'id']);
        $table->addColumn('connection_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('connection_key', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['connection_key'], ['unique' => true, 'name' => 'idx_connection_key'])
            ->create();

        // สร้างตาราง folder
        $table = $this->table('folder', ['id' => 'id']);
        $table->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('parent_id', 'integer', ['default' => 0, 'signed' => false])
            ->addColumn('path', 'string', ['limit' => 512, 'null' => true])
            ->addColumn('created_by', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('updated_by', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['parent_id'], ['name' => 'idx_parent_id'])
            ->create();

        // สร้างตาราง files
        $table = $this->table('files', ['id' => 'id']);
        $table->addColumn('folder_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('file_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('file_path', 'string', ['limit' => 512, 'null' => false])
            ->addColumn('file_size', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('file_type', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('created_by', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_by', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false]) // เวลาอัปเดต
            ->addIndex(['folder_id'], ['name' => 'idx_folder_id'])
            ->addIndex(['file_type'], ['name' => 'idx_file_type'])
            ->addForeignKey('folder_id', 'folder', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
