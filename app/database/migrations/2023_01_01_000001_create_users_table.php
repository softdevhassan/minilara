<?php
use App\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('image')->default('/images/default-avatar.webp');
            $table->longText('details')->nullable();
            $table->longText('allowed_routes')->nullable();
            $table->tinyInteger('locked')->default(0);
            $table->text('color_scheme')->nullable();
            $table->integer('last_updated_by')->default(1);
            $table->timestamps();
        });
    }

    public function down() {
        $this->schema->dropIfExists('users');
    }
};
