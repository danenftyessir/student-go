<?php
// restore problem_images.image_path, caption, updated_at from the package H backup CSV.
// usage (from the repo root): php h2_restore.php <backup.csv>            -> dry run, counts differing rows
//                             php h2_restore.php <backup.csv> --apply    -> writes in one transaction, then verifies
$root = getcwd();
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$file = $argv[1];
$apply = in_array('--apply', $argv, true);
$f = fopen($file, 'r');
$head = fgetcsv($f);
$backup = [];
while (($line = fgetcsv($f)) !== false) {
    $row = array_combine($head, $line);
    $backup[(int) $row['id']] = $row;
}
fclose($f);

$diff = function () use ($backup) {
    $current = DB::table('problem_images')->whereIn('id', array_keys($backup))->get()->keyBy('id');
    $n = 0;
    foreach ($backup as $id => $row) {
        $c = $current[$id] ?? null;
        if (! $c || $c->image_path !== $row['image_path'] || (string) $c->caption !== $row['caption']
            || (string) $c->updated_at !== $row['updated_at']) {
            $n++;
        }
    }
    return [$n, count($current)];
};

[$before, $found] = $diff();
echo "backup rows: ".count($backup).", present: {$found}, differing: {$before}\n";
if (! $apply) {
    exit(0);
}
DB::transaction(function () use ($backup) {
    foreach ($backup as $id => $row) {
        DB::table('problem_images')->where('id', $id)->update([
            'image_path' => $row['image_path'],
            'caption' => $row['caption'] === '' ? null : $row['caption'],
            'updated_at' => $row['updated_at'],
        ]);
    }
});
[$after] = $diff();
echo "restored; differing after restore: {$after}\n";
