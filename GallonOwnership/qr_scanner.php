<?php
// require_once '../check_session.php';
require_once '../Database/db_connection.php';

// if (!isAdmin()) {
//     header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
//     exit();
// }

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
  </style>
</head>

<body class="bg-gray-50">
  <div class="flex h-screen">
    <!-- Sidebar -->
    <aside
      class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">
      <div class="p-7 border-b border-blue-400 flex items-center gap-2">
        <i class="fas fa-droplet text-3xl"></i>
        <div class="overflow-hidden">
          <span
            class="text-2xl font-semibold block whitespace-nowrap transition-all duration-500 ease-in-out opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
            RCH Water
          </span>
        </div>
      </div>

      <nav class="mt-6 flex-1">
        <ul class="space-y-1 px-4">
          <li>
            <a href="../Admin/dashboard.php"
              class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
              <i class="fas fa-chart-line text-lg"></i>
              <div class="overflow-hidden ml-3">
                <span
                  class="block whitespace-nowrap transition-all duration-500 ease-in-out opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                  Dashboard
                </span>
              </div>
            </a>
          </li>
          <li>
            <a href="../Order/orders_list.php"
              class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
              <i class="fas fa-shopping-cart text-lg"></i>
              <div class="overflow-hidden ml-3">
                <span
                  class="block whitespace-nowrap transition-all duration-500 ease-in-out opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                  Orders
                </span>
              </div>
            </a>
          </li>
          <li>
            <a href="../GallonOwnership/gallon_ownership.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg font-medium">
              <i class="fas fa-tint text-lg"></i>
              <div class="overflow-hidden ml-3">
                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                  Gallon Ownership
                </span>
              </div>
            </a>
          </li>
          <li>
            <a href="../Inventory/inventory_list.php"
              class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
              <i class="fas fa-boxes text-lg"></i>
              <div class="overflow-hidden ml-3">
                <span
                  class="block whitespace-nowrap transition-all duration-500 ease-in-out opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                  Inventory
                </span>
              </div>
            </a>
          </li>
          <li>
            <a href="../Customer/customers_list.php"
              class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
              <i class="fas fa-users text-lg"></i>
              <div class="overflow-hidden ml-3">
                <span
                  class="block whitespace-nowrap transition-all duration-500 ease-in-out opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                  Customers
                </span>
              </div>
            </a>
          </li>
          <li>
            <a href="../Delivery/deliveries_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
              <i class="fas fa-truck text-lg"></i>
              <div class="overflow-hidden ml-3">
                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                  Deliveries
                </span>
              </div>
            </a>
          </li>
        </ul>
      </nav>

      <div class="border-t border-blue-400 p-4">
        <a href="../logout.php"
          class="flex items-center px-4 py-3 font-medium text-white hover:bg-blue-500 rounded-lg transition-colors">
          <i class="fas fa-sign-out-alt text-lg"></i>
          <span class="ml-3 hidden group-hover:inline">Logout</span>
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <div id="page-content"
      class="main-content fixed w-full top-0 right-0 overflow-auto h-full transition-all duration-300 ease-in-out">
      <div class="flex-1 transition-all duration-300 ease-in-out group-hover:ml-64 ml-20 flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 shadow-sm p-4 flex items-center justify-between">
          <div class="flex flex-col">
            <h1 class="text-3xl font-semibold text-blue-500">Gallon Ownership</h1>
            <p class="text-sm text-gray-700 mt-1">Manage and track ownership of gallons between customers and the station.</p>
          </div>

          <div class="flex items-center space-x-4">
            <a href="../Notification/notifications.php" title="Notifications"
              class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
              <i class="fas fa-bell text-blue-500 text-xl"></i>
            </a>
            <a href="../Messaging/messages.php" title="Messages"
              class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
              <i class="fas fa-envelope text-blue-500 text-xl"></i>
            </a>
            <a href="../Admin/profile.php" title="Profile"
              class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
              <i class="fas fa-user text-blue-500 text-xl"></i>
            </a>


          </div>
        </div>

        <!-- BODY -->
        <div class="m-4 flex flex-1 gap-6">

          <!-- LEFT SECTION (2/3) -->
          <div class="w-2/3 bg-white p-4 rounded-2xl shadow-md flex flex-col h-full">
            <div class="flex items-center justify-between mb-4 gap-4">
              <div class="flex-shrink-0">
                <a href="gallon_ownership.php"
                  class="bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition inline-flex items-center">
                  <i class="fas fa-arrow-left mr-2"></i> Return
                </a>
              </div>

              <div class="flex-1 ml-4 relative">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="searchInput"
                  placeholder="Search for Qr Image using Code Value ..."
                  class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-sm"
                  style="font-style: italic;"
                  oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';"
                  onkeyup="searchTable()">
              </div>

              <button id="toggleOrderBtn"
                class="flex items-center bg-blue-100 text-blue-600 font-medium px-4 py-2 rounded-lg hover:bg-blue-200 transition whitespace-nowrap">
                <i class="fas fa-sort mr-2"></i> <span id="orderLabel">Recent</span>
              </button>
            </div>

            <div class="flex items-center justify-start mb-4 bg-blue-100 p-3 rounded-lg">
              <i class="fas fa-qrcode mr-3 text-blue-600 text-xl"></i>
              <h2 class="text-lg font-semibold text-blue-600 m-0">QR Code Gallery</h2>
            </div>

            <div id="qrGallery"
              class="grid grid-cols-3 gap-4 overflow-x-auto scrollbar-hide p-1 max-h-[450px]">

              <?php
              $query = "SELECT 
                    go.ownership_id AS `Gallon ID`,
                    go.gallon_type AS `Gallon Type`,
                    u.name AS `Owner`,
                    go.code_value AS `Code Value`,
                    go.status AS `Status`,
                    go.qr_image AS `QR Image`
                    FROM gallon_ownership AS go
                    LEFT JOIN users AS u ON go.owner_id = u.user_id
                    WHERE go.qr_image IS NOT NULL AND go.qr_image <> ''
                    ORDER BY go.ownership_id DESC";

              $stmt = $db->prepare($query);
              $stmt->execute();
              $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($rows) > 0) {
                foreach ($rows as $row) {
                  $qrImage = htmlspecialchars($row['QR Image']);
                  $codeValue = htmlspecialchars($row['Code Value']);
                  $owner = htmlspecialchars($row['Owner'] ?? 'Unknown');
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
                      <p><span class="font-semibold text-gray-700">Owner:</span> ' . $owner . '</p>
                      <p><span class="font-semibold text-gray-700">Gallon Type:</span> ' . htmlspecialchars($row['Gallon Type'] ?? 'Unknown') . '</p>
                      <p><span class="font-semibold text-gray-700">Status:</span> ' . htmlspecialchars($row['Status'] ?? 'Unknown') . '</p>
                    </div>
                  </div>';
                }
              } else {
                echo '<div class="p-3 border rounded-xl flex flex-col items-center shadow-sm hover:shadow-md transition cursor-pointer">';
                echo '<img src="assets/sample-qr1.png" alt="No QR" class="w-32 h-32 object-contain">';
                echo '<p class="text-sm mt-2 text-gray-600">No QR codes found.</p>';
                echo '</div>';
              }
              ?>
            </div>
            <div class="mt-4 flex justify-end">
              <button id="printSelectedBtn"
                class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-5 py-2 rounded-lg shadow-md flex items-center gap-2">
                <i class="fas fa-print"></i>
                <span>Print Selected</span>
              </button>
            </div>

          </div>

          <!-- RIGHT SECTION (1/3) -->
          <div class="w-1/3 bg-white p-4 rounded-2xl shadow-md flex flex-col justify-between h-full">
            <div>
              <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-blue-500 flex-1">
                  <i class="fas fa-camera mr-2"></i> Scan QR Code
                </h2>
                <button id="toggleCameraBtn"
                  class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm transition">
                  Open Camera
                </button>
              </div>

              <div class="relative mx-auto rounded-lg overflow-hidden border border-gray-300 w-[320px] h-[320px]" id="readerContainer">
                <div id="reader" class="absolute inset-0"></div>

                <!--Position Indicator -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                  <div class="border-4 border-blue-400 rounded-lg w-[220px] h-[220px] opacity-80 animate-pulse"></div>
                </div>
              </div>

              <!--Camera Status Message -->
              <p id="cameraStatus" class="text-center text-gray-500 text-sm mt-2 italic">
                Camera is closed.
              </p>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-600 mb-1">Or Upload a QR Code Image:</label>
              <input type="file" id="qrFileInput" accept="image/*"
                class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div id="resultTable" class="mt-4 bg-gray-50 rounded-lg p-3 text-sm">
              <h3 class="font-semibold text-blue-500 mb-2">QR Code Information</h3>
              <table class="w-full text-left border-collapse">
                <tbody>
                  <tr class="border-b border-gray-200">
                    <td class="py-1 font-medium text-gray-600">Code Value:</td>
                    <td id="codeValue" class="py-1 text-gray-700">---</td>
                  </tr>
                  <tr class="border-b border-gray-200">
                    <td class="py-1 font-medium text-gray-600">Owner:</td>
                    <td id="codeOwner" class="py-1 text-gray-700">---</td>
                  </tr>
                  <tr class="border-b border-gray-200">
                    <td class="py-1 font-medium text-gray-600">Gallon Type:</td>
                    <td id="codeType" class="py-1 text-gray-700">---</td>
                  </tr>
                  <tr>
                    <td class="py-1 font-medium text-gray-600">Status:</td>
                    <td id="codeStatus" class="py-1 text-gray-700">---</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script>
    let html5QrcodeScanner;
    let cameraActive = false;

    const fileInput = document.getElementById("qrFileInput");
    const toggleCameraBtn = document.getElementById("toggleCameraBtn");
    const cameraStatus = document.getElementById("cameraStatus");

    const codeValue = document.getElementById("codeValue");
    const codeOwner = document.getElementById("codeOwner");
    const codeType = document.getElementById("codeType");
    const codeStatus = document.getElementById("codeStatus");

    toggleCameraBtn.addEventListener("click", async () => {
      if (!cameraActive) {
        startScanner();
      } else {
        stopScanner();
      }
    });

    async function startScanner() {
      try {
        const qrboxSize = 250; // fixed square
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
            aspectRatio: 1.0, // keep camera feed square
            videoConstraints: {
              zoom: 1.4
            }
          },
          qrCodeMessage => handleDecodedCode(qrCodeMessage),
          errorMessage => {
            cameraStatus.textContent = "Fix qr code position..."; // feedback while scanning
          }
        );

        cameraActive = true;
        toggleCameraBtn.textContent = "Close Camera";
        cameraStatus.textContent = "Point your camera at a QR code...";
      } catch (err) {
        console.error("Camera start failed:", err);
        cameraStatus.textContent = "Failed to access camera.";
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

    // Handle uploaded QR image
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
      }
    });

    //  When QR successfully scanned
    function handleDecodedCode(qrValue) {
      codeValue.innerText = qrValue;
      cameraStatus.textContent = "✅ QR Code Scanned!";

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


    // Highlight selected QR cards and handle printing
    document.querySelectorAll(".qr-card").forEach(card => {
      const checkbox = card.querySelector(".qr-checkbox");

      card.addEventListener("click", e => {
        // Prevent double toggling if clicking the checkbox itself
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

    document.getElementById("printSelectedBtn").addEventListener("click", () => {
      const selectedCards = [...document.querySelectorAll(".qr-checkbox:checked")];
      if (selectedCards.length === 0) {
        alert("Please select at least one QR code to print.");
        return;
      }

      // Build print content
      let printContent = `
    <div style="
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      padding: 20px;
      text-align: center;
    ">
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

      // Open new print window
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
          else {
            img.onload = img.onerror = resolve;
          }
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