<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::table('users')->get();
foreach ($users as $u) {
    if (empty($u->name)) continue;
    $firstName = explode(' ', $u->name)[0];
    
    $m = DB::table('members')->where('name', 'like', $firstName.'%')->first();
    if ($m && $u->member_id != $m->id) {
        DB::table('users')->where('id', $u->id)->update(['member_id' => $m->id]);
        echo "Updated user {$u->name} ({$u->id}) member_id from {$u->member_id} to {$m->id}\n";
    }
}
echo "Done!\n";
