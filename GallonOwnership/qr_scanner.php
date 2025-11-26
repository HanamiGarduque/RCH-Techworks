<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RCH Water - QR Code Scanner & Upload</title>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }

    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    /* Ensure the page layout uses full viewport height */
    .page-container {
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* Main content area */
    .content-wrapper {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* Two-column layout container */
    .two-column-container {
      flex: 1;
      display: flex;
      gap: 1.5rem;
      overflow: hidden;
    }

    /* Left section with gallery */
    .left-section {
      flex: 2;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* Right section with scanner */
    .right-section {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      min-width: 380px;
    }

    /* Gallery scroll container */
    .gallery-scroll {
      overflow-y: auto;
      overflow-x: hidden;
      flex: 1;
    }
  </style>
</head>

<body class="bg-gray-50">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <script src="../Admin/includes/sidebar.js"></script>

    <!-- Main Content -->
    <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
      <div class="page-container">
        <!-- Header - Fixed -->
        <?php
        $pageTitle = "QR Code Scanner";
        $pageSubtitle = "Scan and manage QR codes for gallon tracking.";
        include '../Admin/includes/header.php';
        ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper p-4">
          <!-- Two Column Layout -->
          <div class="two-column-container">

            <!-- LEFT SECTION (Gallery) -->
            <div class="left-section bg-white p-6 rounded-2xl shadow-lg">
              <!-- Header Controls -->
              <div class="flex items-center justify-between mb-4 gap-4 flex-shrink-0">
                <div class="flex-shrink-0">
                  <a href="gallon_ownership.php"
                    class="bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Return
                  </a>
                </div>

                <div class="flex-1 ml-4 relative">
                  <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                  <input type="text" id="searchInput"
                    placeholder="Search by Code Value..."
                    class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeyup="searchTable()">
                </div>

                <button id="toggleOrderBtn"
                  class="flex items-center bg-blue-100 text-blue-600 font-medium px-4 py-2 rounded-lg hover:bg-blue-200 transition whitespace-nowrap flex-shrink-0">
                  <i class="fas fa-sort mr-2"></i> <span id="orderLabel">Recent</span>
                </button>
              </div>

              <!-- Section Title -->
              <div class="flex items-center justify-start mb-4 bg-blue-100 p-3 rounded-lg flex-shrink-0">
                <i class="fas fa-qrcode mr-3 text-blue-600 text-xl"></i>
                <h2 class="text-lg font-semibold text-blue-600 m-0">QR Code Gallery</h2>
              </div>

              <!-- QR Gallery Grid - Scrollable -->
              <div id="qrGallery" class="gallery-scroll grid grid-cols-3 gap-4 p-1">
                <?php
                $query = "SELECT 
                      go.ownership_id AS `Gallon ID`,
                      go.gallon_type AS `Gallon Type`,
                      u.name AS `Current Owner`,
                      go.code_value AS `Code Value`,
                      go.status AS `Status`,
                      go.qr_image AS `QR Image`
                      FROM gallon_ownership AS go
                      LEFT JOIN users AS u ON go.current_owner_id = u.user_id
                      WHERE go.qr_image IS NOT NULL AND go.qr_image <> ''
                      ORDER BY go.ownership_id DESC";

                $stmt = $db->prepare($query);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($rows) > 0) {
                  foreach ($rows as $row) {
                    $qrImage = htmlspecialchars($row['QR Image']);
                    $codeValue = htmlspecialchars($row['Code Value']);
                    $owner = htmlspecialchars($row['Current Owner'] ?? 'Unknown');
                    $gallonId = htmlspecialchars($row['Gallon ID'] ?? '');

                    echo '
                    <div class="relative group p-3 border rounded-xl flex flex-col items-center shadow-sm hover:shadow-md transition cursor-pointer qr-card" 
                        data-id="' . $gallonId . '" 
                        data-code="' . $codeValue . '" 
                        data-owner="' . $owner . '" 
                        data-type="' . htmlspecialchars($row['Gallon Type'] ?? 'Unknown') . '" 
                        data-status="' . htmlspecialchars($row['Status'] ?? 'Unknown') . '">

                      <input type="checkbox" class="qr-checkbox sr-only">

                      <img src="' . $qrImage . '" alt="QR Code ' . $gallonId . '" class="w-32 h-32 object-contain">
                      <p class="text-sm mt-2 text-gray-600">' . $codeValue . '</p>

                      <!-- Hover Info Tooltip -->
                      <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full bg-white border border-gray-200 shadow-md rounded-lg p-3 text-xs w-52 opacity-0 group-hover:opacity-100 pointer-events-none transition duration-300 z-50">
                        <p><span class="font-semibold text-gray-700">Code Value:</span> ' . $codeValue . '</p>
                        <p><span class="font-semibold text-gray-700">Current Owner:</span> ' . $owner . '</p>
                        <p><span class="font-semibold text-gray-700">Gallon Type:</span> ' . htmlspecialchars($row['Gallon Type'] ?? 'Unknown') . '</p>
                        <p><span class="font-semibold text-gray-700">Status:</span> ' . htmlspecialchars($row['Status'] ?? 'Unknown') . '</p>
                      </div>
                    </div>';
                  }
                } else {
                  echo '<div class="col-span-3 p-6 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center text-gray-400">';
                  echo '<i class="fas fa-qrcode text-6xl mb-3"></i>';
                  echo '<p class="text-lg font-medium">No QR codes found</p>';
                  echo '<p class="text-sm">Generate QR codes from the Gallon Ownership page</p>';
                  echo '</div>';
                }
                ?>
              </div>

              <!-- Print Button -->
              <div class="mt-4 flex justify-end flex-shrink-0">
                <button id="printSelectedBtn"
                  class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-5 py-2 rounded-lg shadow-md flex items-center gap-2 transition">
                  <i class="fas fa-print"></i>
                  <span>Print Selected</span>
                </button>
              </div>
            </div>

            <!-- RIGHT SECTION (Scanner) -->
            <div class="right-section bg-white p-6 rounded-2xl shadow-lg">
              <!-- Scanner Header -->
              <div class="flex items-center justify-between mb-4 flex-shrink-0">
                <h2 class="text-lg font-semibold text-blue-600">
                  <i class="fas fa-camera mr-2"></i> Scan QR Code
                </h2>
                <button id="toggleCameraBtn"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition font-medium">
                  Open Camera
                </button>
              </div>

              <!-- Camera Container -->
              <div class="flex-shrink-0 mb-4">
                <div class="relative mx-auto rounded-lg overflow-hidden border-2 border-gray-300 w-full max-w-[320px]" style="aspect-ratio: 1;">
                  <div id="reader" class="absolute inset-0"></div>

                  <!-- Position Indicator -->
                  <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="border-4 border-blue-400 rounded-lg w-[220px] h-[220px] opacity-80 animate-pulse"></div>
                  </div>
                </div>

                <!-- Camera Status Message -->
                <p id="cameraStatus" class="text-center text-gray-500 text-sm mt-3 italic">
                  Camera is closed.
                </p>
              </div>

              <!-- File Upload -->
              <div class="mb-4 flex-shrink-0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Or Upload QR Image:</label>
                <input type="file" id="qrFileInput" accept="image/*"
                  class="block w-full text-sm text-gray-700 border-2 border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
              </div>

              <!-- Result Table -->
              <div id="resultTable" class="bg-gradient-to-br from-blue-50 to-gray-50 rounded-lg p-4 border border-blue-100 flex-shrink-0">
                <h3 class="font-semibold text-blue-600 mb-3 flex items-center">
                  <i class="fas fa-info-circle mr-2"></i>QR Code Information
                </h3>
                <table class="w-full text-left border-collapse text-sm">
                  <tbody>
                    <tr class="border-b border-blue-200">
                      <td class="py-2 font-medium text-gray-700">Code Value:</td>
                      <td id="codeValue" class="py-2 text-gray-800 font-medium">---</td>
                    </tr>
                    <tr class="border-b border-blue-200">
                      <td class="py-2 font-medium text-gray-700">Current Owner:</td>
                      <td id="codeOwner" class="py-2 text-gray-800">---</td>
                    </tr>
                    <tr class="border-b border-blue-200">
                      <td class="py-2 font-medium text-gray-700">Gallon Type:</td>
                      <td id="codeType" class="py-2 text-gray-800">---</td>
                    </tr>
                    <tr>
                      <td class="py-2 font-medium text-gray-700">Status:</td>
                      <td id="codeStatus" class="py-2 text-gray-800">---</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="..Admin/includes/admin_toast.js"></script>

  <script>
    let html5QrcodeScanner;
    let cameraActive = false;
    let descendingOrder = true;

    const fileInput = document.getElementById("qrFileInput");
    const toggleCameraBtn = document.getElementById("toggleCameraBtn");
    const cameraStatus = document.getElementById("cameraStatus");

    const codeValue = document.getElementById("codeValue");
    const codeOwner = document.getElementById("codeOwner");
    const codeType = document.getElementById("codeType");
    const codeStatus = document.getElementById("codeStatus");

    const toggleOrderBtn = document.getElementById("toggleOrderBtn");
    const orderLabel = document.getElementById("orderLabel");
    const qrGallery = document.getElementById("qrGallery");

    // Camera controls
    toggleCameraBtn.addEventListener("click", async () => {
      if (!cameraActive) {
        startScanner();
      } else {
        stopScanner();
      }
    });

    async function startScanner() {
      try {
        const qrboxSize = 250;
        html5QrcodeScanner = new Html5Qrcode("reader");
        cameraStatus.textContent = "📷 Initializing camera...";

        await html5QrcodeScanner.start({
            facingMode: "environment"
          }, {
            fps: 10,
            qrbox: {
              width: qrboxSize,
              height: qrboxSize
            },
            aspectRatio: 1.0,
            videoConstraints: {
              zoom: 1.4
            }
          },
          qrCodeMessage => handleDecodedCode(qrCodeMessage),
          errorMessage => {
            cameraStatus.textContent = "Position QR code in frame...";
          }
        );

        cameraActive = true;
        toggleCameraBtn.textContent = "Close Camera";
        cameraStatus.textContent = "✓ Camera active - Point at QR code";
      } catch (err) {
        console.error("Camera start failed:", err);
        cameraStatus.textContent = "❌ Failed to access camera";
      }
    }

    async function stopScanner() {
      try {
        if (html5QrcodeScanner) {
          await html5QrcodeScanner.stop();
          await html5QrcodeScanner.clear();
          cameraActive = false;
          toggleCameraBtn.textContent = "Open Camera";
          cameraStatus.textContent = "Camera is closed.";
        }
      } catch (err) {
        console.error("Error stopping camera:", err);
      }
    }

    // File upload handler
    fileInput.addEventListener("change", async () => {
      const file = fileInput.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append("file", file);
      try {
        const response = await fetch("https://api.qrserver.com/v1/read-qr-code/", {
          method: "POST",
          body: formData
        });
        const json = await response.json();
        const decodedText = json[0].symbol[0].data;
        if (decodedText) handleDecodedCode(decodedText);
        else codeValue.innerText = "No QR code found.";
      } catch (err) {
        console.error(err);
        codeValue.innerText = "Error reading QR code";
      }
    });

    // QR code decoded handler
    function handleDecodedCode(qrValue) {
      codeValue.innerText = qrValue;
      cameraStatus.textContent = "✅ QR Code Scanned Successfully!";

      fetch("fetch_qr.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "code=" + encodeURIComponent(qrValue)
        })
        .then(res => res.json())
        .then(data => {
          codeOwner.innerText = data.owner || "Unknown";
          codeType.innerText = data.type || "Unknown";
          codeStatus.innerText = data.status || "Unknown";
        })
        .catch(err => console.error(err));
    }

    // QR card selection
    document.querySelectorAll(".qr-card").forEach(card => {
      const checkbox = card.querySelector(".qr-checkbox");

      card.addEventListener("click", e => {
        if (e.target.tagName.toLowerCase() === "input") return;
        checkbox.checked = !checkbox.checked;
        toggleCardSelection(card, checkbox.checked);
      });

      checkbox.addEventListener("change", () => {
        toggleCardSelection(card, checkbox.checked);
      });
    });

    function toggleCardSelection(card, isSelected) {
      if (isSelected) {
        card.classList.add("ring-4", "ring-blue-400", "bg-blue-50");
      } else {
        card.classList.remove("ring-4", "ring-blue-400", "bg-blue-50");
      }
    }

    // Search function
    function searchTable() {
      const searchValue = document.getElementById("searchInput").value.toLowerCase();
      const cards = document.querySelectorAll(".qr-card");

      cards.forEach(card => {
        const codeValue = card.dataset.code.toLowerCase();
        if (codeValue.includes(searchValue)) {
          card.style.display = "";
        } else {
          card.style.display = "none";
        }
      });
    }

    // Sort toggle
    toggleOrderBtn.addEventListener("click", () => {
      const cards = Array.from(qrGallery.querySelectorAll(".qr-card"));

      cards.sort((a, b) => {
        const idA = parseInt(a.dataset.id);
        const idB = parseInt(b.dataset.id);
        return descendingOrder ? idB - idA : idA - idB;
      });

      qrGallery.innerHTML = "";
      cards.forEach(card => qrGallery.appendChild(card));

      descendingOrder = !descendingOrder;
      orderLabel.textContent = descendingOrder ? "Recent" : "Oldest";
    });

    // Print selected QR codes
    document.getElementById("printSelectedBtn").addEventListener("click", () => {
      const selectedCards = [...document.querySelectorAll(".qr-checkbox:checked")];
      if (selectedCards.length === 0) {
        alert("Please select at least one QR code to print.");
        return;
      }

      let printContent = `
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 20px; text-align: center;">
      `;

      selectedCards.forEach(checkbox => {
        const card = checkbox.closest(".qr-card");
        const img = card.querySelector("img").src;
        const code = card.dataset.code;
        const type = card.dataset.type;

        printContent += `
          <div style="border: 1px solid #ccc; border-radius: 10px; padding: 10px;">
            <img src="${img}" style="width:120px; height:120px; object-fit: contain; margin-bottom: 8px;">
            <p style="margin: 2px 0; font-size: 14px;"><strong>Code:</strong> ${code}</p>
            <p style="margin: 2px 0; font-size: 13px; color: #555;"><strong>Type:</strong> ${type}</p>
          </div>
        `;
      });

      printContent += `</div>`;

      const printWindow = window.open("", "_blank");
      printWindow.document.write(`
        <html>
          <head>
            <title>Print QR Codes</title>
            <style>
              body { font-family: Arial, sans-serif; }
              @media print {
                body { margin: 0; padding: 0; }
                img, div { page-break-inside: avoid; }
              }
            </style>
          </head>
          <body>${printContent}</body>
        </html>
      `);
      printWindow.document.close();

      const images = printWindow.document.querySelectorAll("img");
      const imagePromises = Array.from(images).map(img => {
        return new Promise(resolve => {
          if (img.complete) resolve();
          else img.onload = img.onerror = resolve;
        });
      });

      Promise.all(imagePromises).then(() => {
        printWindow.focus();
        printWindow.print();
      });
    });
  </script>
</body>

</html>