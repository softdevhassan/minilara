<?php
use App\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        $this->schema->create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value1')->nullable();
            $table->text('value2')->nullable();
            $table->text('value3')->nullable();
            $table->integer('last_updated_by')->default(1);
            $table->timestamps();
        });
    }

    public function down() {
        $this->schema->dropIfExists('settings');
    }
};
