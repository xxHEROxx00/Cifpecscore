<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";

//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  // UPDATE first
  if (isset($_POST['submit_edit'])) {
    $id = $_POST['id_pelajar'];
    $nama = $_POST['nama_pelajar'];
    $bidang = $_POST['bidang'];
    $kursus = $_POST['kursus'];
    $ahli_kumpulan = $_POST['ahli_kumpulan'];

    $stmt = $conn->prepare("UPDATE pelajar SET nama_pelajar=?, bidang=?, kursus=?, ahli_kumpulan=? WHERE id_pelajar=?");
    $stmt->bind_param("ssssi", $nama, $bidang, $kursus, $ahli_kumpulan, $id);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Pelajar berjaya dikemaskini!";
    } else {
      $_SESSION['error'] = "Ralat kemaskini: " . $stmt->error;
    }
    $stmt->close();
    header("Location: pelajar.php");
    exit;
  }

  // INSERT fallback
  if (isset($_POST['nama_pelajar']) && isset($_POST['bidang']) && isset($_POST['kursus']) && isset($_POST['ahli_kumpulan'])) {
    $nama = $_POST['nama_pelajar'];
    $bidang = $_POST['bidang'];
    $kursus = $_POST['kursus'];
    $ahli_kumpulan = $_POST['ahli_kumpulan'];

    $sql = "INSERT INTO pelajar (nama_pelajar, bidang, kursus,ahli_kumpulan) VALUES (?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama, $bidang, $kursus, $ahli_kumpulan);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Pelajar berjaya ditambah!";
    } else {
      $_SESSION['error'] = "Ralat tambah: " . $stmt->error;
    }
    $stmt->close();
    header("Location: pelajar.php");
    exit;
  }
}


// DELETE: Delete pelajar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_delete'])) {
  $idToDelete = $_POST['delete_id_pelajar'] ?? null;

  if ($idToDelete) {
    $stmt = $conn->prepare("DELETE FROM pelajar WHERE id_pelajar = ?");
    $stmt->bind_param("i", $idToDelete);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Pelajar berjaya dihapuskan!";
    } else {
      $_SESSION['error'] = "Ralat hapus: " . $stmt->error;
    }
    $stmt->close();
    header("Location: pelajar.php");
    exit;
  }
}


$query = "SELECT * FROM pelajar";
$result = $conn->query($query);

?>

<body>
  <!-- ============================================================== -->
  <!-- main wrapper -->
  <!-- ============================================================== -->
  <div class="dashboard-main-wrapper">
    <?php
    include "includes/navbar.php";
    include "includes/leftbar.php";

    ?>

    <!-- ============================================================== -->
    <!-- wrapper  -->
    <!-- ============================================================== -->
    <div class="dashboard-wrapper">
      <div class="container-fluid dashboard-content">
        <!-- ============================================================== -->
        <!-- pageheader -->
        <!-- ============================================================== -->
        <div class="row">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
              <h2 class="pageheader-title">Pelajar</h2>

              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Pelajar</li>
                  </ol>
                </nav>
              </div>
            </div>


            <?php if (!empty($_SESSION['success'])): ?>
              <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                  </div>
                </div>
              </div>
              <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

          </div>
        </div>
        <!-- ============================================================== -->
        <!-- end pageheader -->
        <!-- ============================================================== -->
        <div class="row">


          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
              <h5 class="card-header">Senarai Pelajar <a href="#" class="btn btn-primary btn-sm btn-rounded" data-toggle="modal" data-target="#tambahPelajar">
                  + Tambah Pelajar
                </a></h5>

              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered first">
                    <thead>
                      <tr>
                        <th>Nama</th>
                        <th>Bidang</th>
                        <th>Kursus</th>
                        <th>Ahli Kumpulan</th>
                        <th>Tindakan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>

                          <td><?= $row['nama_pelajar'] ?></td>
                          <td><?= $row['bidang'] ?></td>
                          <td><?= $row['kursus'] ?></td>
                          <td><?= $row['ahli_kumpulan'] ?></td>
                          <td class="text-end">
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editPelajar<?= $row['id_pelajar'] ?>">
                              Kemaskini
                            </a>

                            <form method="POST" style="display:inline;" onsubmit="return confirm('Padam pelajar ini?')">
                              <input type="hidden" name="delete_id_pelajar" value="<?= $row['id_pelajar'] ?>">
                              <button type="submit" name="submit_delete" class="btn btn-danger btn-sm">
                                Hapus
                              </button>
                            </form>

                          </td>

                        </tr>


                        <!-- Modal Edit Pelajar -->
                        <div class="modal fade" id="editPelajar<?= $row['id_pelajar'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $row['id_pelajar'] ?>" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <form action="" method="POST">
                                <input type="hidden" name="id_pelajar" value="<?= $row['id_pelajar'] ?>">

                                <div class="modal-header">
                                  <h5 class="modal-title" id="editModalLabel<?= $row['id_pelajar'] ?>">Kemaskini Pelajar</h5>
                                  <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </a>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="id_pelajar" value="<?= $row['id_pelajar'] ?>">

                                  <div class="form-group">
                                    <label for="nama_pelajar">Nama Penuh Pelajar</label>
                                    <input name="nama_pelajar" type="text" value="<?= htmlspecialchars($row['nama_pelajar']) ?>" class="form-control" required>
                                  </div>

                                  <div class="form-group">
                                    <label for="bidang">Bidang</label>
                                    <input name="bidang" type="text" value="<?= htmlspecialchars($row['bidang']) ?>" class="form-control" required>
                                  </div>

                                  <div class="form-group">
                                    <label for="kursus">Kursus</label>
                                    <input name="kursus" type="text" value="<?= htmlspecialchars($row['kursus']) ?>" class="form-control" required>
                                  </div>

                                  <div class="form-group">
                                    <label for="ahli_kumpulan">Ahli Kumpulan</label>
                                    <textarea name="ahli_kumpulan" type="text" value="<?= $row['ahli_kumpulan'] ?>" class="form-control"><?= $row['ahli_kumpulan'] ?></textarea>
                                  </div>

                                </div>
                                <div class="modal-footer">
                                  <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
                                  <button type="submit" name="submit_edit" class="btn btn-primary">Kemaskini</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>



                      <?php endwhile; ?>


                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
      // include "includes/footer.php" 
      ?>
    </div>
    <!-- ============================================================== -->
    <!-- end main wrapper -->
    <!-- ============================================================== -->
  </div>




  <!-- Modal  Tambah-->
  <div class="modal fade" id="tambahPelajar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Pelajar</h5>
            <a href="#" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </a>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="nama_pelajar">Nama Penuh Pelajar</label>
              <input name="nama_pelajar" type="text" placeholder="Masukkan Nama Penuh Pelajar" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="bidang">Bidang</label>
              <input name="bidang" type="text" placeholder="Masukkan Bidang" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="kursus">Kursus</label>
              <input name="kursus" type="text" placeholder="Kursus" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="ahli_kumpulan">Ahli Kumpulan</label>
              <textarea name="ahli_kumpulan" type="text" class="form-control"></textarea>
            </div>

          </div>
          <div class="modal-footer">
            <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
            <button type="submit" class="btn btn-success">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>





  <!-- ============================================================== -->
  <!-- end main wrapper -->
  <!-- ============================================================== -->
  <!-- Optional JavaScript -->
  <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
  <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/libs/js/main-js.js"></script>

  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="assets/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/vendor/datatables/js/data-table.js"></script>


  <script>
  </script>
</body>

</html>