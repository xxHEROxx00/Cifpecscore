<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";

//admin sahaja boleh access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: halaman-utama.php");
  exit;
}


if (isset($_POST['submit_assign'])) {
  $id_penilai = $_POST['id_penilai'];
  $id_kumpulan = $_POST['id_ketua_kumpulan']; // this is actually kumpulan

  // Check if this assignment already exists
  $check = $conn->prepare("SELECT * FROM penilaian WHERE id_penilai = ? AND id_kumpulan = ?");
  $check->bind_param("ii", $id_penilai, $id_kumpulan);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    $_SESSION['error'] = "Penilai sudah ditugaskan kepada kumpulan ini.";
  } else {
    // Insert into penilaian
    $stmt = $conn->prepare("INSERT INTO penilaian (id_penilai, id_kumpulan) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_penilai, $id_kumpulan);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Penilai berjaya ditugaskan!";
    } else {
      $_SESSION['error'] = "Ralat ketika menyimpan: " . $stmt->error;
    }
    $stmt->close();
  }

  $check->close();
  header("Location: senarai-panel.php"); // change this to your actual page
  exit;
}

if (isset($_POST['remove_penilaian'])) {
  $id_penilai = $_POST['id_penilai'];
  $id_kumpulan = $_POST['id_kumpulan'];
  $conn->query("DELETE FROM penilaian WHERE id_penilai = '$id_penilai' AND id_kumpulan = '$id_kumpulan'");
}


$query = "
  SELECT 
    u.*, 
    GROUP_CONCAT(CONCAT(k.id_kumpulan, '::', k.tajuk) SEPARATOR ',') AS kumpulan_data
  FROM users u
  LEFT JOIN penilaian p ON u.id = p.id_penilai
  LEFT JOIN kumpulan k ON p.id_kumpulan = k.id_kumpulan
  WHERE u.role = 'hakim'
  GROUP BY u.id
";

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
              <h2 class="pageheader-title">Senarai Panel / Hakim</h2>

              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Senarai Panel / Hakim</li>
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


            <?php if (!empty($_SESSION['error'])): ?>
              <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                  </div>
                </div>
              </div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

          </div>
        </div>
        <!-- ============================================================== -->
        <!-- end pageheader -->
        <!-- ============================================================== -->
        <div class="row">


          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
              <h5 class="card-header">Senarai Panel / Hakim Berdaftar </h5>

              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered first">
                    <thead>
                      <tr>
                        <th>Nama Panel / Hakim</th>
                        <th>Email</th>
                        <th>Booth Perlu Dinilai</th>
                        <th>Tindakan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>

                          <td><?= $row['name'] ?></td>
                          <td><?= $row['email'] ?></td>
                          <td>
                            <?php if ($row['kumpulan_data']): ?>
                              <?php
                              $kumpulanList = explode(',', $row['kumpulan_data']);
                              foreach ($kumpulanList as $kumpulanItem):
                                list($id_kumpulan, $tajuk) = explode('::', $kumpulanItem);
                              ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                  <span><?= htmlspecialchars($tajuk) ?></span>
                                  <form method="POST" action="" onsubmit="return confirm('Padam data ini?')">
                                    <input type="hidden" name="id_penilai" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="id_kumpulan" value="<?= $id_kumpulan ?>">
                                    <button type="submit" name="remove_penilaian" class="btn btn-sm btn-danger ml-2">Buang</button>
                                  </form>
                                </div>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <span class="text-muted">Tiada</span>
                            <?php endif; ?>
                          </td>


                          <td class="text-end">
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#assignPenilaian<?= $row['id'] ?>">
                              Assign Penilai
                            </a>
                          </td>
                        </tr>


                        <!-- Modal -->
                        <div class="modal fade" id="assignPenilaian<?= $row['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="assignPenilaianModalLabel<?= $row['id'] ?>" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <form action="" method="POST">
                                <input type="hidden" name="id_kumpulan" value="<?= $row['id'] ?>">

                                <div class="modal-header">
                                  <h5 class="modal-title" id="assignPenilaianModalLabel<?= $row['id'] ?>">Assign Penilai Booth / Kumpulan</h5>
                                  <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </a>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="id_penilai" value="<?= $row['id'] ?>">


                                  <div class="form-group">
                                    <label for="id_ketua_kumpulan">Booth / Kumpulan</label>
                                    <select class="form-control" name="id_ketua_kumpulan" required>
                                      <option value="" selected disabled>-- Sila Pilih Booth --</option>
                                      <?php
                                      $pelajarList = $conn->query("SELECT * FROM kumpulan");
                                      while ($pel = $pelajarList->fetch_assoc()):
                                        // $selected = ($pel['id_kumpulan'] == $row['tajuk']) ? 'selected' : '';
                                      ?>
                                        <option value="<?= $pel['id_kumpulan'] ?>"><?= $pel['tajuk'] ?></option>
                                        <!-- <option value="<?= $pel['id_kumpulan'] ?>" <?= $selected ?>><?= $pel['tajuk'] ?></option> -->
                                      <?php endwhile; ?>
                                    </select>
                                  </div>


                                </div>
                                <div class="modal-footer">
                                  <a href="#" class="btn btn-secondary" data-dismiss="modal">Tutup</a>
                                  <button type="submit" name="submit_assign" class="btn btn-success">Simpan</button>
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