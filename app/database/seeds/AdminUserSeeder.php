<?php
use App\Seeder;

return new class extends Seeder {
    public function run() {
        $this->table('users')->insert([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'phone' => '0000000000',
            'username' => 'admin',
            'password' => password_hash('admin', PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->table('settings')->insert([
            ['key' => 'APP_NAME', 'value1' => 'Mini Lara', 'created_at' => date('Y-m-d H:i:s')],
            ['key' => 'APP_TAGLINE', 'value1' => 'Clean & Scalable System', 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }
};
