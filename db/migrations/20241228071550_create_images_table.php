<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateImagesTable extends AbstractMigration
{
    public function change(): void
    {
        // สร้างตาราง images
        $imagesTable = $this->table('images', ['id' => 'image_id']);
        $imagesTable->addColumn('group', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('path', 'string', ['limit' => 512, 'null' => true])
            ->addColumn('base_url', 'text', ['null' => true])
            ->addColumn('lazy_url', 'text', ['null' => true])
            ->addColumn('base_size', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('lazy_size', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('uploaded_by', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('uploaded_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addIndex(['uploaded_by'], ['name' => 'idx_uploaded_by'])
            ->create();
    }
}
