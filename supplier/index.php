<?php

require_once '../init.php';

// Jika belum login, arahkan ke halaman login
if (!session_is_login()) {
    header("Location: $BASE_URL/login.php");
}

$title = "Supplier";

$supplier = db_get('supplier');
$message = session_flash('message');
$error = session_flash('error');
?>

<!-- mulai halaman -->
<?php include '../layout/header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php include '../layout/menu.php'; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h1><?=$title?></h1>
            <?php if(session_is_admin()) { ?>
            <p><a href="tambah.php" class="btn btn-primary">Tambah</a></p>
            <?php } ?>
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
            <?php elseif (isset($message)) : ?>
                <div class="alert alert-info" role="alert">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            <table class="table" id="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Supplier</th>
                        <th>Nama Supplier</th>
                        <th>Alamat Supplier</th>
                        <th>Telp Supplier</th>
                        <?php if(session_is_admin()) { ?>
                        <th>Aksi</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($supplier as $key => $value) : ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $value['kode_sup'] ?></td>
                            <td><?= $value['nama_sup'] ?></td>
                            <td><?= $value['alamat_sup'] ?></td>
                            <td><?= $value['telp_sup'] ?></td>
                            <?php if(session_is_admin()) { ?>
                            <td>
                                <a href="edit.php?kode_sup=<?= $value['kode_sup'] ?>" class="btn btn-warning">Edit</a>
                                <button onclick="hapus('<?=$value['kode_sup']?>')" class="btn btn-danger">Hapus</button>
                            </td>
                            <!-- modal -->
                            <div class="modal fade" id="modalHapus<?=$value['kode_sup']?>" tabindex="-1" role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalHapusLabel">Hapus Data</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            Apakah anda yakin ingin menghapus data ini?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                                            <a href="hapus.php?kode_sup=<?=$value['kode_sup']?>" class="btn btn-primary">Ya</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function hapus(kode_sup) {
        $(`#modalHapus${kode_sup}`).modal('show');
    }
</script>
<?php include '../layout/footer.php'; ?>