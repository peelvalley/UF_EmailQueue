<?php

namespace UserFrosting\Sprinkle\EmailQueue\Database\Migrations\v100;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class MailingQueueTable extends Migration
{

    /**
    * {@inheritdoc}
    */
    public function up()
    {
        if (!$this->schema->hasTable('mailing_queue')) {
            $this->schema->create('mailing_queue', function (Blueprint $table) {
                $table->increments('id');
                $table->string('template', 255)->nullable();
                $table->json('recipients')->nullable();
                $table->json('from')->nullable();
                $table->json('data')->nullable();
                $table->json('metadata')->nullable();
                $table->json('attachments')->nullable();
                $table->string('to_email', 255)->virtualAs('`recipients` ->> "$[0].email"');
                $table->string('to_name', 255)->virtualAs('`recipients` ->> "$[0].name"');

                $table->index('to_email');
                $table->index('to_name');

                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8mb4_unicode_520_ci';
                $table->charset = 'utf8mb4';
            });
        }
    }

    /**
    * {@inheritdoc}
    */
    public function down()
    {
        if ($this->schema->hasTable('mailing_queue')) {
            $this->schema->drop('mailing_queue');
        }
    }
}