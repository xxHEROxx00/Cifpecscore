<!DOCTYPE html>
<html lang="en">
<?php include "includes/head.php";

//admin sahaja boleh access
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//   header("Location: halaman-utama.php");
//   exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['id_penilaian'], $_POST['markah']) && is_array($_POST['markah'])) {
    $id_penilaian = $_POST['id_penilaian'];
    $markahData = $_POST['markah']; // array: id_rubrik => markah

    // Retrieve the 'tajuk' from the 'kumpulan' table based on 'id_kumpulan' from the 'penilaian' table
    $tajukQuery = "SELECT k.tajuk FROM kumpulan k 
                   INNER JOIN penilaian p ON p.id_kumpulan = k.id_kumpulan
                   WHERE p.id_penilaian = ?";
    $stmtTajuk = $conn->prepare($tajukQuery);
    $stmtTajuk->bind_param("i", $id_penilaian);
    $stmtTajuk->execute();
    $stmtTajuk->bind_result($tajuk);
    $stmtTajuk->fetch();
    $stmtTajuk->close();

    // Check if id_penilaian already exists in the markah table
    $checkQuery = "SELECT COUNT(*) FROM markah WHERE id_penilaian = ?";
    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->bind_param("i", $id_penilaian);
    $stmtCheck->execute();
    $stmtCheck->bind_result($count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($count > 0) {
      $_SESSION['error'] = "Markah untuk penilaian '$tajuk' sudah diberi.";
    } else {
      // Insert new markah if not already exists
      $stmt = $conn->prepare("INSERT INTO markah (id_rubrik, id_penilaian, markah) VALUES (?, ?, ?)");

      if ($stmt) {
        foreach ($markahData as $id_rubrik => $skala) {
          // Get sequence for the current rubrik
          $seqQuery = "SELECT sequence FROM rubrik WHERE id_rubrik = ?";
          $seqStmt = $conn->prepare($seqQuery);
          $seqStmt->bind_param("i", $id_rubrik);
          $seqStmt->execute();
          $seqStmt->bind_result($sequence);
          $seqStmt->fetch();
          $seqStmt->close();

          // Calculate markah based on sequence
          $markah = 0;
          switch ($sequence) {
            case 1:
              $markah = ($skala / 10) * 10;
              break;
            case 2:
              $markah = ($skala / 15) * 25;
              break;
            case 3:
              $markah = ($skala / 15) * 20;
              break;
            case 4:
              $markah = ($skala / 10) * 20;
              break;
            case 5:
              $markah = ($skala / 5) * 5;
              break;
            case 6:
              $markah = ($skala / 15) * 20;
              break;
          }

          // Round the result and insert into the database
          $markah = round($markah, 2);
          $stmt->bind_param("iid", $id_rubrik, $id_penilaian, $markah);
          $stmt->execute();
        }

        $stmt->close();
        $_SESSION['success'] = "Markah berjaya dihantar untuk '$tajuk'";
      } else {
        $_SESSION['error'] = "Ralat SQL: " . $conn->error;
      }
    }
  } else {
    $_SESSION['error'] = "Sila lengkapkan semua maklumat sebelum hantar.";
  }

  header("Location: nilai-markah.php");
  exit;
}




$id_penilai = $_SESSION['id'];
$query = "
  SELECT p.id_kumpulan, kul.tajuk, p.id_penilaian
  FROM penilaian p
  JOIN kumpulan kul ON p.id_kumpulan = kul.id_kumpulan
  WHERE p.id_penilai = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_penilai);
$stmt->execute();
$result = $stmt->get_result();
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
              <h2 class="pageheader-title">Nilai Markah</h2>

              <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="halaman-utama.php" class="breadcrumb-link">Halaman Utama</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Nilai Markah</li>
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
              <h5 class="card-header">Penilaian Markah oleh Panel Hakim</h5>

              <div class="card-body">
                <form action="" method="post" id="markahForm" onsubmit="return confirm('Adakah anda setuju dan berpuas hati dengan markah yang diberikan?')">
                  <label for="id_penilaian">Booth / Kumpulan yang perlu dinilai:</label>
                  <select class="form-control" name="id_penilaian" required>
                    <option value="" disabled selected>--Sila Pilih--</option>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <option value="<?= $row['id_penilaian'] ?>">
                        <?= htmlspecialchars($row['tajuk']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                  <hr>

                  <div class="table-responsive">
                    <?php
                    $query = "SELECT * FROM rubrik ORDER BY kategori, id_rubrik";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $dataByKategori = [];
                    while ($row = $result->fetch_assoc()) {
                      $dataByKategori[$row['kategori']][] = $row;
                    }

                    foreach ($dataByKategori as $kategori => $soalanList):
                    ?>
                      <h5><strong><?= htmlspecialchars($kategori) ?></strong></h5>
                      <table class="table table-bordered table-striped mb-4" data-kategori="<?= htmlspecialchars($kategori) ?>">
                        <thead class="thead-dark">
                          <tr>
                            <th style="width: 50%;">Soalan</th>
                            <th class="text-center">1</th>
                            <th class="text-center">2</th>
                            <th class="text-center">3</th>
                            <th class="text-center">4</th>
                            <th class="text-center">5</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($soalanList as $row): ?>
                            <tr data-sequence="<?= $row['sequence'] ?>" data-kategori="<?= htmlspecialchars($kategori) ?>">
                              <td><?= htmlspecialchars($row['soalan']) ?></td>
                              <?php for ($i = 1; $i <= 5; $i++): ?>
                                <td class="text-center">
                                  <label class="custom-control custom-radio custom-control-inline">
                                    <input type="radio"
                                      name="markah[<?= $row['id_rubrik'] ?>]"
                                      value="<?= $i ?>"
                                      class="custom-control-input"
                                      onchange="calculateTotal()"
                                      required>
                                    <span class="custom-control-label"></span>
                                  </label>
                                </td>
                              <?php endfor; ?>
                            </tr>

                          <?php endforeach; ?>

                          <tr>
                            <th class="text-right">Jumlah Markah (<?= htmlspecialchars($kategori) ?>):</th>
                            <th class="text-center" colspan="5">
                              <input type="text" id="totalMarkah_<?= htmlspecialchars($kategori) ?>" class="form-control" disabled>
                            </th>
                          </tr>
                        </tbody>

                      </table>
                    <?php endforeach; ?>
                  </div>

                  <div class="form-group">
                    <label>Jumlah Markah:</label>
                    <input type="text" id="totalMarkah" class="form-control" disabled>
                  </div>

                  <button type="submit" class="btn btn-primary">Hantar Markah</button>
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>
      <?php
      // include "includes/footer.php" 
      ?>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <script src="assets/vendor/jquery/jquery-3.3.1.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
  <script src="assets/vendor/slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/libs/js/main-js.js"></script>

  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="assets/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/vendor/datatables/js/data-table.js"></script>

  <script>
    function calculateTotal() {
      const tables = document.querySelectorAll("table[data-kategori]");
      let grandTotal = 0;

      tables.forEach(table => {
        let total = 0;
        const kategori = table.getAttribute("data-kategori");
        const rows = table.querySelectorAll("tbody tr[data-sequence]");

        rows.forEach(row => {
          const sequence = parseInt(row.getAttribute("data-sequence"));
          const selected = row.querySelector("input[type='radio']:checked");

          if (selected) {
            const skala = parseInt(selected.value);

            // Apply formula based on sequence
            switch (sequence) {
              case 1:
                total += (skala / 10) * 10;
                break;
              case 2:
                total += (skala / 15) * 25;
                break;
              case 3:
                total += (skala / 15) * 20;
                break;
              case 4:
                total += (skala / 10) * 20;
                break;
              case 5:
                total += (skala / 5) * 5;
                break;
              case 6:
                total += (skala / 15) * 20;
                break;
            }
          }
        });

        // Update category total
        const totalField = document.getElementById(`totalMarkah_${kategori}`);
        if (totalField) {
          totalField.value = total.toFixed(2);
        }

        // Add to grand total
        grandTotal += total;
      });

      // Update grand total
      const grandField = document.getElementById("totalMarkah");
      if (grandField) {
        grandField.value = grandTotal.toFixed(2);
      }
    }
  </script>

</body>

</html>