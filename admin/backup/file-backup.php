<?php
// admin/backup/file-backup.php
require_once __DIR__ . '/../includes/admin_functions.php';

$backupDir = ROOT_PATH . 'backup/files/';
if (!is_dir($backupDir)) { mkdir($backupDir, backup/file-backup.php`

```php
<?php
// admin/backup/file-backup.php
require_once __DIR__ . '/../includes/admin_functions.php';

$backupDir = ROOT_PATH . 'backup/files/';
if (!is_dir($backupDir)) { mkdir($backupDir, 0755, true); }

$message = '';

// Create file backup (zip)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_file_backup'])) {
    $filename = 'files_backup_' . date('Y-m-d_H-i-s') . '.zip';
    $filepath = $backupDir . $filename;

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if0755, true); }

$message = '';

// Create file backup (zip)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_file_backup'])) {
    $filename = 'files_backup_' . date('Y-m-d_H-i-s') . '.zip';
    $filepath = $backupDir . $filename;

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE) === TRUE) {
            $rootPath = realpath(ROOT_PATH);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Skip backup directories and logs ($zip->open($filepath, ZipArchive::CREATE) === TRUE) {
            $rootPath = realpath(ROOT_PATH);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Skip backup directories and logs/c/cacheache
                if
                if (strpos($ (strpos($relativePathrelativePath, ', 'backupbackup/') === 0 ||/') === 0 || strpos strpos($relative($relativePath,Path, 'logs/') 'logs === /') === 0 ||0 || strpos strpos($relative($relativePath,Path, 'cache/') 'cache/') ===  === 0)0) {
                    {
                    continue continue;
               ;
                }
                $ }
                $zip->zip->addFileaddFile($file($filePath,Path, $relative $relativePathPath);
            }
            $);
            }
            $zip->closezip->();
            $close();
            $message =message = '<div '<div class=" class="alert alertalert alert-success">-success">File backupFile backup created: created: ' . ' . $filename $filename . '</ . '</divdiv>';
        }>';
        } else else {
            $ {
            $message =message = '<div '<div class=" class="alertalert alert alert-danger">-danger">Failed to create zipFailed to create zip archive.</ archive.</div>';
       div>';
        }
    } }
    } else else {
        $ {
        $message = '<divmessage = '<div class=" class="alert alertalert alert-danger">-danger">ZipArchiveZipArchive not available not available on this server.</div on this server.</div>';
    }
}

//>';
    }
}

// Delete file Delete file backup backup
if (
if (isset($_isset($_GET['GET['delete_filedelete_file']))'])) {
    $ {
    $file = basenamefile = basename($_GET['delete_file']);
    $fullPath = $backup($_GET['delete_file']);
    $fullPath = $backupDir . $file;
    if (file_existsDir . $file;
    if (file_exists($full($fullPath)) {
       Path)) {
        unlink unlink($full($fullPathPath);
        header);
        header("Location("Location: file-backup: file-backup.php?.php?msg=msg=deleted");
       deleted");
        exit exit;
   ;
    }
}

// }
}

// List file List file backups
$back backups
$backups = [];
ifups = [];
if (is_dir($ (is_dir($backupDir)) {
    $files = scandbackupDir)) {
    $files = scandir($backupDir);
    foreachir($backupDir);
    foreach ($files ($files as $ as $file) {
       file) {
        if ($file != if ($file != '.' && $file '.' && $file != '..' != '..' && path && pathinfo($info($file,file, PATHINFO_EXTENSION) PATHINFO_EXTENSION) == 'zip') {
            $backups[] = [
                'name' => $file,
                'size' => files == 'zip') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($ize($backupDir . $file),
                'date' => filemtime($backupDir . $file),
                'date' => filemtime($backupbackupDir . $fileDir . $file)
           )
            ];
        ];
        }
    }
    }
    }
    usort usort($backups, function($($backups, function($a,a, $b $b) { return $b['date']) { return $b['date'] - $ - $a['a['date'];date']; });
 });
}
?>
<!}
?>
<!DOCTYPE htmlDOCTYPE html>
<html>
<html lang=" lang="enen">
<head>
   ">
<head>
    <meta charset="UTF-8 <meta charset="UTF-8">
    <title">
   >File <title>File Backup | Backup | CFCI CFCI Admin</title Admin</title>
   >
    <link rel <link rel="stylesheet="stylesheet" href" href="https="https://cdn://cdnjs.cloudjs.cloudflare.comflare.com/ajax/libs/ajax/libs/font/font-awesome-awesome/6/6.5.5.1.1/css/all/css/all.min.css.min.css">
   ">
    <link href="https:// <link href="https://cdn.jscdn.jsdelivdelivr.net/npm/bootstrap@r.net/npm/bootstrap@5.5.3.2/dist3.2/dist/css/css/bootstrap.min.css" rel/bootstrap.min.css" rel="stylesheet">
   ="stylesheet">
    <link <link href=" href="https://https://fonts.googleapisfonts.googleapis.com/css.com/css2?2?family=family=Inter:wInter:wght@ght@300;300;400;400;500;500;600;600;700&700&display=display=swap"swap" rel=" rel="stylesheetstylesheet">
   ">
    <style <style>
        body>
        body { font { font-family:-family: 'Inter', sans 'Inter-serif;', sans background: #f-serif; background: #f4f4f6f6f9;9; }
        }
        .admin .admin-layout { display-layout: flex { display: flex; min-height: 100; min-height: 100vh;vh; }
        }
        .admin .admin-main {-main { flex: flex: 1; margin-left: 1; margin-left: 260 260px; padding:px; 1 padding: 1.5rem 2rem.5rem 2rem; }
        .card {; }
        .card { background: background: #fff #fff; border; border-radius:-radius: 14 14px; box-shadow: px; box-shadow: 0 0 4px4px 12 12px rgbapx rgba(0,0(0,0,0,0.04,0,0.04); border: none); border: none;; }
        . }
        .badgebadge-size { background:-size { background: #e #e2e8f0;2e8f0; color: color: #1 #1a527a5276;6; padding: 0.2 padding: 0.2em em 0.0.7em; border7em; border-radius: 30-radius: 30px;px; font-size font-size: : 0.0.8rem;8rem; }
    </ }
    </stylestyle>
</head>
</head>
<body>
<body>
<div>
<div class=" class="admin-ladmin-layoutayout">
    <?">
    <?php includephp include __DIR __DIR__ .__ . '/../ '/../includes/admin_sidebarincludes/admin_sidebar.php';.php'; ?>
    ?>
    <main <main class=" class="admin-mainadmin-main">
       ">
        <?php include __DIR__ . '/../includes/admin_top <?php include __DIR__ . '/../includes/admin_topbar.phpbar.php';'; ?>
        ?>
        <h4 <h4 class=" class="fw-boldfw-bold mb-4">File Backup</h4 mb-4">File Backup</h4>
        <?>
        <?= $message= $message ?>

        ?>

        <div class <div class="card="card p- p-4 mb4 mb-4-4">
           ">
            <h <h5 class5 class="fw="fw-semibold mb-semibold mb-3-3">Create">Create Website Website File Backup File Backup</h</h55>
           >
            <p class <p class="text="text-muted">Downloads-muted">Downloads the the entire site entire site (excluding backups (excluding backups and logs and logs) as) as a ZIP a ZIP file.</ file.</pp>
           >
            <form method="post <form method="post">
               ">
                <button type=" <button type="submit" name="create_filesubmit" name="create_file_backup_backup" class" class="btn btn-primary="btn btn-primary"><i"><i class=" class="fas fa-file-fas fa-file-archive mearchive me-1-1"></i"></i> Create> Create File Backup File Backup</button</button>
            </form>
       >
            </form>
        </div>

        </div>

        <div <div class=" class="card pcard p-3-3">
           ">
            <h5 class="fw-semib <h5 class="fw-semibold mbold mb-3-3">Existing">Existing File Back File Backups (ups (<?= count($backups) ?>)</h5>
            <?php if (empty($backups)): ?>
                <p class="text-muted"><?= count($backups) ?>)</h5>
            <?php if (empty($backups)): ?>
                <p class="text-muted">No file backups yetNo file backups yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class=" class="table aligntable align-middle-middle">
                       ">
                        <thead <thead><tr><tr><th>Filename</><th>Filename</thth><th>><th>Size</Size</thth><th>><th>Created</th><th classCreated</th><th class="text-end">="text-end">Actions</Actions</th></th></tr></tr></theadthead>
                       >
                        <tbody <tbody>
                        <?>
                        <?php foreach ($backups asphp foreach ($backups as $b): ?>
                            $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b <tr>
                                <td><?= htmlspecialchars($b['name']) ?></['name']) ?></tdtd>
                               >
                                <td <td><span class><span class="bad="badge-sizege-size"><?"><?= round= round($b($b['size['size']/']/10241024/102/1024, 24, 2) ?> MB</) ?> MB</span></tdspan></td>
                               >
                                <td><? <td><?= date('M= date('M d, d, Y H Y H:i',:i', $b['date $b['date']) ?></']) ?></tdtd>
                                <td class>
                                <td class="text-end="text-end">
                                   ">
                                    <a href <a href="<?=="<?= ROOT_PATH . 'backup/files/' . ROOT_PATH . 'backup/files/' . $b['name $b['name'] ?'] ?>" download class="btn btn>" download class="btn btn-outline-primary btn-sm"><i class-outline-primary btn-sm"><i class="fas="fas fa-d fa-download"></ownload"></i></i></aa>
                                    <a href="?delete_file=<?>
                                    <a href="?delete_file=<?= url= urlencode($encode($b['b['name'])name']) ?>" ?>" class=" class="btn btnbtn btn-outline-outline-danger btn-danger btn-sm"-sm" onclick=" onclick="return confirmreturn confirm('Delete('Delete file backup file backup?')?')"><i"><i class=" class="fas fafas fa-trash-trash"></i"></i></a></a>
                               >
                                </td </td>
                            </tr>
                       >
                            </tr>
                        <?php end <?php endforeachforeach;; ?>
 ?>
                        </tbody>
                    </table>
                </div>
            <?php endif;                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </ ?>
        </divdiv>
    </>
    </mainmain>
</div>
</div>
<script src=">
<script src="https://https://cdn.jscdn.jsdelivdelivr.netr.net/npm/npm/bootstrap@5./bootstrap@5.3.2/dist3.2/dist/js/bootstrap.bundle/js/bootstrap.bundle.min.js"></script.min.js"></script>
</>
</body>
</htmlbody>
</html>
>