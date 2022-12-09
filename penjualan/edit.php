<?php

require_once '../init.php';

// Jika belum login, arahkan ke halaman login
if (!session_is_login()) {
    header("Location: $BASE_URL/login.php");
}

// Jika bukan admin, arahkan ke halaman index
if (!session_is_admin()) {
    header("Location: index.php");
}

// Jika tidak ada parameter no_transaksi, arahkan ke halaman index
if (!isset($_GET['no_transaksi'])) {
    header("Location: index.php");
}

$no_transaksi = $_GET['no_transaksi'];
$penjualan = db_get_one('penjualan', "no_transaksi = '$no_transaksi'");
if (!$penjualan) {
    header("Location: index.php");
}

if (isset($_POST['tambah'])) {
    $kode_brg = $_POST['kode_brg'];
    $jumlah = $_POST['jumlah'];
    $barang = db_get_one('barang', "kode_brg='$kode_brg'");
    $total = $jumlah * $barang['harga'];
    $data = [
        'no_transaksi' => $no_transaksi,
        'kode_brg' => $kode_brg,
        'jumlah' => $jumlah,
        'total' => $total
    ];
    $result = db_insert('penjualan_barang', $data);
    $result2 = db_update('barang', [
        'stok' => $barang['stok'] - $jumlah
    ], "kode_brg='$kode_brg'");
    if ($result && $result2) {
        session_flash('message', 'Data berhasil ditambahkan');
    } else {
        session_flash('error', 'Data gagal ditambahkan');
    }
}

if (isset($_POST['edit'])) {
    $id_detail = $_POST['id_detail'];
    $old_detail = db_get_one('penjualan_barang', "id_detail=$id_detail");
    $kode_brg = $_POST['kode_brg'];
    $jumlah = $_POST['jumlah'];
    $barang = db_get_one('barang', "kode_brg='$kode_brg'");
    $total = $jumlah * $barang['harga'];
    $data = [
        'kode_brg' => $kode_brg,
        'jumlah' => $jumlah,
        'total' => $total
    ];
    $result = db_update('penjualan_barang', $data, "id_detail=$id_detail");
    $result2 = db_update('barang', [
        'stok' => $barang['stok'] - $old_detail['jumlah'] - $jumlah
    ], "kode_brg='$kode_brg'");
    if ($result && $result2) {
        session_flash('message', 'Data berhasil diubah');
    } else {
        session_flash('error', 'Data gagal diubah');
    }
}

if (isset($_POST['hapus'])) {
    $id_detail = $_POST['id_detail'];
    $old_detail = db_get_one('penjualan_barang', "id_detail=$id_detail");
    $barang = db_get_one('barang', "kode_brg='" . $old_detail['kode_brg'] . "'");
    $result = db_delete('penjualan_barang', "id_detail=$id_detail");
    $result2 = db_update('barang', ['stok' => $barang['stok'] - $old_detail['jumlah']], "kode_brg='" . $barang['kode_brg'] . "'");
    if ($result && $result2) {
        session_flash('message', 'Data berhasil dihapus');
    } else {
        session_flash('error', 'Data gagal dihapus');
    }
}

$penjualan_barang = db_get('penjualan_barang', "no_transaksi = '$no_transaksi'");

$semua_barang = db_get('barang');
$semua_supplier = db_get('supplier');

$title = "Penjualan Barang";

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
            <h1><?= $title ?></h1>
            <h4>No Transaksi: <?= $no_transaksi ?></h4>
            <h4>Tanggal: <?= $penjualan['tgl_transaksi'] ?></h4>
            <?php if (session_is_admin()) { ?>
                <p><a href="#" onclick="tambah()" class="btn btn-primary">Tambah</a></p>
                <div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTambahLabel">Tambah Data</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="kode_brg">Barang</label>
                                        <select name="kode_brg" class="form-control">
                                            <?php foreach ($semua_barang as $barang) { ?>
                                                <option value="<?= $barang['kode_brg'] ?>" ><?= $barang['nama_brg'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="jumlah">Jumlah</label>
                                        <input type="number" class="form-control" name="jumlah" min="0" onkeypress="input_number(event)">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <input type="hidden" name="no_transaksi" value="<?= $no_transaksi ?>">
                                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
            <!-- loop modalDetail rak -->
            <table class="table" id="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penjualan_barang as $key => $value) {
                        $barang = db_get_one('barang', "kode_brg='" . $value['kode_brg'] . "'");?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $value['kode_brg'] ?></td>
                            <td><?= $barang['nama_brg'] ?></td>
                            <td>Rp<?= number_format($barang['harga'], 2, ',', '.') ?></td>
                            <td><?= $value['jumlah'] ?></td>
                            <td>Rp<?= number_format($value['total'], 2, ',', '.') ?></td>
                            <td>
                                <button onclick="edit('<?= $value['id_detail'] ?>')" class="btn btn-warning">Edit</button>
                                <div class="modal fade" id="modalEdit<?= $value['id_detail'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="kode_brg">Barang</label>
                                                        <select name="kode_brg" class="form-control">
                                                            <?php foreach ($semua_barang as $barang) { ?>
                                                                <option value="<?= $barang['kode_brg'] ?>" <?= $value['kode_brg'] == $barang['kode_brg'] ? 'selected' : '' ?>><?= $barang['nama_brg'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="jumlah">Jumlah</label>
                                                        <input type="number" class="form-control" name="jumlah" value="<?= $value['jumlah'] ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <input type="hidden" name="id_detail" value="<?= $value['id_detail'] ?>">
                                                    <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="hapus('<?= $value['id_detail'] ?>')" class="btn btn-danger">Hapus</button>
                                <!-- modal -->
                                <div class="modal fade" id="modalHapus<?= $value['id_detail'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
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
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="id_detail" value="<?= $value['id_detail'] ?>">
                                                    <button type="submit" name="hapus" class="btn btn-primary">Ya</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        <?php } ?>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function tambah() {
        $(`#modalTambah`).modal('show');
    }
    
    function edit(id_detail) {
        $(`#modalEdit${id_detail}`).modal('show');
    }

    function hapus(id_detail) {
        $(`#modalHapus${id_detail}`).modal('show');
    }
</script>
<?php include '../layout/footer.php'; ?>