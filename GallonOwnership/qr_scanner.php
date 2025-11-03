<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>QR Code Scanner & Upload</title>
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2d9d6c5b2.js" crossorigin="anonymous"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center h-screen">

  <!-- Button to open the scanner -->
  <button id="openScannerBtn"
    class="bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition">
    <i class="fas fa-qrcode"></i> Scan QR Code
  </button>

  <!-- Modal -->
  <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white rounded-xl p-6 w-[90%] max-w-md relative shadow-xl">

      <!-- Close Button --> <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold text-center mb-4">QR Code Scanner & Uploader</h2> <!-- Camera Scanner -->
      <div id="reader" class="w-full mb-3 border rounded-md"></div> <!-- Upload Option -->
      <p class="text-center text-gray-600 mb-2">or upload a QR image:</p>
      <input type="file" id="qrFileInput" accept="image/*" class="w-full mb-3 border p-2 rounded-md">
      <!-- QR Result -->
      <div id="result" class="mt-4 text-center font-medium"></div>
    </div>
  </div>

  <script>
    let html5QrcodeScanner;
    const modal = document.getElementById("qrModal");
    const openBtn = document.getElementById("openScannerBtn");
    const closeBtn = document.getElementById("closeModalBtn");
    const fileInput = document.getElementById("qrFileInput");
    const resultDiv = document.getElementById("result");

    // Open modal + start scanning
    openBtn.addEventListener("click", () => {
      modal.classList.remove("hidden");
      startScanner();
    });

    // Close modal + stop scanning
    closeBtn.addEventListener("click", () => {
      modal.classList.add("hidden");
      stopScanner();
    });

    // Close modal when clicking outside
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.add("hidden");
        stopScanner();
      }
    });

    // Start camera scanner
    function startScanner() {
      html5QrcodeScanner = new Html5Qrcode("reader");
      html5QrcodeScanner.start({
          facingMode: "environment"
        }, {
          fps: 10,
          qrbox: 250
        },

        qrCodeMessage => {
          handleDecodedCode(qrCodeMessage);
          // keep scanner running so user can rescan
        },
        errorMessage => {
          console.log(errorMessage);
        }
      );
    }

    // Stop the camera scanner
    function stopScanner() {
      if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
          console.log("Scanner stopped.");
        }).catch(err => {
          console.error("Stop error:", err);
        });
      }
    }

    // Handle file upload
    fileInput.addEventListener("change", async () => {
      const file = fileInput.files[0];
      if (!file) return;

      resultDiv.innerHTML = "Decoding uploaded image...";
      const formData = new FormData();
      formData.append("file", file);

      try {
        const response = await fetch("https://api.qrserver.com/v1/read-qr-code/", {
          method: "POST",
          body: formData
        });
        const json = await response.json();
        const decodedText = json[0].symbol[0].data;

        if (decodedText) {
          handleDecodedCode(decodedText);
        } else {
          resultDiv.innerHTML = "No QR code found in image.";
        }
      } catch (err) {
        resultDiv.innerHTML = "Error decoding image.";
        console.error(err);
      }
    });

    // Handle decoded QR result
    function handleDecodedCode(codeValue) {
      // Reset uploaded file input to avoid confusion
      fileInput.value = "";

      resultDiv.innerHTML = `<p class="text-green-700">Scanned Code: <strong>${codeValue}</strong></p>`;
      fetch("fetch_qr.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "code=" + encodeURIComponent(codeValue)
        })
        .then(res => res.text())
        .then(data => {
          resultDiv.innerHTML += `<div class="mt-3 text-gray-700"><strong>Details:</strong><br>${data}</div>`;
        });
    }
  </script>
</body>

</html>