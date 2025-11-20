<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - RCH Water</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-100">

  <div class="flex">

    <!-- SIDEBAR -->
    <div class="w-64 bg-gradient-to-b from-blue-500 to-blue-600 text-white min-h-screen p-6 rounded-r-2xl shadow-lg">
      <h1 class="text-2xl font-semibold mb-10 flex items-center gap-2">
        <i class="bi bi-droplet"></i> RCH Water
      </h1>

      <nav class="space-y-2">
        <a href="#" class="flex items-center gap-2 p-3 rounded-lg hover:bg-blue-700 transition">
          <i class="bi bi-droplet-half"></i> Order Water Refill
        </a>
        <a href="#" class="flex items-center gap-2 p-3 rounded-lg hover:bg-blue-700 transition">
          <i class="bi bi-cart"></i> Browse Products
        </a>
        <a href="#" class="flex items-center gap-2 p-3 rounded-lg bg-blue-800 rounded-lg">
          <i class="bi bi-box-seam"></i> My Orders
        </a>
        <a href="#" class="flex items-center gap-2 p-3 rounded-lg hover:bg-blue-700 transition">
          <i class="bi bi-droplet"></i> My Gallons
        </a>
        <a href="#" class="flex items-center gap-2 p-3 rounded-lg hover:bg-blue-700 transition">
          <i class="bi bi-receipt"></i> Receipts
        </a>
      </nav>

      <button class="mt-16 w-full bg-blue-700 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-800 transition">
        <i class="bi bi-box-arrow-left text-lg"></i> Logout
      </button>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-10">

      <!-- TOP BAR -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-blue-700">My Orders</h1>
          <p class="text-gray-500 text-sm">View and track your orders and deliveries in real time.</p>
        </div>

        <!-- Notifications / Profile -->
        <div class="flex items-center gap-6">
          <div class="relative cursor-pointer">
            <i class="bi bi-bell text-2xl text-blue-700"></i>
            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs px-1 rounded-full">3</span>
          </div>
          <div class="relative cursor-pointer">
            <i class="bi bi-chat-left-text text-2xl text-blue-700"></i>
            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs px-1 rounded-full">8</span>
          </div>
          <button class="flex items-center gap-2 bg-blue-100 text-blue-700 px-4 py-2 rounded-full border border-blue-300">
            <i class="bi bi-person-circle text-xl"></i> Profile
          </button>
        </div>
      </div>

      <!-- ORDER DETAILS CARD -->
      <div class="bg-white shadow-md rounded-2xl p-8">

        <!-- Delivery Info -->
        <div class="mb-6">
          <h2 class="font-semibold mb-3 flex items-center gap-2 text-blue-700">
            <i class="bi bi-geo-alt"></i> Delivery Information
          </h2>

          <p class="font-semibold">Christine May Padua</p>
          <p>116 Purok 1, Tibig, Lipa City, Batangas 4217</p>
          <p class="text-sm mt-1">09161891555</p>
        </div>

        <!-- Order Items -->
        <div class="border rounded-xl p-5 mb-6">

          <span class="text-red-600 font-semibold text-sm">Pending</span>

          <table class="w-full mt-3 text-sm">
            <thead class="border-b">
              <tr class="text-left">
                <th class="py-2">Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
              </tr>
            </thead>

            <tbody>
              <tr class="border-b">
                <td class="py-2">20L Slim Container Gallon</td>
                <td>2</td>
                <td>₱170.00</td>
                <td>₱340.00</td>
              </tr>
              <tr class="border-b">
                <td class="py-2">Slim Gallon Faucet - Press</td>
                <td>10</td>
                <td>₱25.00</td>
                <td>₱250.00</td>
              </tr>
            </tbody>
          </table>

          <div class="mt-4 space-y-1 text-sm">
            <p class="flex justify-between"><span>Subtotal</span><span>₱590.00</span></p>
            <p class="flex justify-between"><span>Tax (8%)</span><span>₱47.20</span></p>
            <p class="flex justify-between"><span>Delivery Fee</span><span>₱40.00</span></p>

            <p class="flex justify-between font-bold text-lg mt-3">
              <span>Total Cost</span>
              <span class="text-blue-700">₱677.20</span>
            </p>
          </div>
        </div>

        <!-- Order ID -->
        <div class="border rounded-xl p-5 mb-6 text-sm">
          <div class="flex justify-between mb-2">
            <span class="font-semibold">Order ID</span>
            <span>ORD-110325001</span>
          </div>
          <div class="flex justify-between mb-2">
            <span>Paid by</span>
            <span>Cash on Delivery</span>
          </div>
          <div class="flex justify-between mb-2">
            <span>Order Date & Time</span>
            <span>03-11-2025 | 8:38</span>
          </div>
          <div class="flex justify-between">
            <span>Ship Date & Time</span>
            <span>05-11-2025 | 11:00</span>
          </div>
        </div>

        <!-- Track Order Button -->
        <div class="text-right">
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
            Track Order
          </button>
        </div>

      </div>

    </div>
  </div>

</body>
</html>
