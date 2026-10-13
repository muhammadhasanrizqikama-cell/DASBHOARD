<?php
require_once 'koneksi.php';

// Proses data formulir
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama = trim($_POST['nama'] ?? '');
        $harga = $_POST['harga'] ?? '';
        $stok = $_POST['stok'] ?? '';

        if (
            $nama !== '' &&
            is_numeric($harga) &&
            $harga >= 0 &&
            filter_var($stok, FILTER_VALIDATE_INT) !== false &&
            $stok >= 0
        ) {
            $stmt = $koneksi->prepare(
                "INSERT INTO produk (nama, harga, stok)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([$nama, $harga, $stok]);
        }
    }

    elseif ($aksi === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $harga = $_POST['harga'] ?? '';
        $stok = $_POST['stok'] ?? '';

        if (
            $id > 0 &&
            $nama !== '' &&
            is_numeric($harga) &&
            $harga >= 0 &&
            filter_var($stok, FILTER_VALIDATE_INT) !== false &&
            $stok >= 0
        ) {
            $stmt = $koneksi->prepare(
                "UPDATE produk
                 SET nama = ?, harga = ?, stok = ?
                 WHERE id = ?"
            );

            $stmt->execute([$nama, $harga, $stok, $id]);
        }
    }

    elseif ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $koneksi->prepare(
                "DELETE FROM produk WHERE id = ?"
            );

            $stmt->execute([$id]);
        }
    }

    header("Location: index.php");
    exit;
}

// Mengambil data dari database
$stmt = $koneksi->query(
    "SELECT * FROM produk ORDER BY id ASC"
);

$produkList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Menghitung ringkasan produk
$totalProduk = count($produkList);
$totalStok = array_sum(array_column($produkList, 'stok'));

$totalNilai = 0;
foreach ($produkList as $produk) {
    $totalNilai += $produk['harga'] * $produk['stok'];
}

function e($nilai) {
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
}

function rupiah($nilai) {
    return 'Rp ' . number_format(
        (float) $nilai,
        0,
        ',',
        '.'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SATAMART - Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            font-size: .875rem;
            background-color: #f8f9fa;
            color: #212529;
        }

        .topbar {
            height: 48px;
            z-index: 1030;
            padding: 0;
        }

        .navbar-brand {
            width: 240px;
            min-width: 240px;
            height: 48px;
            padding: 13px 16px;
            margin: 0;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
        }

        .sidebar {
            position: fixed;
            top: 48px;
            bottom: 0;
            left: 0;
            z-index: 100;
            width: 240px;
            padding: 0;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }

        .sidebar-inner {
            height: calc(100vh - 48px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar .nav-link {
            color: #0d6efd;
            font-weight: 500;
            padding: 10px 16px;
            margin-bottom: 2px;
        }

        .sidebar .nav-link:hover {
            color: #0a58ca;
            background-color: rgba(13, 110, 253, .05);
        }

        .sidebar .nav-link.text-danger:hover {
            background-color: rgba(220, 53, 69, .05);
        }

        main {
            margin-left: 240px;
            padding: 20px 24px;
        }

        .table-responsive {
            background: #fff;
            border-radius: 5px;
        }

        .table th {
            font-weight: 700;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-add {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .btn-add:hover {
            background-color: #5c636a;
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .navbar-brand {
                width: auto;
                min-width: 0;
            }

            .sidebar {
                position: fixed;
                width: 240px;
                box-shadow: 2px 0 8px rgba(0, 0, 0, .08);
            }

            main {
                margin-left: 0;
                padding: 20px 14px;
            }
        }
    </style>
</head>

<body>

<!-- Header -->
<header class="navbar navbar-dark sticky-top bg-dark topbar shadow">
    <a class="navbar-brand text-white" href="index.php">
        SATAMART
    </a>

    <button class="navbar-toggler d-md-none me-3"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#sidebarMenu"
            aria-controls="sidebarMenu"
            aria-expanded="false"
            aria-label="Buka menu">

        <span class="navbar-toggler-icon"></span>
    </button>
</header>

<!-- Sidebar -->
<nav id="sidebarMenu" class="sidebar collapse d-md-block">

    <div class="sidebar-inner">

        <ul class="nav flex-column pt-3">

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Orders
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link fw-bold" href="#dataProduk">
                    <i class="bi bi-cart me-2"></i>
                    Products
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-people me-2"></i>
                    Customers
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-graph-up me-2"></i>
                    Reports
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-puzzle me-2"></i>
                    Integrations
                </a>
            </li>

        </ul>

        <ul class="nav flex-column mb-2 border-top pt-3">
            <li class="nav-item">
                <a class="nav-link text-danger" href="#">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Sign out
                </a>
            </li>
        </ul>

    </div>
</nav>

<!-- Konten utama -->
<main>

    <div class="d-flex justify-content-between flex-wrap
                flex-md-nowrap align-items-center
                pt-3 pb-2 mb-3 border-bottom">

        <h1 class="h2">Dashboard</h1>

    </div>

    <!-- Ringkasan -->
    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-secondary mb-2">
                        Total Produk
                    </div>

                    <h4 class="fw-bold mb-0">
                        <?= number_format($totalProduk, 0, ',', '.') ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-secondary mb-2">
                        Total Stok
                    </div>

                    <h4 class="fw-bold mb-0">
                        <?= number_format($totalStok, 0, ',', '.') ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-secondary mb-2">
                        Nilai Persediaan
                    </div>

                    <h5 class="fw-bold mb-0">
                        <?= rupiah($totalNilai) ?>
                    </h5>
                </div>
            </div>
        </div>

    </div>

    <!-- Data Produk -->
    <section id="dataProduk">

        <h2 class="h4 mb-3">Data Produk</h2>

        <button class="btn btn-add btn-sm mb-3"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#addProductModal">

            Add Product

        </button>

        <div class="table-responsive border">

            <table class="table table-striped table-hover
                          align-middle mb-0">

                <thead class="table-light border-bottom">
                    <tr>
                        <th scope="col" style="width:10%">No</th>
                        <th scope="col" style="width:30%">Nama Barang</th>
                        <th scope="col" style="width:20%">Harga</th>
                        <th scope="col" style="width:25%">Stok</th>
                        <th scope="col" style="width:15%">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($produkList)): ?>

                    <tr>
                        <td colspan="5" class="text-center py-4">
                            Belum ada data produk.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($produkList as $index => $produk): ?>

                    <tr>

                        <td>
                            <?= number_format($index + 1001, 0, ',', '.') ?>
                        </td>

                        <td>
                            <?= e($produk['nama']) ?>
                        </td>

                        <td>
                            <?= rupiah($produk['harga']) ?>
                        </td>

                        <td>
                            <?= e($produk['stok']) ?>
                        </td>

                        <td class="text-nowrap">

                            <a href="#"
                               class="text-primary text-decoration-none"
                               data-bs-toggle="modal"
                               data-bs-target="#editProductModal"
                               data-id="<?= $produk['id'] ?>"
                               data-nama="<?= e($produk['nama']) ?>"
                               data-harga="<?= e($produk['harga']) ?>"
                               data-stok="<?= e($produk['stok']) ?>">

                                Edit

                            </a>

                            <span class="text-muted"> | </span>

                            <form method="post"
                                  class="d-inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">

                                <input type="hidden"
                                       name="aksi"
                                       value="hapus">

                                <input type="hidden"
                                       name="id"
                                       value="<?= $produk['id'] ?>">

                                <button type="submit"
                                        class="btn btn-link text-danger
                                               text-decoration-none p-0 align-baseline">

                                    Hapus

                                </button>

                            </form>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<!-- Modal Tambah Produk -->
<div class="modal fade"
     id="addProductModal"
     tabindex="-1"
     aria-labelledby="addProductModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="post">

                <div class="modal-header">

                    <h5 class="modal-title" id="addProductModalLabel">
                        Tambah Produk Baru
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup">
                    </button>

                </div>

                <div class="modal-body">

                    <input type="hidden" name="aksi" value="tambah">

                    <div class="mb-3">
                        <label for="namaBarang" class="form-label">
                            Nama Barang
                        </label>

                        <input type="text"
                               class="form-control"
                               id="namaBarang"
                               name="nama"
                               maxlength="150"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="hargaBarang" class="form-label">
                            Harga
                        </label>

                        <input type="number"
                               class="form-control"
                               id="hargaBarang"
                               name="harga"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="stokBarang" class="form-label">
                            Stok
                        </label>

                        <input type="number"
                               class="form-control"
                               id="stokBarang"
                               name="stok"
                               min="0"
                               step="1"
                               required>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Produk
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- Modal Edit Produk -->
<div class="modal fade"
     id="editProductModal"
     tabindex="-1"
     aria-labelledby="editProductModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="post">

                <div class="modal-header">

                    <h5 class="modal-title" id="editProductModalLabel">
                        Edit Produk
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup">
                    </button>

                </div>

                <div class="modal-body">

                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" id="editId">

                    <div class="mb-3">
                        <label for="editNamaBarang" class="form-label">
                            Nama Barang
                        </label>

                        <input type="text"
                               class="form-control"
                               id="editNamaBarang"
                               name="nama"
                               maxlength="150"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="editHargaBarang" class="form-label">
                            Harga
                        </label>

                        <input type="number"
                               class="form-control"
                               id="editHargaBarang"
                               name="harga"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="editStokBarang" class="form-label">
                            Stok
                        </label>

                        <input type="number"
                               class="form-control"
                               id="editStokBarang"
                               name="stok"
                               min="0"
                               step="1"
                               required>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit" class="btn btn-primary">
                        Perbarui Produk
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const editModal = document.getElementById('editProductModal');

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        document.getElementById('editId').value =
            button.getAttribute('data-id');

        document.getElementById('editNamaBarang').value =
            button.getAttribute('data-nama');

        document.getElementById('editHargaBarang').value =
            button.getAttribute('data-harga');

        document.getElementById('editStokBarang').value =
            button.getAttribute('data-stok');
    });
</script>

</body>
</html>
