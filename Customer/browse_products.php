<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Browse Products - RCH Water</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Bootstrap icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    /* small visual polish */
    .sidebar-collapsed { width: 72px !important; }
    .sidebar-collapsed .label-text { display:none; }
    .card-rounded { border-radius: 12px; }
    .btn-blue { background-color:#0b78f6; color:white; }
    .btn-blue:hover { background-color:#0967d6; }
    /* custom scrollbar for main content */
    .main-scroll::-webkit-scrollbar { width:10px; }
    .main-scroll::-webkit-scrollbar-thumb { background:#cfd7df; border-radius: 10px; }
  </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

  <div class="min-h-screen flex">
    <!-- SIDEBAR -->
    <aside id="sidebar" class="flex-shrink-0 bg-gradient-to-b from-blue-600 to-blue-700 text-white w-64 p-6 flex flex-col transition-all duration-200">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <i class="bi bi-droplet-fill text-2xl"></i>
          <div class="label-text">
            <h1 class="text-xl font-bold">RCH Water</h1>
          </div>
        </div>
        <button id="collapseBtn" class="bg-white/20 p-1 rounded-md hover:bg-white/30" title="Collapse sidebar">
          <i class="bi bi-chevron-left"></i>
        </button>
      </div>

      <nav class="flex-1 space-y-2">
        <button data-view="browse" class="nav-btn w-full flex items-center gap-3 p-3 rounded-lg hover:bg-blue-500 active:bg-blue-500">
          <i class="bi bi-bucket"></i><span class="label-text">Order Water Refill</span>
        </button>

        <button data-view="browse" id="browseNav" class="nav-btn w-full flex items-center gap-3 p-3 rounded-lg bg-blue-500">
          <i class="bi bi-shop"></i><span class="label-text">Browse Products</span>
        </button>

        <button data-view="cart" id="cartNav" class="nav-btn w-full flex items-center gap-3 p-3 rounded-lg hover:bg-blue-500">
          <i class="bi bi-cart"></i><span class="label-text">My Cart</span>
        </button>

        <button class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-blue-500">
          <i class="bi bi-box-seam"></i><span class="label-text">My Orders</span>
        </button>

        <button class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-blue-500">
          <i class="bi bi-droplet"></i><span class="label-text">My Gallons</span>
        </button>

        <button class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-blue-500">
          <i class="bi bi-receipt"></i><span class="label-text">Receipts</span>
        </button>
      </nav>

      <button id="logoutBtn" class="mt-auto flex items-center gap-2 bg-white/20 p-2 rounded-lg hover:bg-white/30">
        <i class="bi bi-box-arrow-right"></i><span class="label-text">Logout</span>
      </button>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-6 overflow-hidden">
      <!-- header -->
      <header class="flex justify-between items-center mb-6">
        <div>
          <h2 id="pageTitle" class="text-3xl font-bold text-blue-700">Browse Products</h2>
          <p id="pageSubtitle" class="text-gray-500 text-sm">Shop for gallon containers and accessories!</p>
        </div>
        <div class="flex items-center gap-3 text-blue-700 text-xl">
          <button class="p-2 rounded-md border"><i class="bi bi-bell"></i></button>
          <button class="p-2 rounded-md border"><i class="bi bi-chat-dots"></i></button>
          <button id="miniCartBtn" class="p-2 rounded-md border"><i class="bi bi-cart"></i> <span id="miniCartCount" class="ml-1 text-sm text-red-600 font-semibold">0</span></button>
          <button class="p-2 rounded-md border"><i class="bi bi-person-circle"></i></button>
        </div>
      </header>

      <!-- content wrapper (scrollable) -->
      <div id="contentArea" class="bg-white p-4 rounded-xl shadow-md main-scroll overflow-auto" style="height: calc(100vh - 120px);">

        <!-- ================== BROWSE VIEW ================== -->
        <section id="browseView">
          <div class="mb-4">
            <input id="searchInput" type="text" placeholder="Search products..." class="w-full border rounded-lg p-3" />
          </div>

          <div class="flex gap-3 mb-4">
            <button class="category-btn px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white" data-category="all">All</button>
            <button class="category-btn px-4 py-2 rounded-lg text-sm font-semibold bg-gray-200 text-gray-700 hover:bg-blue-200" data-category="gallons">Gallons</button>
            <button class="category-btn px-4 py-2 rounded-lg text-sm font-semibold bg-gray-200 text-gray-700 hover:bg-blue-200" data-category="accessories">Accessories</button>
          </div>

          <p id="errorMsg" class="hidden text-red-500 font-semibold mb-2">No products found.</p>

          <div id="productList" class="grid grid-cols-3 gap-6"></div>
        </section>

        <!-- ================== CART VIEW ================== -->
        <section id="cartView" class="hidden">
          <div class="card-rounded border p-4">
            <h3 class="text-xl font-semibold mb-3">My Cart</h3>

            <div class="overflow-auto">
              <table class="w-full text-sm">
                <thead class="text-left text-gray-600">
                  <tr>
                    <th class="py-2"><input id="selectAllCart" type="checkbox" /></th>
                    <th class="py-2">Product</th>
                    <th class="py-2">Unit Price</th>
                    <th class="py-2">Quantity</th>
                    <th class="py-2">Total Price</th>
                    <th class="py-2">Action</th>
                  </tr>
                </thead>
                <tbody id="cartTableBody" class="align-top"></tbody>
              </table>
            </div>

            <div class="flex items-center justify-between mt-6">
              <div class="flex items-center gap-4">
                <input id="cartSelectCount" type="checkbox" disabled />
                <label id="selectAllLabel" class="text-sm">Select All (0)</label>
                <button id="deleteSelectedBtn" class="text-red-500 ml-3">Delete</button>
              </div>

              <div class="flex items-center gap-4">
                <div class="text-sm">Subtotal: <span id="cartSubtotal" class="text-blue-700 font-bold text-lg">₱0.00</span></div>
                <button id="checkoutBtn" class="px-4 py-2 rounded-lg btn-blue">Check Out</button>
              </div>
            </div>
          </div>
        </section>

        <!-- ================== CHECKOUT VIEW ================== -->
        <section id="checkoutView" class="hidden">
          <div class="card-rounded border p-6">
            <h3 class="text-2xl font-semibold mb-4">Checkout</h3>

            <!-- Delivery address block -->
            <div class="p-4 border rounded-lg mb-4">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="text-blue-600 font-semibold mb-1">Delivery Address</h4>
                  <div id="deliveryDisplay">
                    <p id="deliverName" class="font-semibold">User Name</p>
                    <p id="deliverPhone" class="text-sm text-gray-600">Phone</p>
                    <p id="deliverAddress" class="text-sm text-gray-600">Address</p>
                  </div>
                </div>

                <div class="text-right">
                  <button id="changeAddressBtn" class="text-sm text-blue-600">Change</button>
                </div>
              </div>

              <div id="deliveryEdit" class="hidden mt-4 space-y-2">
                <input id="inputName" class="w-full border rounded p-2" placeholder="Full name" />
                <input id="inputPhone" class="w-full border rounded p-2" placeholder="Phone number" />
                <textarea id="inputAddress" class="w-full border rounded p-2" rows="2" placeholder="Delivery address"></textarea>
                <div class="flex gap-2">
                  <button id="saveAddressBtn" class="px-4 py-2 rounded-lg btn-blue">Save</button>
                  <button id="cancelAddressBtn" class="px-4 py-2 rounded-lg border">Cancel</button>
                </div>
              </div>
            </div>

            <!-- Products ordered -->
            <div class="p-4 border rounded-lg mb-4">
              <h4 class="text-blue-600 font-semibold mb-3">Products Ordered</h4>
              <div id="checkoutProducts" class="text-sm"></div>
            </div>

            <!-- costs -->
            <div class="p-4 border rounded-lg mb-4">
              <div class="flex justify-between mb-2"><div>Subtotal</div><div id="coSubtotal">₱0.00</div></div>
              <div class="flex justify-between mb-2"><div>Tax (8%)</div><div id="coTax">₱0.00</div></div>
              <div class="flex justify-between mb-2"><div>Delivery Fee</div><div id="coDelivery">₱40.00</div></div>
              <hr class="my-3" />
              <div class="flex justify-between text-lg font-bold"><div>Total Cost</div><div id="coTotal">₱0.00</div></div>
            </div>

            <div class="flex justify-end gap-3">
              <button id="cancelOrderBtn" class="px-4 py-2 rounded-lg border">Cancel</button>
              <button id="placeOrderBtn" class="px-4 py-2 rounded-lg btn-blue">Place Order</button>
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>

<script>
/* ---------------------------
   Products data (images in `img/` folder)
   Make sure the img filenames match what's below.
   --------------------------- */
const products = [
  { id: 'p1', name: "20L Slim Container Gallon", price: 170, stock: true, category: "gallons", img: "20L_box_type_gallon.jpg" },
  { id: 'p2', name: "10L Slim Container Gallon", price: 120, stock: true, category: "gallons", img: "10L_box_type_gallon.jpg" },
  { id: 'p3', name: "18.9L Round Container Gallon", price: 150, stock: true, category: "gallons", img: "18.9L_round_gallon.jpg" },
  { id: 'p4', name: "1L Water Bottle", price: 40, stock: false, category: "gallons", img: "1L_water_bottle.jpg" },
  { id: 'p5', name: "500mL Water Bottle", price: 25, stock: false, category: "gallons", img: "500ml_water_bottle.jpg" },

  { id: 'p6', name: "Slim Gallon Faucet - Press", price: 50, stock: true, category: "accessories", img: "faucet_press.jpg" },
  { id: 'p7', name: "Slim Gallon Faucet - Rotate", price: 60, stock: false, category: "accessories", img: "faucet_rotate.jpg" },
  { id: 'p8', name: "Slim Gallon Inside Lid/Cap", price: 20, stock: true, category: "accessories", img: "inner_cap.jpg" },
  { id: 'p9', name: "Slim Gallon Small Lid/Cap", price: 15, stock: true, category: "accessories", img: "small_cap.jpg" },
  { id: 'p10', name: "Slim Gallon Big Lid/Cap", price: 25, stock: true, category: "accessories", img: "big_cap.jpg" },
];

/* ---------------------------
   State
   --------------------------- */
let cart = JSON.parse(localStorage.getItem('rch_cart') || '[]'); // [{id, qty}]
let activeCategory = 'all';
let selectedCartIds = new Set();

/* ---------------------------
   Utilities
   --------------------------- */
const formatPHP = (n) => '₱' + Number(n).toFixed(2);

/* ---------------------------
   Render browse products
   --------------------------- */
const productListEl = document.getElementById('productList');
const errorMsgEl = document.getElementById('errorMsg');
function renderProducts(filter = 'all', search = '') {
  productListEl.innerHTML = '';
  const s = search.trim().toLowerCase();

  const filtered = products.filter(p =>
    (filter === 'all' || p.category === filter) &&
    p.name.toLowerCase().includes(s)
  );

  if (filtered.length === 0) {
    errorMsgEl.classList.remove('hidden');
  } else {
    errorMsgEl.classList.add('hidden');
  }

  filtered.forEach(p => {
    const card = document.createElement('div');
    card.className = 'border rounded-xl p-4 shadow-sm bg-white flex flex-col';

    // image
    const img = document.createElement('img');
    img.src = `img/${p.img}`;
    img.alt = p.name;
    img.className = 'h-40 w-full object-contain mb-3';
    img.onerror = () => { img.src = 'https://via.placeholder.com/240x160?text=No+Image'; };

    // name, price, stock
    const name = document.createElement('p');
    name.className = 'font-semibold text-gray-800 text-sm mb-1';
    name.textContent = p.name;

    const price = document.createElement('p');
    price.className = 'text-blue-700 font-bold text-lg mb-1';
    price.textContent = formatPHP(p.price);

    const stock = document.createElement('span');
    stock.className = (p.stock ? 'text-green-600' : 'text-red-500') + ' text-sm font-semibold';
    stock.textContent = p.stock ? 'In Stock' : 'Unavailable';

    // quantity controls
    const qtyWrap = document.createElement('div');
    qtyWrap.className = 'mt-3 flex items-center gap-2';
    const minus = document.createElement('button');
    minus.className = 'px-3 py-1 border rounded';
    minus.textContent = '-';
    const qtyInput = document.createElement('input');
    qtyInput.type = 'number';
    qtyInput.min = '1';
    qtyInput.value = '1';
    qtyInput.className = 'w-16 text-center border rounded p-1';
    const plus = document.createElement('button');
    plus.className = 'px-3 py-1 border rounded';
    plus.textContent = '+';

    minus.onclick = () => { if (Number(qtyInput.value) > 1) qtyInput.value = Number(qtyInput.value) - 1; };
    plus.onclick = () => { qtyInput.value = Number(qtyInput.value) + 1; };

    qtyWrap.appendChild(document.createTextNode('Quantity '));
    qtyWrap.appendChild(minus);
    qtyWrap.appendChild(qtyInput);
    qtyWrap.appendChild(plus);

    // actions
    const actions = document.createElement('div');
    actions.className = 'flex items-center gap-2 mt-3';
    const addBtn = document.createElement('button');
    addBtn.className = 'px-3 py-1 border rounded-lg hover:bg-blue-200 flex items-center gap-2';
    addBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add';
    const buyBtn = document.createElement('button');
    buyBtn.className = 'px-3 py-1 bg-blue-600 text-white rounded-lg';
    buyBtn.textContent = 'Buy Now';

    // add to cart
    addBtn.onclick = () => {
      if (!p.stock) {
        Swal.fire({ icon: 'error', title: 'Unavailable', text: 'This product is currently unavailable.' });
        return;
      }
      const qty = Math.max(1, parseInt(qtyInput.value || 1));
      addToCart(p.id, qty);
      Swal.fire({ icon: 'success', title: 'Added', text: `${p.name} added to cart.`, timer: 1400, showConfirmButton: false });
      updateMiniCart();
    };

    // buy now -> add 1 (or qty) then open cart and select it
    buyBtn.onclick = () => {
      if (!p.stock) {
        Swal.fire({ icon: 'error', title: 'Unavailable', text: 'This product is currently unavailable.' });
        return;
      }
      const qty = Math.max(1, parseInt(qtyInput.value || 1));
      addToCart(p.id, qty);
      updateMiniCart();
      // navigate to cart
      showView('cart');
      // select all to show it
      setTimeout(() => {
        // select that product only
        selectedCartIds.clear();
        selectedCartIds.add(p.id);
        renderCart();
      }, 50);
    };

    actions.appendChild(addBtn);
    actions.appendChild(buyBtn);

    // assemble
    card.appendChild(img);
    card.appendChild(name);
    card.appendChild(price);
    card.appendChild(stock);
    card.appendChild(qtyWrap);
    card.appendChild(actions);
    productListEl.appendChild(card);
  });
}

/* ---------------------------
   Cart logic
   --------------------------- */
function saveCart() {
  localStorage.setItem('rch_cart', JSON.stringify(cart));
}

function findCartItem(productId) {
  return cart.find(i => i.id === productId);
}

function addToCart(productId, qty = 1) {
  const existing = findCartItem(productId);
  if (existing) {
    existing.qty = existing.qty + qty;
  } else {
    cart.push({ id: productId, qty: qty });
  }
  saveCart();
}

/* compute totals only for selected items (if none selected, compute all) */
function cartItemsDetailed() {
  return cart.map(ci => {
    const p = products.find(x => x.id === ci.id);
    return { ...p, qty: ci.qty, lineTotal: p.price * ci.qty };
  });
}

function computeSubtotal() {
  // subtotal for selected items; if none selected use all
  let items = cartItemsDetailed();
  if (selectedCartIds.size > 0) {
    items = items.filter(it => selectedCartIds.has(it.id));
  }
  return items.reduce((s, it) => s + it.lineTotal, 0);
}

/* render cart table */
const cartTableBody = document.getElementById('cartTableBody');
const cartSubtotalEl = document.getElementById('cartSubtotal');
const selectAllCartCheckbox = document.getElementById('selectAllCart');
const selectAllLabel = document.getElementById('selectAllLabel');
function renderCart() {
  cartTableBody.innerHTML = '';
  const detailed = cartItemsDetailed();

  if (detailed.length === 0) {
    cartTableBody.innerHTML = `<tr><td colspan="6" class="py-6 text-center text-gray-500">Your cart is empty.</td></tr>`;
  } else {
    detailed.forEach(it => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="py-3 align-top"><input class="cart-check" data-id="${it.id}" type="checkbox"${selectedCartIds.has(it.id) ? ' checked' : ''}></td>
        <td class="py-3 align-top flex items-center gap-3">
          <img src="img/${it.img}" class="w-16 h-12 object-contain" onerror="this.src='https://via.placeholder.com/80x60?text=No+Image'"/>
          <div><div class="font-semibold">${it.name}</div></div>
        </td>
        <td class="py-3 align-top">${formatPHP(it.price)}</td>
        <td class="py-3 align-top">
          <div class="flex items-center gap-2">
            <button class="qty-minus px-2 py-1 border rounded" data-id="${it.id}">-</button>
            <input class="qty-input w-16 text-center border rounded p-1" data-id="${it.id}" value="${it.qty}" type="number" min="1"/>
            <button class="qty-plus px-2 py-1 border rounded" data-id="${it.id}">+</button>
          </div>
        </td>
        <td class="py-3 align-top" id="line-${it.id}">${formatPHP(it.lineTotal)}</td>
        <td class="py-3 align-top"><button class="delete-item text-red-500" data-id="${it.id}"><i class="bi bi-trash"></i></button></td>
      `;
      cartTableBody.appendChild(tr);
    });
  }

  // wire up handlers
  document.querySelectorAll('.cart-check').forEach(cb => {
    cb.onchange = (e) => {
      const id = e.target.dataset.id;
      if (e.target.checked) selectedCartIds.add(id); else selectedCartIds.delete(id);
      updateSelectAllUI();
      updateSubtotalUI();
    };
  });

  document.querySelectorAll('.qty-minus').forEach(btn => btn.onclick = (e) => {
    const id = e.target.dataset.id;
    const p = findCartItem(id);
    if (!p) return;
    if (p.qty > 1) p.qty--, saveCart();
    renderCart();
    updateMiniCart();
  });

  document.querySelectorAll('.qty-plus').forEach(btn => btn.onclick = (e) => {
    const id = e.target.dataset.id;
    const p = findCartItem(id);
    if (!p) return;
    p.qty++, saveCart();
    renderCart();
    updateMiniCart();
  });

  document.querySelectorAll('.qty-input').forEach(inp => {
    inp.onchange = (e) => {
      const id = e.target.dataset.id;
      const val = Math.max(1, parseInt(e.target.value || 1));
      const p = findCartItem(id);
      if (!p) return;
      p.qty = val;
      saveCart();
      renderCart();
      updateMiniCart();
    };
  });

  document.querySelectorAll('.delete-item').forEach(btn => btn.onclick = (e) => {
    const id = e.target.dataset.id;
    Swal.fire({
      title: 'Delete item?',
      text: 'This will remove the product from your cart.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete'
    }).then(res => {
      if (res.isConfirmed) {
        cart = cart.filter(ci => ci.id !== id);
        selectedCartIds.delete(id);
        saveCart();
        renderCart();
        updateMiniCart();
      }
    });
  });

  updateSelectAllUI();
  updateSubtotalUI();
}

/* Update select all UI and label count */
function updateSelectAllUI() {
  const total = cart.length;
  selectAllLabel.textContent = `Select All (${total})`;
  const selectedCount = selectedCartIds.size;
  document.getElementById('cartSelectCount').checked = (selectedCount === total && total>0);
  selectAllCartCheckbox.checked = (selectedCount === total && total>0);
}

/* delete selected */
document.getElementById('deleteSelectedBtn').onclick = () => {
  if (selectedCartIds.size === 0) return;
  Swal.fire({
    title: 'Delete selected items?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete'
  }).then(res => {
    if (res.isConfirmed) {
      cart = cart.filter(ci => !selectedCartIds.has(ci.id));
      selectedCartIds.clear();
      saveCart();
      renderCart();
      updateMiniCart();
    }
  });
};

/* select all toggle */
selectAllCartCheckbox.onchange = (e) => {
  if (e.target.checked) {
    cart.forEach(ci => selectedCartIds.add(ci.id));
  } else {
    selectedCartIds.clear();
  }
  renderCart();
};

/* update subtotal UI display */
function updateSubtotalUI() {
  const sub = computeSubtotal();
  cartSubtotalEl.textContent = formatPHP(sub);
}

/* Mini cart count and update */
function updateMiniCart() {
  const count = cart.reduce((s, it) => s + it.qty, 0);
  document.getElementById('miniCartCount').textContent = count;
  saveCart();
  renderCart();
}

/* ---------------------------
   Navigation / Views
   --------------------------- */
function showView(view) {
  // hide all
  document.getElementById('browseView').classList.add('hidden');
  document.getElementById('cartView').classList.add('hidden');
  document.getElementById('checkoutView').classList.add('hidden');

  // set titles
  const title = document.getElementById('pageTitle');
  const subtitle = document.getElementById('pageSubtitle');

  if (view === 'browse') {
    document.getElementById('browseView').classList.remove('hidden');
    title.textContent = 'Browse Products';
    subtitle.textContent = 'Shop for gallon containers and accessories!';
  } else if (view === 'cart') {
    document.getElementById('cartView').classList.remove('hidden');
    title.textContent = 'My Cart';
    subtitle.textContent = 'Ready to checkout? Review your cart below.';
  } else if (view === 'checkout') {
    document.getElementById('checkoutView').classList.remove('hidden');
    title.textContent = 'Checkout';
    subtitle.textContent = "You're just one step away—review your order and confirm your delivery!";
    populateCheckout();
  }
}

document.querySelectorAll('[data-view]').forEach(btn => {
  btn.onclick = () => showView(btn.getAttribute('data-view'));
});
document.getElementById('cartNav').onclick = () => showView('cart');
document.getElementById('browseNav').onclick = () => showView('browse');
document.getElementById('miniCartBtn').onclick = () => showView('cart');

/* ---------------------------
   Sidebar collapse
   --------------------------- */
const sidebar = document.getElementById('sidebar');
const collapseBtn = document.getElementById('collapseBtn');
let collapsed = false;
collapseBtn.onclick = () => {
  collapsed = !collapsed;
  if (collapsed) {
    sidebar.classList.add('sidebar-collapsed');
    collapseBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
  } else {
    sidebar.classList.remove('sidebar-collapsed');
    collapseBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
  }
};

/* ---------------------------
   Category buttons & search
   --------------------------- */
document.querySelectorAll('.category-btn').forEach(b => {
  b.onclick = () => {
    document.querySelectorAll('.category-btn').forEach(x => x.classList.remove('bg-blue-600','text-white'));
    b.classList.add('bg-blue-600','text-white');
    activeCategory = b.dataset.category;
    renderProducts(activeCategory, document.getElementById('searchInput').value);
  };
});

// search
document.getElementById('searchInput').addEventListener('input', (e) => {
  renderProducts(activeCategory, e.target.value);
});

/* ---------------------------
   Checkout flow
   --------------------------- */
document.getElementById('checkoutBtn').onclick = () => {
  // if no items, warn
  if (cart.length === 0) {
    Swal.fire({ icon: 'info', title: 'Cart empty', text: 'Add some products before checking out.' });
    return;
  }
  // ensure there are selected items (if none selected select all)
  if (selectedCartIds.size === 0) cart.forEach(ci => selectedCartIds.add(ci.id));
  showView('checkout');
};

/* populate checkout details */
const deliveryDefaults = {
  name: 'Christine May Padua',
  phone: '09161891595',
  address: 'Camella Homes, Block 26 Lot 33 Alexia Street, Phase 1, Tibig, Lipa City, Batangas 4217'
};

function populateCheckout() {
  // display delivery
  document.getElementById('deliverName').textContent = deliveryDefaults.name;
  document.getElementById('deliverPhone').textContent = deliveryDefaults.phone;
  document.getElementById('deliverAddress').textContent = deliveryDefaults.address;
  document.getElementById('inputName').value = deliveryDefaults.name;
  document.getElementById('inputPhone').value = deliveryDefaults.phone;
  document.getElementById('inputAddress').value = deliveryDefaults.address;

  // products ordered (only selected)
  const items = cartItemsDetailed().filter(it => selectedCartIds.has(it.id));
  const container = document.getElementById('checkoutProducts');
  container.innerHTML = '';
  if (items.length === 0) container.innerHTML = '<div class="text-gray-500">No items selected.</div>';
  items.forEach(it => {
    const row = document.createElement('div');
    row.className = 'flex justify-between py-2 border-b';
    row.innerHTML = `<div>${it.name} <div class="text-xs text-gray-500">x ${it.qty}</div></div><div>${formatPHP(it.lineTotal)}</div>`;
    container.appendChild(row);
  });

  // costs
  const subtotal = items.reduce((s, it) => s + it.lineTotal, 0);
  const tax = subtotal * 0.08; // 8%
  const delivery = 40; // fixed
  const total = subtotal + tax + delivery;
  document.getElementById('coSubtotal').textContent = formatPHP(subtotal);
  document.getElementById('coTax').textContent = formatPHP(tax);
  document.getElementById('coDelivery').textContent = formatPHP(delivery);
  document.getElementById('coTotal').textContent = formatPHP(total);
  document.getElementById('cartSubtotal').textContent = formatPHP(computeSubtotal());
}

/* Toggle address edit */
document.getElementById('changeAddressBtn').onclick = () => {
  document.getElementById('deliveryEdit').classList.remove('hidden');
};
document.getElementById('cancelAddressBtn').onclick = () => {
  document.getElementById('deliveryEdit').classList.add('hidden');
};
document.getElementById('saveAddressBtn').onclick = () => {
  const n = document.getElementById('inputName').value.trim();
  const ph = document.getElementById('inputPhone').value.trim();
  const a = document.getElementById('inputAddress').value.trim();
  if (!n || !ph || !a) {
    Swal.fire({ icon: 'warning', text: 'Please fill name, phone, and address.' });
    return;
  }
  deliveryDefaults.name = n;
  deliveryDefaults.phone = ph;
  deliveryDefaults.address = a;
  document.getElementById('deliveryEdit').classList.add('hidden');
  populateCheckout();
};

/* Cancel order */
document.getElementById('cancelOrderBtn').onclick = () => {
  Swal.fire({
    title: 'Cancel Order?',
    text: 'Do you want to cancel this order and return to cart?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel'
  }).then(res => {
    if (res.isConfirmed) {
      // success alert
      Swal.fire({ icon: 'success', title: 'Canceled', text: 'Your order has been canceled.' });
      showView('cart');
    }
  });
};

/* Place order */
document.getElementById('placeOrderBtn').onclick = () => {
  Swal.fire({
    title: '<span style="color:#0b78f6; font-weight:700;">Pending Payment</span>',
    html: `
      <p class="text-gray-700 mb-3">Thank you for your order!<br>
      Please have your payment ready upon delivery.</p>

      <a href="my_orders.php"
         class="px-4 py-2 rounded-lg"
         style="background:#0b78f6; color:white; display:inline-block; margin-top:8px;">
         View My Order
      </a>
    `,
    icon: 'success',
    showConfirmButton: false,
    allowOutsideClick: false,
  });

  // Optional: clear cart after placing order
  cart = [];
  selectedCartIds.clear();
  saveCart();
  renderCart();
  updateMiniCart();

  // Navigate back to browse view if needed
  showView('browse');
};

/* ---------------------------
   Init
   --------------------------- */
(function init() {
  // initial render
  renderProducts('all', '');
  renderCart();
  updateMiniCart();
  // set active category button (All)
  document.querySelectorAll('.category-btn')[0].classList.add('bg-blue-600','text-white');
})();
</script>
</body>
</html>
