<?php
// admin/backup/database-backup.php
require_once __DIR__ . '/../includes/admin_functions.php';

$backupDir = ROOT_PATH . 'backup/db/';
if (!is_dir($backupDir)) { mkdir($backupDir, 0755, true); }

$message = '';

// Create database backup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupDir . $filename;

    try {
        // Get all table names
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- CFCI Church Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Drop table
            $table) {
            // Drop table
            $sql .= "DROPsql .= "DROP TABLE IF TABLE IF EXISTS ` EXISTS `$table`;\$table`;\nn";
            //";
            // Show Show create table create table
           
            $create $createStmtStmt = $ = $conn->conn->query("query("SHOWSHOW CREATE TABLE `$ CREATE TABLE `$table`table`")->")->fetch(fetch(PDO::FETCH_ASSOC);
            $sql .= $createPDO::FETCH_ASSOC);
            $sql .= $createStmt['Create Table'] . ";\n\nStmt['Create Table'] . ";\n";
            //\n";
            // Data Data
            $
            $rows =rows = $conn $conn->query->query("SELECT("SELECT * FROM * FROM `$table` `$table`")->")->fetchAllfetchAll(PD(PDO::O::FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = array_keys($rows[0FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $colList = '`' . implode('`, `', $columns)]);
                $colList = '`' . implode('`, `', $columns) . '`';
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = . '`';
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = "NULL "NULL";
                        } else {
                            $values[] = $conn->quote($val);
                        }
                    }
                    $sql .= "INSERT INTO `$table` ($colList)";
                        } else {
                            $values[] = $conn->quote($val);
                        }
                    }
                    $sql .= "INSERT INTO `$table` ($colList) VALUES (" VALUES (" . impl . implode(',ode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1=1;\n";
        file_put_contents($filepath, $sql;\n";
        file_put_contents($filepath, $sql);
       );
        $message = '<div class $message = '<div class="alert="alert alert-success">Database alert-success">Database backup created backup created:: ' ' . $ . $filename .filename . '</div '</div>';
   >';
    } catch } catch (Exception (Exception $e $e)) {
        $ {
        $message = '<divmessage = '<div class="alert alert class="alert alert-danger">-danger">BackupBackup failed: failed: ' . ' . $e $e->get->getMessage()Message() . '</ . '</divdiv>';
   >';
    }
}

// }
}

// Delete backup Delete backup
if
if (isset (isset($_GET['delete_db($_GET['delete_db'])) {
    $file = basename($_GET'])) {
    $file = basename($_GET['delete['delete_db_db']);
    $fullPath']);
    $fullPath = $ = $backupbackupDir .Dir . $file $file;
   ;
    if ( if (file_existsfile_exists($full($fullPath))Path)) {
        un {
        unlink($fullPathlink($fullPath);
        header("Location:);
        header("Location: database-back database-backup.phpup.php?msg=deleted");
        exit;
    }
?msg=deleted");
        exit;
    }
}

// List}

// List backups backups
$
$backupsbackups = = [];
if ( [];
if (is_dir($backis_dir($backupDirupDir)))) {
    $ {
    $files =files = scandir scandir($back($backupDirupDir);
   );
    foreach ($ foreach ($files asfiles as $file $file)) {
        if {
        if ($file ($file != '.' != '.' && $ && $file !=file != '.. '..' && pathinfo($file, PATH' && pathinfo($file, PATHINFO_INFO_EXTENSIONEXTENSION) == 'sql')) == 'sql') {
            $backups[] = [
                'name' => $file,
                {
            $backups[] = [
                'name' => $file,
                'size' => 'size' => filesize($backupDir . $file),
                'date' filesize($backupDir . $file),
                'date' => file => filemtimemtime($back($backupDirupDir . $ . $filefile)
           )
            ];
        ];
        }
    }
    }
    usort($ }
    usort($backupsbackups, function($a, function($a, $, $b)b) { return { return $b $b['date['date'] -'] - $a['date $a'];['date']; });
}
 });
}
?>
<!DOCTYPE?>
<!DOCTYPE html html>
<html lang>
<html lang="en="en">
<head">
<head>
    <meta>
    <meta charset="UTF- charset="UTF-8">
   8">
    <title>Database Backup | CFCI Admin</title <title>Database Backup | CFCI Admin</title>
    <link>
    <link rel="stylesheet" href=" rel="stylesheet" href="https://https://cdnjs.cloudflare.com/ajaxcdnjs.cloudflare.com/ajax/libs/font-/libs/font-awesome/6.5.awesome/6.5.1/css1/css/all.min.css">
    <link href="https://cdn/all.min.css">
    <link href="https://cdn.jsdel.jsdelivr.net/nivr.net/npm/bootstrappm/bootstrap@5@5.3.3.2.2/dist/css/dist/css/bootstrap.min/bootstrap.min.css".css" rel=" rel="stylesheetstylesheet">
   ">
    <link href <link href="https="https://fonts.googleapis.com/css2?family=Inter:wght@300;400://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background; background: #: #f4f6f9; }
        .admin-layout { display: flex; min-heightf4f6f9; }
        .admin-layout { display: flex; min-height: 100vh: 100vh;; }
        . }
        .admin-mainadmin-main { flex { flex: : 1;1; margin-left margin-left: : 260px260px; padding; padding: : 1.5rem1.5rem 2rem; }
        2rem; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0 rgba(0,,0,0,0.0,0.04);04); border: none; border: none; }
        }
        .table .table th { th { font-weight: 600; color: font-weight: 600; color: #64748b #64748b; }
        .; }
        .badgebadge-size-size { background: #e2 { background: #e2e8f0e8f0; color; color: #: #1a1a52765276; padding; padding: : 0.0.2em2em 0 0.7.7em;em; border-radius border-radius: : 30px30px; font; font-size:-size: 0.8 0rem;.8rem; }
    }
    </style </style>
</>
</headhead>
<body>
<body>
<div class>
<div class="admin="admin-layout-layout">
   ">
    <?php <?php include __ include __DIR__DIR__ . '/ . '/../includes../includes/admin_/admin_sidebar.phpsidebar.php';'; ?>
    <main class="admin-main ?>
    <main class="admin-main">
        <?php include __DIR">
        <?php include __DIR__ . '/../__ . '/../includes/adminincludes/admin_topbar_topbar.php';.php'; ?>
        ?>
        <h <h4 class="fw4 class="fw-bold mb-4">Database Backup</h4>
        <?= $message ?>

        <div class="card p-4 mb--bold mb-4">Database Backup</h4>
        <?= $message ?>

        <div class="card p-4 mb-44">
            <h5">
            <h5 class="fw-sem class="fw-semiboldibold mb- mb-3">Create New Backup</h5>
            <form method="post">
               3">Create New Backup</h5>
            <form method="post">
                <button type <button type="submit="submit" name" name="create="create_backup" class_backup" class="btn btn-primary="btn btn-primary"><i class=""><i class="fas fafas fa-download me-1"></i> Backup Now</button>
            </form-download me-1"></i> Backup Now</button>
            </form>
       >
        </div>

        </div>

        <div <div class=" class="card p-3card p-3">
           ">
            <h <h5 class5 class="fw-semib="fw-semibold mbold mb-3">Existing-3">Existing Backups Backups (<? (<?= count($back= count($backups)ups) ?>) ?>)</h</h55>
            <?>
            <?php ifphp if (empty (empty($back($backups))ups)): ?>
                <p class: ?>
                <p class="text="text-muted">No backups yet.</p-muted">No backups yet.</p>
           >
            <?php <?php else: ?>
                else: ?>
                <div <div class=" class="table-responsivetable-responsive">
                    <table class="table align-middle">
                       ">
                    <table class="table align-middle">
                        <thead <thead><tr><tr><th>><th>Filename</th><th>Size</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($backFilename</th><th>Size</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($backups as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($bups as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['name']) ?></td>
                                <td><span class="badge-size"><?= round($b['size']/1024, 1) ?> KB</span></td>
                                <td><?= date('M d, Y H:i', $b['date']) ?></td>
                                <td class="['name']) ?></td>
                                <td><span class="badge-size"><?= round($b['size']/1024, 1) ?> KB</span></td>
                                <td><?= date('M d, Y H:i', $b['date']) ?></td>
                                <td class="text-endtext-end">
                                    <a href="<?= ROOT_PATH . 'backup/db/' . $b['name'] ?>" download class="">
                                    <a href="<?= ROOT_PATH . 'backup/db/' . $b['name'] ?>" download class="btn btn-outline-primary btnbtn btn-outline-primary btn-sm"><i class-sm"><i class="fas fa-d="fas fa-download"></ownload"></i></i></aa>
                                   >
                                    <a href <a href="?="?delete_dbdelete_db=<?= url=<?= urlencode($encode($b['b['name'])name']) ?>" ?>" class=" class="btn btnbtn btn-outline-outline-danger btn-danger btn-sm"-sm" onclick=" onclick="return confirmreturn confirm('Delete('Delete this backup this backup?')?')"><i class=""><i class="fas fafas fa-trash-trash"></i></a"></i></a>
                               >
                                </td </td>
                           >
                            </tr </tr>
                       >
                        <?php endforeach; <?php endforeach; ?>
                        </tbody ?>
                        </tbody>
                    </>
                    </tabletable>
                </>
                </div>
            <?div>
            <?php endifphp endif;; ?>
        </ ?>
        </divdiv>
    </>
    </mainmain>
</div>
</div>
<script>
<script src=" src="https://https://cdn.jsdelivcdn.jsdelivr.netr.net/npm/npm/bootstrap@/bootstrap@5.5.3.3.2/dist2/dist/js/bootstrap/js/bootstrap.bundle.bundle.min.js.min.js"></script"></script>
</>
</bodybody>
</html>
</html>
>