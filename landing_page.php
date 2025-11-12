<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Water Refill Order</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Poppins', sans-serif; }

    /* Step indicator styling */
    .step-dots {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
    }

    .step-dot {
      width: 2.5rem;
      height: 2.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 9999px;
      transition: all 0.3s ease;
    }

    .step-line {
      flex-grow: 1;
      height: 2px;
      background-color: #E5E7EB;
      transition: background-color 0.3s ease;
    }

    .step-dot.completed,
    .step-dot.active {
      background-color: #2563eb;
      color: white;
    }

    .step-dot.pending {
      background-color: #E5E7EB;
      color: #4B5563;
    }

    .step-line.completed {
      background-color: #2563eb;
    }
    /* Thicker lines between steps */
    .step-line { height: 6px; border-radius: 4px; }

    /* Sidebar buttons */
    .sidebar-btn {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      border-radius: 0.75rem;
      color: rgba(255,255,255,0.95);
      background: transparent;
      border: none;
      width: 100%;
      text-align: left;
      transition: background-color 0.15s ease, color 0.15s ease;
    }

    .sidebar-btn .icon-circle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2rem;
      height: 2rem;
      border-radius: 0.5rem;
      background: rgba(255,255,255,0.08);
      color: white;
    }

    /* Hover should be a bit whiter */
    .sidebar-btn:hover {
      background: rgba(255,255,255,0.12);
      color: #ffffff;
    }

    /* Active sidebar selection */
    .sidebar-btn.active {
      background: rgba(255,255,255,0.18);
      box-shadow: inset 0 0 0 3px rgba(255,255,255,0.06);
    }

    /* Logout button design (match image) */
    .logout-btn {
  display:flex;
  align-items:center;
  gap:0.5rem;
  padding:0.6rem 0.8rem;
  border-radius:0.6rem;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.18);
  color: white;
  width:100%;
  justify-content:center;
  transition: background-color 0.12s ease, transform 0.06s ease;
    .logout-btn:hover {
      background: #f1f8ff;
      color: #2563eb;
      transform: translateY(-1px);
    }
    }

    .logout-btn .bi { font-size: 1rem; }

    /* Header icons (notification/cart/profile) */
    .header-action {
      display:inline-flex;
      align-items:center;
      gap:0.5rem;
      background: white;
      color: #2563eb;
      border-radius: 0.5rem;
      padding: 0.35rem 0.6rem;
      box-shadow: 0 1px 0 rgba(0,0,0,0.03);
      border: 1px solid rgba(0,0,0,0.04);
      transition: background-color 0.12s ease, transform 0.06s ease;
    }
    .header-action:hover { background:#f1f8ff; transform: translateY(-1px); }

    /* Header bottom line */
    .main-header { border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem; }

    /* Disabled time slot styling */
    button[disabled] {
      cursor: not-allowed !important;
      opacity: 0.5 !important;
      background-color: #f3f4f6 !important;
      color: #9ca3af !important;
    }

    button[disabled]:hover {
      background-color: #f3f4f6 !important;
      color: #9ca3af !important;
    }
  </style>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
  <div class="flex h-screen">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-blue-500 to-blue-600 text-white flex flex-col p-6">
      <h2 class="text-xl font-semibold mb-8">RCH Water</h2>
      <nav class="flex-1 space-y-4">
        <button id="navOrder" class="sidebar-btn active">
          <span class="icon-circle"><i class="bi bi-droplet-fill"></i></span>
          <span>Order Water Refill</span>
        </button>
        <button id="navBrowse" class="sidebar-btn">
          <span class="icon-circle"><i class="bi bi-cart-fill"></i></span>
          <span>Browse Products</span>
        </button>
        <button id="navMyOrders" class="sidebar-btn">
          <span class="icon-circle"><i class="bi bi-box-seam-fill"></i></span>
          <span>My Orders</span>
        </button>
        <button id="navMyGallons" class="sidebar-btn">
          <span class="icon-circle"><i class="bi bi-droplet-fill"></i></span>
          <span>My Gallons</span>
        </button>
        <button id="navReceipts" class="sidebar-btn">
          <span class="icon-circle"><i class="bi bi-receipt-cutoff"></i></span>
          <span>Receipts</span>
        </button>
      </nav>
      <div class="mt-6">
        <button id="logoutBtn" class="logout-btn">
          <i class="bi bi-box-arrow-right-fill"></i>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 bg-white rounded-l-xl shadow overflow-y-auto">
    <div class="flex justify-between items-center main-header">
        <div>
          <h1 class="text-2xl font-semibold">Order Water Refill</h1>
          <p class="text-gray-500 text-sm">Hydrate made easy—order and we'll handle the rest!</p>
        </div>

        <div class="flex space-x-3 items-center">
          <button class="header-action" title="Notifications"><i class="bi bi-bell-fill"></i></button>
          <button class="header-action" title="Messages"><i class="bi bi-chat-dots-fill"></i></button>
          <button class="header-action" title="Cart"><i class="bi bi-cart-fill"></i><span class="ml-2 hidden md:inline">Cart</span></button>
          <button class="header-action" title="Profile"><i class="bi bi-person-fill"></i><span class="ml-2 hidden md:inline">Profile</span></button>
        </div>
      </div>

      <!-- Steps -->
      <div class="mt-8 bg-gray-50 p-6 rounded-xl border">
        <div class="step-dots">
          <div id="step1Dot" class="step-dot active">1</div>
          <div class="step-line"></div>
          <div id="step2Dot" class="step-dot pending">2</div>
          <div class="step-line"></div>
          <div id="step3Dot" class="step-dot pending">3</div>
          <div class="step-line"></div>
          <div id="step4Dot" class="step-dot pending">4</div>
          <div class="step-line"></div>
          <div id="step5Dot" class="step-dot pending">5</div>
          <div class="step-line"></div>
          <div id="step6Dot" class="step-dot pending">6</div>
        </div>

        <h2 id="stepTitle" class="text-lg font-semibold mb-4">Step 1: Choose Gallon Type</h2>

  <!-- Product Set 1 -->
  <div id="set1" class="grid grid-cols-2 gap-6">
          <!-- Slim Gallon -->
          <div id="slimCard" class="border-4 border-blue-500 p-4 rounded-xl cursor-pointer text-center hover:bg-blue-500 hover:text-white transition">
            <img src="img/20L_box_type_gallon.jpg" class="mx-auto w-40" />
            <p class="font-medium mt-2">Slim Gallon</p>
          </div>

          <!-- Round Gallon -->
          <div id="roundCard" data-price="30" class="border-2 border-gray-300 p-4 rounded-xl cursor-pointer text-center hover:bg-blue-500 hover:text-white transition">
            <img src="img/18.9L_round_gallon.jpg" class="mx-auto w-40" />
            <p class="font-medium mt-2">Round Gallon</p>
          </div>
        </div>

        <!-- Product Set 2 (hidden initially) - Step 2: Choose Size -->
        <div id="set2" class="grid grid-cols-2 gap-6 hidden">
          <div id="sizeCard1" data-size="large" data-price="30" class="size-card border-2 border-gray-300 p-6 rounded-xl cursor-pointer text-center transition hover:bg-blue-600 hover:text-white">
            <div class="p-6 bg-white rounded-md">
              <img src="img/20L_box_type_gallon.jpg" class="mx-auto w-36" />
            </div>
            <p class="font-medium mt-4">Slim Gallon - 20L</p>
            <p class="font-large mt-4">₱30.00</p>
          </div>

          <div id="sizeCard2" data-size="small" data-price="20" class="size-card border-2 border-gray-300 p-6 rounded-xl cursor-pointer text-center transition hover:bg-blue-600 hover:text-white">
            <div class="p-6 bg-white rounded-md">
              <img src="img/10L_box_type_gallon.jpg" class="mx-auto w-36" />
            </div>
            <p class="font-medium mt-4">Slim Gallon - 10L</p>
            <p class="font-large mt-4">₱20.00</p>
          </div>
        </div>

        <!-- Step 3: Quantity (hidden initially) -->
        <div id="set3" class="hidden">
          <div class="flex items-center justify-center space-x-6 py-8">
            <button id="qtyMinus" class="w-16 h-16 bg-white border rounded-lg text-2xl">-</button>
            <div id="qtyDisplay" class="text-4xl font-bold text-blue-600">1</div>
            <button id="qtyPlus" class="w-16 h-16 bg-white border rounded-lg text-2xl">+</button>
          </div>

          <div class="text-center mt-6">
            <div class="text-gray-500">Subtotal</div>
            <div id="subtotalDisplay" class="text-3xl font-bold text-blue-600 mt-2">₱30.00</div>
          </div>
        </div>
        </div>

        <!-- Controls: Step 2 has only Back; Step 3 has Back + Next -->
        <div id="controlsStep2" class="mt-6 hidden flex justify-start">
          <button id="backBtn" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
        </div>

        <div id="controlsStep3" class="mt-6 hidden flex justify-between">
          <button id="backBtn3" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
          <button id="nextBtn3" class="w-40 bg-blue-600 text-white py-2 rounded-lg">Next</button>
        </div>

        <!-- Step 4: Pickup date & time (hidden initially) -->
  <div id="set4" class="hidden mt-6 bg-white p-4 rounded-md">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Pick Up Date</label>
            <input id="pickupDate" type="date" class="w-full border rounded px-3 py-2" />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Pick Up Time</label>
            <div class="flex space-x-4">
              <button id="timeMorning" class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="07:30">Morning - 7:30 AM</button>
              <button id="timeAfternoon" class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="13:00">Afternoon - 1:00 PM</button>
            </div>
          </div>
        </div>

        <div id="controlsStep4" class="mt-6 hidden flex justify-between">
          <button id="backBtn4" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
          <button id="nextBtn4" class="w-40 bg-blue-600 text-white py-2 rounded-lg">Next</button>
        </div>

        <!-- Step 5: Delivery date & time slots (hidden initially) -->
        <div id="set5" class="hidden mt-6 bg-white p-4 rounded-md">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Delivery Date</label>
            <input id="deliveryDate" type="date" class="w-full border rounded px-3 py-2" />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Delivery Time</label>
            <div class="grid grid-cols-2 gap-4">
              <button id="delTime1" class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="09:00">Morning - 9:00 AM</button>
              <button id="delTime2" class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="11:00">Morning - 11:00 AM</button>
              <button id="delTime3" class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="14:00">Afternoon - 2:00 PM</button>
              <button id="delTime4" class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="16:00">Afternoon - 4:00 PM</button>
            </div>
          </div>
        </div>

        <div id="controlsStep5" class="mt-6 hidden flex justify-between">
          <button id="backBtn5" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
          <button id="nextBtn5" class="w-40 bg-blue-600 text-white py-2 rounded-lg">Next</button>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Elements
    const slim = document.getElementById('slimCard');
    const round = document.getElementById('roundCard');
    const set1 = document.getElementById('set1');
    const set2 = document.getElementById('set2');
  const set3 = document.getElementById('set3');
  const set4 = document.getElementById('set4');
  const set5 = document.getElementById('set5');
    const stepTitle = document.getElementById('stepTitle');
    const step1Dot = document.getElementById('step1Dot');
    const step2Dot = document.getElementById('step2Dot');
  const step3Dot = document.getElementById('step3Dot');
  const step4Dot = document.getElementById('step4Dot');
  const step5Dot = document.getElementById('step5Dot');
    const controlsStep2 = document.getElementById('controlsStep2');
  const controlsStep3 = document.getElementById('controlsStep3');
  const controlsStep4 = document.getElementById('controlsStep4');
  const controlsStep5 = document.getElementById('controlsStep5');
    const backBtn = document.getElementById('backBtn');
    const backBtn3 = document.getElementById('backBtn3');
    const nextBtn3 = document.getElementById('nextBtn3');
    const size1 = document.getElementById('sizeCard1');
    const size2 = document.getElementById('sizeCard2');
    const qtyMinus = document.getElementById('qtyMinus');
  const qtyPlus = document.getElementById('qtyPlus');
  const pickupDate = document.getElementById('pickupDate');
  const timeMorning = document.getElementById('timeMorning');
  const timeAfternoon = document.getElementById('timeAfternoon');
  const set4Next = document.getElementById('nextBtn4');
  const backBtn4 = document.getElementById('backBtn4');
  const deliveryDate = document.getElementById('deliveryDate');
  const delTime1 = document.getElementById('delTime1');
  const delTime2 = document.getElementById('delTime2');
  const delTime3 = document.getElementById('delTime3');
  const delTime4 = document.getElementById('delTime4');
  const backBtn5 = document.getElementById('backBtn5');
  const nextBtn5 = document.getElementById('nextBtn5');
    const qtyDisplay = document.getElementById('qtyDisplay');
    const subtotalDisplay = document.getElementById('subtotalDisplay');

    let chosenType = null; // 'slim' | 'round'
    let chosenSize = null; // 'large' | 'small'
    let chosenPrice = 0;
    let quantity = 1;
    // User info / address (defaults can be replaced with actual profile data)
    let userName = 'Christine May Padua';
    let userContact = '09161951955';
    let userAddress = {
      house: 'Camella Homes, Block 26 Lot 33',
      street: 'Alexia Street, Phase 1',
      barangay: 'Tibig',
      city: 'Lipa City',
      province: 'Batangas',
      postal: '4217'
    };
    // Orders array to hold multiple refill orders in one checkout
    let orders = [];

    function saveCurrentOrder() {
      if (!chosenType || !chosenPrice || !quantity) return null;
      let sizeLabel = '';
      if (chosenType === 'round') sizeLabel = '18.9L';
      else if (chosenType === 'slim') {
        if (chosenSize === 'large') sizeLabel = '20L';
        else if (chosenSize === 'small') sizeLabel = '10L';
      }
      const unit = chosenPrice || 0;
      const subtotal = unit * quantity;
      const order = {
        type: chosenType === 'slim' ? 'Slim' : 'Round',
        size: sizeLabel,
        quantity: quantity,
        unitPrice: unit,
        subtotal: subtotal
      };
      orders.push(order);
      return order;
    }

    function renderOrdersInStep6() {
      const tbody = document.getElementById('ordersTbody');
      const rows = (orders && orders.length) ? orders : (chosenType ? [
        {
          type: chosenType === 'slim' ? 'Slim' : 'Round',
          size: chosenSize || (chosenType === 'round' ? '18.9L' : ''),
          quantity: quantity,
          unitPrice: chosenPrice || 0,
          subtotal: (chosenPrice || 0) * quantity
        }
      ] : []);
      let html = '';
      let total = 0;
      rows.forEach(o => {
        html += `<tr>
          <td class="py-2 text-center">${o.type}</td>
          <td class="text-center">${o.size}</td>
          <td class="text-center">${o.quantity}</td>
          <td class="text-center">₱${o.unitPrice.toFixed(2)}</td>
          <td class="text-center">₱${o.subtotal.toFixed(2)}</td>
        </tr>`;
        total += o.subtotal;
      });
      if (tbody) tbody.innerHTML = html;
      const totalEl = document.getElementById('totalCost');
      if (totalEl) totalEl.textContent = total.toFixed(2);
    }

    function setActiveStep(n) {
      // Get all step dots and lines
      const dots = [step1Dot, step2Dot, step3Dot, step4Dot, step5Dot, step6Dot];
      const lines = document.querySelectorAll('.step-line');

      // Special case for step 6: mark all steps as completed
      if (n === 6) {
        dots.forEach((dot, i) => {
          dot.classList.remove('pending', 'active');
          dot.classList.add('completed');
          if (i < lines.length) {
            lines[i].classList.add('completed');
          }
        });
        // Make step 6 active
        dots[5].classList.remove('completed');
        dots[5].classList.add('active');
        return;
      }

      // Normal step handling for steps 1-5
      dots.forEach((dot, i) => {
        if (i + 1 < n) {
          // Previous steps are completed
          dot.classList.remove('pending', 'active');
          dot.classList.add('completed');
          if (i < lines.length) {
            lines[i].classList.add('completed');
          }
        } else if (i + 1 === n) {
          // Current step is active
          dot.classList.remove('pending', 'completed');
          dot.classList.add('active');
          if (i > 0) {
            lines[i-1].classList.add('completed');
          }
        } else {
          // Future steps are pending
          dot.classList.remove('completed', 'active');
          dot.classList.add('pending');
          if (i < lines.length) {
            lines[i].classList.remove('completed');
          }
        }
      });
    }

    function goToStep2() {
      // hide set1, show set2
      set1.classList.add('hidden');
      set2.classList.remove('hidden');
      set3.classList.add('hidden');
      stepTitle.textContent = 'Step 2: Choose Size';
      setActiveStep(2);
      // show only back button for step2
      controlsStep2.classList.remove('hidden');
      controlsStep3.classList.add('hidden');
      // clear any previous size highlight
      chooseSize(null, {silent:true});
    }

    function goToStep1() {
      set1.classList.remove('hidden');
      set2.classList.add('hidden');
      set3.classList.add('hidden');
      stepTitle.textContent = 'Step 1: Choose Gallon Type';
      setActiveStep(1);
      controlsStep2.classList.add('hidden');
      controlsStep3.classList.add('hidden');
    }

    function goToStep3() {
      set2.classList.add('hidden');
      set3.classList.remove('hidden');
      stepTitle.textContent = 'Step 3: Select Quantity';
      setActiveStep(3);
      controlsStep2.classList.add('hidden');
      controlsStep3.classList.remove('hidden');
      // init quantity and subtotal
      quantity = 1;
      qtyDisplay.textContent = quantity;
      updateSubtotal();
    }

    function goToStep4() {
      // hide previous sets
      set1.classList.add('hidden');
      set2.classList.add('hidden');
      set3.classList.add('hidden');
      set4.classList.remove('hidden');
      stepTitle.textContent = 'Step 4: Select Pick Up Date & Time of Gallon';
      setActiveStep(4);
      controlsStep2.classList.add('hidden');
      controlsStep3.classList.add('hidden');
      controlsStep4.classList.remove('hidden');
      // prefill date with today
      const today = new Date().toISOString().split('T')[0];
      pickupDate.value = today;
      // reset time selection visuals
      timeMorning.classList.remove('bg-blue-600','text-white');
      timeMorning.classList.add('bg-white','border');
      timeAfternoon.classList.remove('bg-blue-600','text-white');
      timeAfternoon.classList.add('bg-white','border');
    }

    function goToStep5() {
      // hide previous sets
      set1.classList.add('hidden');
      set2.classList.add('hidden');
      set3.classList.add('hidden');
      set4.classList.add('hidden');
      set5.classList.remove('hidden');
      stepTitle.textContent = 'Step 5: Select Delivery Date & Time of Gallon';
      setActiveStep(5);
      controlsStep2.classList.add('hidden');
      controlsStep3.classList.add('hidden');
      controlsStep4.classList.add('hidden');
      controlsStep5.classList.remove('hidden');
      // prefill delivery date
      const today = new Date().toISOString().split('T')[0];
      deliveryDate.value = today;
      // reset delivery time visuals
      [delTime1, delTime2, delTime3, delTime4].forEach(btn => {
        btn.classList.remove('bg-blue-600','text-white');
        btn.classList.add('bg-white','border');
      });
      chosenDeliveryTime = null;
      // Update delivery time slots based on current pickup date/time
      updateTimeSlots(deliveryDate, [delTime1, delTime2, delTime3, delTime4]);
    }

    // Click handlers for type selection — immediately go to step 2
    slim.addEventListener('click', () => {
      chosenType = 'slim';
      slim.classList.add('border-blue-500', 'border-4');
      round.classList.remove('border-blue-500', 'border-4');
      round.classList.add('border-gray-300', 'border-2');
      goToStep2();
    });

    round.addEventListener('click', () => {
      // Round gallon selected — hide slim and show only round with its size (18.9L),
      // then go directly to quantity.
      chosenType = 'round';

      // visually select round
      round.classList.add('border-blue-500', 'border-4');
      slim.classList.remove('border-blue-500', 'border-4');
      slim.classList.add('border-gray-300', 'border-2');

  // Hide/remove the slim card from the UI so only round remains
  slim.classList.add('hidden');
  // center the round card by converting the grid to single column and centering items
  set1.classList.remove('grid-cols-2');
  set1.classList.add('grid-cols-1','place-items-center');

      // Update round card label to include size 18.9L and ensure price is set
      const roundLabel = round.querySelector('p');
      if (roundLabel) roundLabel.textContent = 'Round Gallon - 18.9L';
      // ensure data-price exists (fallback to 30 if not set)
      if (!round.dataset.price) round.dataset.price = '30';

      // set default chosenSize/price for round
      chosenSize = '18.9L';
      chosenPrice = parseFloat(round.dataset.price) || 0;

      // go directly to quantity (step 3)
      goToStep3();
    });

    // Size selection: select and immediately go to quantity (Step 3)
    function chooseSize(size, opts = {}) {
      chosenSize = size;
      // reset visuals
      [size1, size2].forEach(el => {
        el.classList.remove('border-blue-500','border-4','bg-blue-500','text-white');
        el.classList.add('border-gray-300');
      });
      if (size === 'large') {
        size1.classList.add('border-blue-500','border-4','bg-blue-500','text-white');
        chosenPrice = parseFloat(size1.dataset.price) || 0;
      } else if (size === 'small') {
        size2.classList.add('border-blue-500','border-4','bg-blue-500','text-white');
        chosenPrice = parseFloat(size2.dataset.price) || 0;
      } else {
        chosenPrice = 0;
      }

      // If not silent, proceed to step 3
      if (!opts.silent && chosenSize) {
        goToStep3();
      }
    }

    size1.addEventListener('click', () => chooseSize('large'));
    size2.addEventListener('click', () => chooseSize('small'));

    // Quantity handlers
    function updateSubtotal() {
      const subtotal = (chosenPrice || 0) * (quantity || 0);
      subtotalDisplay.textContent = '₱' + subtotal.toFixed(2);
      qtyDisplay.textContent = quantity;
    }

    qtyMinus.addEventListener('click', () => {
      if (quantity > 1) quantity -= 1;
      updateSubtotal();
    });

    qtyPlus.addEventListener('click', () => {
      quantity += 1;
      updateSubtotal();
    });

    // Back / Next handlers
    backBtn.addEventListener('click', () => {
      goToStep1();
    });

    backBtn3.addEventListener('click', () => {
      // go back to size selection
      goToStep2();
    });

    // Step 4 back/next
    backBtn4.addEventListener('click', () => {
      // back to quantity
      // If round was selected and slim was hidden, go back to quantity (set3) directly
      set4.classList.add('hidden');
      goToStep3();
    });

    // Date validation functions
    function getTodayString() {
      const today = new Date();
      return today.toISOString().split('T')[0];
    }

    function getCurrentTime() {
      const now = new Date();
      return now.getHours() * 100 + now.getMinutes();
    }

    function isTimeSlotAvailable(timeStr, selectedDate) {
      const [hours, minutes] = timeStr.split(':').map(Number);
      const slotTime = hours * 100 + minutes;
      const today = new Date();
      const selected = new Date(selectedDate);
      
      // If selected date is today, check if time has passed
      if (selected.toDateString() === today.toDateString()) {
        return getCurrentTime() < slotTime;
      }
      
      // If selected date is in the future, slot is available
      return true;
    }

    // Initialize date inputs with min date (today)
    function initializeDateInputs() {
      const today = getTodayString();
      pickupDate.min = today;
      pickupDate.value = today;
      deliveryDate.min = today;
      deliveryDate.value = today;
    }

    // Check if delivery time should be blocked due to pickup time constraint
    function isDeliverySlotBlockedByPickup(deliveryTime, deliveryDate) {
      // Get pickup date and time
      const pickupDateValue = pickupDate.value;
      const pickupTimeValue = chosenPickupTime;
      
      // Check if delivery date is same as pickup date
      if (deliveryDate === pickupDateValue) {
        // Check if pickup time is 1:00 PM (Afternoon)
        if (pickupTimeValue === 'Afternoon - 1:00 PM') {
          // Block morning slots (9:00 AM and 11:00 AM)
          if (deliveryTime === '09:00' || deliveryTime === '11:00') {
            return true; // Block this slot
          }
        }
      }
      return false; // Slot is not blocked
    }

    // Update time slots based on selected date
    function updateTimeSlots(dateInput, timeSlots) {
      const selectedDate = dateInput.value;
      const isDeliverySlots = timeSlots.length === 4; // 4 delivery slots vs 2 pickup slots
      
      timeSlots.forEach(slot => {
        const time = slot.dataset.time;
        let shouldDisable = !isTimeSlotAvailable(time, selectedDate);
        
        // Additional check for delivery slots
        if (isDeliverySlots && !shouldDisable) {
          shouldDisable = isDeliverySlotBlockedByPickup(time, selectedDate);
        }
        
        if (shouldDisable) {
          slot.disabled = true;
          slot.classList.add('opacity-50', 'cursor-not-allowed');
          slot.classList.remove('hover:bg-blue-600');
          // If this was the selected slot, deselect it
          if (slot.classList.contains('bg-blue-600')) {
            slot.classList.remove('bg-blue-600', 'text-white');
            slot.classList.add('bg-white', 'border');
          }
        } else {
          slot.disabled = false;
          slot.classList.remove('opacity-50', 'cursor-not-allowed');
          slot.classList.add('hover:bg-blue-600');
        }
      });
    }

    // time selection buttons (robust: remove conflicting bg classes before adding selected styles)
    function selectPickupTime(selectedBtn, timeLabel) {
      if (selectedBtn.disabled) return;

      // normalize both buttons first
      [timeMorning, timeAfternoon].forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        // ensure unselected state has bg-white and border
        if (!btn.classList.contains('bg-white')) btn.classList.add('bg-white');
        if (!btn.classList.contains('border')) btn.classList.add('border');
      });

      // remove unselected utility classes from the selected button
      selectedBtn.classList.remove('bg-white','border');
      // add the selected utilities
      selectedBtn.classList.add('bg-blue-600','text-white');
      chosenPickupTime = timeLabel;
    }

    // Initialize date validation and time slot updates
    initializeDateInputs();

    // Event listeners for date changes
    pickupDate.addEventListener('change', () => {
      updateTimeSlots(pickupDate, [timeMorning, timeAfternoon]);
    });

    deliveryDate.addEventListener('change', () => {
      updateTimeSlots(deliveryDate, [delTime1, delTime2, delTime3, delTime4]);
    });

    // Initial time slot update
    updateTimeSlots(pickupDate, [timeMorning, timeAfternoon]);
    updateTimeSlots(deliveryDate, [delTime1, delTime2, delTime3, delTime4]);

    timeMorning.addEventListener('click', () => selectPickupTime(timeMorning, 'Morning - 7:30 AM'));
    timeAfternoon.addEventListener('click', () => selectPickupTime(timeAfternoon, 'Afternoon - 1:00 PM'));

    set4Next.addEventListener('click', () => {
      // validate pickupDate and pickupTime
      const date = pickupDate.value;
      const time = chosenPickupTime;
      
      if (!date) {
        Swal.fire('Please select a pickup date', '', 'warning');
        return;
      }

      // Validate that a time slot is selected
      if (!time) {
        Swal.fire('Please select a pickup time slot', '', 'warning');
        return;
      }

      // Validate that the selected time is not in the past
      const today = getTodayString();
      if (date === today && !isTimeSlotAvailable(chosenPickupTime.includes('7:30') ? '07:30' : '13:00', date)) {
        Swal.fire('The selected pickup time has already passed. Please select a future time slot.', '', 'warning');
        return;
      }

      // proceed to Step 5 (delivery date & time selection)
      goToStep5();
    });

    // Delivery time selection handlers (Step 5) — ensure bg-white is removed from selected so blue shows
    [delTime1, delTime2, delTime3, delTime4].forEach((btn, idx) => {
      btn.addEventListener('click', () => {
        if (btn.disabled) return; // Don't allow selecting disabled time slots

        // reset all to unselected state
        [delTime1, delTime2, delTime3, delTime4].forEach(b => {
          b.classList.remove('bg-blue-600','text-white');
          if (!b.classList.contains('bg-white')) b.classList.add('bg-white');
          if (!b.classList.contains('border')) b.classList.add('border');
        });

        // set selected btn
        btn.classList.remove('bg-white','border');
        btn.classList.add('bg-blue-600','text-white');
        const times = ['Morning - 9:00 AM','Morning - 11:00 AM','Afternoon - 2:00 PM','Afternoon - 4:00 PM'];
        chosenDeliveryTime = times[idx];
      });
    });

    backBtn5.addEventListener('click', () => {
      // return to step4
      set5.classList.add('hidden');
      goToStep4();
    });

    nextBtn5.addEventListener('click', () => {
      const date = deliveryDate.value;
      const time = chosenDeliveryTime;

      if (!date) {
        Swal.fire('Please select a delivery date', '', 'warning');
        return;
      }

      if (!time) {
        Swal.fire('Please select a delivery time slot', '', 'warning');
        return;
      }

      // Validate that the selected time is not in the past
      const today = getTodayString();
      if (date === today) {
        const timeMap = {
          'Morning - 9:00 AM': '09:00',
          'Morning - 11:00 AM': '11:00',
          'Afternoon - 2:00 PM': '14:00',
          'Afternoon - 4:00 PM': '16:00'
        };
        const selectedTime = timeMap[time];
        if (selectedTime && !isTimeSlotAvailable(selectedTime, date)) {
          Swal.fire('The selected delivery time has already passed. Please select a future time slot.', '', 'warning');
          return;
        }
      }

      // Go to Step 6: Review Order
      set5.classList.add('hidden');
      controlsStep5.classList.add('hidden');
      setActiveStep(6); // Mark all previous steps and lines as completed
      showStep6();
    });

    // Step 6: Review Order (with editable delivery address and order summary)
    function showStep6() {
      // Create review order UI
      let step6 = document.getElementById('set6');
      let controlsStep6 = document.getElementById('controlsStep6');
  if (!step6) {
        step6 = document.createElement('div');
        step6.id = 'set6';
        step6.className = 'mt-6 bg-white p-6 rounded-md';
        step6.innerHTML = `
          <h2 class="text-lg font-semibold mb-4">Step 6: Review Your Order</h2>
          <div class="mb-4 p-4 border rounded-lg">
            <div class="flex items-start justify-between">
              <div>
                <div class="font-medium text-blue-700 mb-1">Delivery Address</div>
                <div id="userNameDisplay" class="font-semibold">${userName}</div>
                <div id="userContactDisplay" class="text-sm text-gray-600">${userContact}</div>
              </div>
              <div>
                <button id="editAddressBtn" class="text-sm text-blue-600 hover:text-blue-800">Change</button>
              </div>
            </div>

            <div id="addressDisplay" class="mt-3 text-sm text-gray-700">
              ${userAddress.house || ''} ${userAddress.street || ''}<br>
              ${userAddress.barangay || ''}, ${userAddress.city || ''}, ${userAddress.province || ''} ${userAddress.postal || ''}
            </div>

            <div id="addressForm" class="mt-3 hidden">
              <div class="grid grid-cols-1 gap-2">
                <input id="addrHouse" placeholder="House/Unit No." class="border rounded px-2 py-2" />
                <input id="addrStreet" placeholder="Street Name" class="border rounded px-2 py-2" />
                <input id="addrBarangay" placeholder="Barangay" class="border rounded px-2 py-2" />
                <input id="addrCity" placeholder="City" class="border rounded px-2 py-2" />
                <input id="addrProvince" placeholder="Province" class="border rounded px-2 py-2" />
                <input id="addrPostal" placeholder="Postal Code" class="border rounded px-2 py-2" />
              </div>
              <div class="flex space-x-3 mt-3">
                <button id="saveAddressBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                <button id="cancelAddressBtn" class="bg-white border px-4 py-2 rounded hover:bg-blue-600 hover:text-white">Cancel</button>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <table class="w-full text-sm border rounded-lg">
              <thead>
                <tr class="bg-gray-100">
                  <th class="py-2 text-center">Gallon Type</th>
                  <th class="text-center">Size</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-center">Unit Price</th>
                  <th class="text-center">Subtotal</th>
                </tr>
              </thead>
              <tbody id="ordersTbody">
                <tr>
                  <td class="py-2">${chosenType === 'slim' ? 'Slim' : 'Round'}</td>
                  <td>${chosenSize || (chosenType === 'round' ? '18.9L' : '')}</td>
                  <td>${quantity}</td>
                  <td>₱${chosenPrice.toFixed(2)}</td>
                  <td>₱${(chosenPrice * quantity).toFixed(2)}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mb-4 text-right text-xl font-bold text-blue-600">Total Cost: ₱<span id="totalCost">${(chosenPrice * quantity).toFixed(2)}</span></div>

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <div class="text-xs text-gray-500">Pick Up Date & Time</div>
              <div class="font-semibold text-blue-700">${pickupDate.value} ${chosenPickupTime || ''}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Delivery Date & Time</div>
              <div class="font-semibold text-blue-700">${deliveryDate.value} ${chosenDeliveryTime}</div>
            </div>
          </div>

          <div class="mb-4 p-3 border rounded text-xs text-blue-700 bg-blue-50">Note: You will receive a notification when your gallon is picked up. You can track the delivery in real-time using the GPS map on the "My Orders" page.</div>
        `;
  document.querySelector('main').appendChild(step6);

  // Hook up address edit controls
        document.getElementById('editAddressBtn').addEventListener('click', () => {
          // show form and populate with current address
          document.getElementById('addressForm').classList.remove('hidden');
          document.getElementById('addressDisplay').classList.add('hidden');
          document.getElementById('addrHouse').value = userAddress.house;
          document.getElementById('addrStreet').value = userAddress.street;
          document.getElementById('addrBarangay').value = userAddress.barangay;
          document.getElementById('addrCity').value = userAddress.city;
          document.getElementById('addrProvince').value = userAddress.province;
          document.getElementById('addrPostal').value = userAddress.postal;
        });

        document.getElementById('cancelAddressBtn').addEventListener('click', () => {
          document.getElementById('addressForm').classList.add('hidden');
          document.getElementById('addressDisplay').classList.remove('hidden');
        });

  document.getElementById('saveAddressBtn').addEventListener('click', () => {
          // save values
          const h = document.getElementById('addrHouse').value.trim();
          const s = document.getElementById('addrStreet').value.trim();
          const b = document.getElementById('addrBarangay').value.trim();
          const c = document.getElementById('addrCity').value.trim();
          const p = document.getElementById('addrProvince').value.trim();
          const pc = document.getElementById('addrPostal').value.trim();

          if (!h || !s || !b || !c || !p || !pc) {
            Swal.fire('Please fill in the full delivery address', '', 'warning');
            return;
          }

          userAddress.house = h;
          userAddress.street = s;
          userAddress.barangay = b;
          userAddress.city = c;
          userAddress.province = p;
          userAddress.postal = pc;

          // update display
          document.getElementById('addressDisplay').innerHTML = `${h} ${s}<br>${b}, ${c}, ${p} ${pc}`;
          document.getElementById('addressForm').classList.add('hidden');
          document.getElementById('addressDisplay').classList.remove('hidden');
        });
        // render orders after creating UI
        renderOrdersInStep6();
      } else {
        // update values if already created
        step6.style.display = '';
        document.getElementById('userNameDisplay').textContent = userName;
        document.getElementById('userContactDisplay').textContent = userContact;
        document.getElementById('addressDisplay').innerHTML = `${userAddress.house || ''} ${userAddress.street || ''}<br>${userAddress.barangay || ''}, ${userAddress.city || ''}, ${userAddress.province || ''} ${userAddress.postal || ''}`;
        renderOrdersInStep6();
      }
      // Controls for Step 6
      if (!controlsStep6) {
        controlsStep6 = document.createElement('div');
        controlsStep6.id = 'controlsStep6';
        controlsStep6.className = 'mt-6 flex justify-between';
        controlsStep6.innerHTML = `
          <div class="flex space-x-3">
            <button id="backBtn6" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
            <button id="startOverBtn6" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Start Over</button>
          </div>
          <div>
            <button id="placeOrderBtn" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Place Order</button>
          </div>
        `;
        document.querySelector('main').appendChild(controlsStep6);
      } else {
        controlsStep6.style.display = '';
      }
      // Back button handler
      document.getElementById('backBtn6').onclick = () => {
        step6.style.display = 'none';
        controlsStep6.style.display = 'none';
        set5.classList.remove('hidden');
        controlsStep5.classList.remove('hidden');
      };
      // Start Over button handler
      document.getElementById('startOverBtn6').onclick = () => {
        location.reload();
      };
      // Place Order button handler
      document.getElementById('placeOrderBtn').onclick = () => {
        // Ensure address is filled
        const addrFilled = userAddress.house && userAddress.street && userAddress.barangay && userAddress.city && userAddress.province && userAddress.postal;
        if (!addrFilled) {
          Swal.fire('Please provide a full delivery address before placing the order', '', 'warning');
          return;
        }
        showPendingPaymentAlert();
      };
    }

    // SweetAlert2: Pending Payment
    function showPendingPaymentAlert() {
      Swal.fire({
        title: '<span style="color:#2563eb;font-weight:600;">Pending Payment</span>',
        html: `<div class="text-base mb-4">Thank you for your order! Please have your payment ready upon delivery.</div>
          <div class="text-center"><button id='viewOrdersBtn' class='bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded'>View My Orders</button></div>`,
        showConfirmButton: false,
        showCloseButton: false,
        allowOutsideClick: false,
        customClass: {
          popup: 'rounded-lg'
        },
        didRender: () => {
          const btn = document.getElementById('viewOrdersBtn');
          if (btn) {
            btn.onclick = function() {
              window.location.href = '#'; // TODO: Replace with actual My Orders page link
            };
          }
        }
      });
    }

    nextBtn3.addEventListener('click', () => {
      // Show SweetAlert2 modal offering to add more orders or proceed to checkout
      Swal.fire({
        title: 'Add More Orders?',
        text: 'Would you like to add more water refills or proceed to checkout?',
        icon: 'question',
        showDenyButton: true,
        showCancelButton: false,
        confirmButtonText: 'Add More Orders',
        denyButtonText: 'Proceed to Checkout',
        customClass: {
          popup: 'rounded-lg'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          // Save current order then allow user to add more
          saveCurrentOrder();
          // User wants to add more orders: return to Step 1 and reset selections
          // Restore Slim card (in case it was hidden) and restore original grid
          slim.classList.remove('hidden');
          set1.classList.remove('grid-cols-1','place-items-center');
          set1.classList.add('grid-cols-2');
          // Restore round label if it was modified
          const roundLabel = round.querySelector('p');
          if (roundLabel) roundLabel.textContent = 'Round Gallon';
          // reset selection state
          chosenType = null;
          chosenSize = null;
          chosenPrice = 0;
          quantity = 1;
          goToStep1();
        } else if (result.isDenied) {
          // Save current order then proceed to checkout (Step 4)
          saveCurrentOrder();
          goToStep4();
        }
      });
    });

    // initial state
    setActiveStep(1);
    controlsStep2.classList.add('hidden');
    controlsStep3.classList.add('hidden');
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const pickupDate = document.getElementById("pickupDate");
      // Set min date and value to today
      const today = new Date();
      const todayStr = today.toISOString().split('T')[0];
      pickupDate.setAttribute('min', todayStr);
      pickupDate.value = todayStr;
      // Prevent selecting past date before proceeding
      const nextBtn4 = document.getElementById('nextBtn4');
      nextBtn4.addEventListener('click', function(e) {
        if (pickupDate.value < todayStr) {
          e.preventDefault();
          alert('Please select today or a future date for pickup.');
          pickupDate.value = todayStr;
        }
      });
    });
  </script>
</body>
</html>
