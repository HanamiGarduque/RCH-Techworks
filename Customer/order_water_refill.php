<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Order Water Refill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Step indicator styling */
        .step-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            gap: 10px;
        }

        .step-dot {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            transition: all .3s;
        }

        .step-line {
            flex-grow: 1;
            height: 6px;
            background-color: #E5E7EB;
            border-radius: 4px;
            transition: background-color .3s;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar (kept as before) -->
        <script src="includes/sidebar.js"></script>

        <!-- Main Content -->
        <main class="flex-1 ml-20 bg-gray-50 rounded-xl shadow overflow-y-auto">
            <div class="main-heade  r">
                <?php
                $pageTitle = "Order Water Refill";
                $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
                include 'includes/header.php';
                ?>
            </div>

            <!-- Steps card -->
            <div class="m-3 bg-white p-6 border rounded-xl">
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

                <!-- Step containers -->
                <div id="set1" class="grid grid-cols-2 gap-6">
                    <div id="slimCard" class="border-2 border-gray-300 p-4 rounded-xl cursor-pointer text-center hover:bg-blue-500 hover:text-white transition">
                        <img src="img/20L_box_type_gallon.jpg" class="mx-auto w-40" />
                        <p class="font-medium mt-2">Slim Gallon</p>
                    </div>

                    <div id="roundCard" data-price="30" class="border-2 border-gray-300 p-4 rounded-xl cursor-pointer text-center hover:bg-blue-500 hover:text-white transition">
                        <img src="img/18.9L_round_gallon.jpg" class="mx-auto w-40" />
                        <p class="font-medium mt-2">Round Gallon</p>
                    </div>
                </div>

                <div id="set2" class="grid grid-cols-2 gap-6 hidden">
                    <div id="sizeCard1" data-size="large" data-price="30"
                        class="size-card border-2 border-gray-300 p-6 rounded-xl cursor-pointer text-center transition hover:bg-blue-600 hover:text-white">
                        <div class="p-6 bg-white rounded-md">
                            <img src="img/20L_box_type_gallon.jpg" class="mx-auto w-36" />
                        </div>
                        <p class="font-medium mt-4">Slim Gallon - 20L</p>
                        <p class="font-large mt-4">₱30.00</p>
                    </div>

                    <div id="sizeCard2" data-size="small" data-price="20"
                        class="size-card border-2 border-gray-300 p-6 rounded-xl cursor-pointer text-center transition hover:bg-blue-600 hover:text-white">
                        <div class="p-6 bg-white rounded-md">
                            <img src="img/10L_box_type_gallon.jpg" class="mx-auto w-36" />
                        </div>
                        <p class="font-medium mt-4">Slim Gallon - 10L</p>
                        <p class="font-large mt-4">₱20.00</p>
                    </div>
                </div>

                <div id="set3" class="hidden">
                    <div class="flex items-center justify-center space-x-6 py-8">
                        <button id="qtyMinus" type="button" class="w-16 h-16 bg-white border rounded-lg text-2xl">-</button>
                        <div id="qtyDisplay" class="text-4xl font-bold text-blue-600">1</div>
                        <button id="qtyPlus" type="button" class="w-16 h-16 bg-white border rounded-lg text-2xl">+</button>
                    </div>

                    <div class="text-center mt-6">
                        <div class="text-gray-500">Subtotal</div>
                        <div id="subtotalDisplay" class="text-3xl font-bold text-blue-600 mt-2">₱30.00</div>
                    </div>
                </div>

                <div id="set4" class="hidden mt-6 bg-white p-4 rounded-md">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Pick Up Date</label>
                        <input id="pickupDate" type="date" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Pick Up Time</label>
                        <div class="flex space-x-4">
                            <button id="timeMorning" type="button"
                                class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="07:30">Morning - 7:30 AM</button>
                            <button id="timeAfternoon" type="button"
                                class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="13:00">Afternoon - 1:00 PM</button>
                        </div>
                    </div>
                </div>

                <div id="set5" class="hidden mt-6 bg-white p-4 rounded-md">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Delivery Date</label>
                        <input id="deliveryDate" type="date" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gallon Delivery Time</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button id="delTime1" type="button"
                                class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="09:00">Morning - 9:00 AM</button>
                            <button id="delTime2" type="button"
                                class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="11:00">Morning - 11:00 AM</button>
                            <button id="delTime3" type="button"
                                class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="14:00">Afternoon - 2:00 PM</button>
                            <button id="delTime4" type="button"
                                class="px-6 py-3 bg-white border rounded hover:bg-blue-600 transition-colors duration-200" data-time="16:00">Afternoon - 4:00 PM</button>
                        </div>
                    </div>
                </div>

                <!-- Step 6 (hidden by default) -->
                <div id="set6" class="hidden mt-6 bg-white p-6 rounded-md">
                    <div class="mb-4 p-4 border rounded-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-medium text-blue-700 mb-1">Delivery Address</div>
                                <div id="userNameDisplay" class="font-semibold"></div>
                                <div id="userContactDisplay" class="text-sm text-gray-600"></div>
                            </div>
                            <div>
                                <button id="editAddressBtn" class="text-sm text-blue-600 hover:text-blue-800">Change</button>
                            </div>
                        </div>

                        <div id="addressDisplay" class="mt-3 text-sm text-gray-700"></div>

                        <div id="addressFormContainer" class="mt-3 hidden flex gap-4">
                            <div id="addressForm" class="flex-1 ">
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
                            <div id="mapContainer" class="flex-1 border rounded-lg bg-blue-100" style="height: 300px;">
                                <!-- The map will be rendered here -->
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
                            <tbody id="ordersTbody"></tbody>
                        </table>
                    </div>

                    <div class="mb-4 text-right text-xl font-bold text-blue-600">Total Cost: ₱<span id="totalCost">0.00</span></div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="text-xs text-gray-500">Pick Up Date & Time</div>
                            <div id="pickupSummary" class="font-semibold text-blue-700"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Delivery Date & Time</div>
                            <div id="deliverySummary" class="font-semibold text-blue-700"></div>
                        </div>
                    </div>

                    <div class="mb-4 p-3 border rounded text-xs text-blue-700 bg-blue-50">Note: You will receive a notification when your gallon is picked up. You can track the delivery in real-time using the GPS map on the "My Orders" page.</div>
                </div>
            </div>

            <!-- SINGLE Controls container (we render exactly what is needed) -->
            <div id="controls" class="mt-6 m-3"></div>
        </main>
    </div>

    <script>
        // ---------- Elements ----------
        const set1 = document.getElementById('set1');
        const set2 = document.getElementById('set2');
        const set3 = document.getElementById('set3');
        const set4 = document.getElementById('set4');
        const set5 = document.getElementById('set5');
        const set6 = document.getElementById('set6');

        const stepTitle = document.getElementById('stepTitle');
        const step1Dot = document.getElementById('step1Dot');
        const step2Dot = document.getElementById('step2Dot');
        const step3Dot = document.getElementById('step3Dot');
        const step4Dot = document.getElementById('step4Dot');
        const step5Dot = document.getElementById('step5Dot');
        const step6Dot = document.getElementById('step6Dot');

        const slim = document.getElementById('slimCard');
        const round = document.getElementById('roundCard');
        const size1 = document.getElementById('sizeCard1');
        const size2 = document.getElementById('sizeCard2');

        const qtyMinus = document.getElementById('qtyMinus');
        const qtyPlus = document.getElementById('qtyPlus');
        const qtyDisplay = document.getElementById('qtyDisplay');
        const subtotalDisplay = document.getElementById('subtotalDisplay');

        const pickupDate = document.getElementById('pickupDate');
        const timeMorning = document.getElementById('timeMorning');
        const timeAfternoon = document.getElementById('timeAfternoon');

        const deliveryDate = document.getElementById('deliveryDate');
        const delTime1 = document.getElementById('delTime1');
        const delTime2 = document.getElementById('delTime2');
        const delTime3 = document.getElementById('delTime3');
        const delTime4 = document.getElementById('delTime4');

        const controlsContainer = document.getElementById('controls');

        // step content collection for easy hide/show
        const stepContent = {
            1: set1,
            2: set2,
            3: set3,
            4: set4,
            5: set5,
            6: set6
        };

        // ---------- State (preserved across steps) ----------
        const state = {
            userId: 4, // TEMPORARY: hardcoded user ID for testing 
            chosenType: null, // 'slim' | 'round'
            chosenSize: null, // 'large' | 'small' | '18.9L'
            chosenPrice: 0,
            quantity: 1,
            chosenPickupTime: null,
            chosenDeliveryTime: null,
            hasOwnGallon: true, // default value

            userName: '',
            userContact: '',
            userAddress: {
                house: '',
                street: '',
                barangay: '',
                city: '',
                province: '',
                postal: ''
            },
            orders: [] // saved orders if user adds multiple
        };

        fetch('../Order/get_user.php')
            .then(res => res.json())
            .then(user => {
                const addrParts = user.address.split(',').map(part => part.trim());

                // update existing state
                state.userName = user.name;
                state.userContact = user.phone_number;
                state.userAddress = {
                    house: addrParts[0] || '',
                    street: addrParts[1] || '',
                    barangay: addrParts[2] || '',
                    city: addrParts[3] || '',
                    province: addrParts[4] || '',
                    postal: addrParts[5] || ''
                };

                console.log(state);
            });



        // ---------- Utility functions ----------
        function hideAllSteps() {
            Object.values(stepContent).forEach(el => el.classList.add('hidden'));
        }

        function setActiveStep(n) {
            const dots = [step1Dot, step2Dot, step3Dot, step4Dot, step5Dot, step6Dot];
            const lines = document.querySelectorAll('.step-line');

            if (n === 6) {
                dots.forEach((d, i) => {
                    d.classList.remove('pending', 'active');
                    d.classList.add('completed');
                    if (i < lines.length) lines[i].classList.add('completed');
                });
                dots[5].classList.remove('completed');
                dots[5].classList.add('active');
                return;
            }

            dots.forEach((d, i) => {
                if (i + 1 < n) {
                    d.classList.remove('pending', 'active');
                    d.classList.add('completed');
                    if (i < lines.length) lines[i].classList.add('completed');
                } else if (i + 1 === n) {
                    d.classList.remove('pending', 'completed');
                    d.classList.add('active');
                    if (i > 0) lines[i - 1].classList.add('completed');
                } else {
                    d.classList.remove('completed', 'active');
                    d.classList.add('pending');
                    if (i < lines.length) lines[i].classList.remove('completed');
                }
            });
        }

        function getTodayString() {
            return new Date().toISOString().split('T')[0];
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
            if (selected.toDateString() === today.toDateString()) {
                return getCurrentTime() < slotTime;
            }
            return true;
        }

        function isDeliverySlotBlockedByPickup(deliveryTime, deliveryDate) {
            const pickupDateValue = pickupDate.value;
            const pickupTimeValue = state.chosenPickupTime;
            if (deliveryDate === pickupDateValue) {
                if (pickupTimeValue === 'Afternoon - 1:00 PM') {
                    if (deliveryTime === '09:00' || deliveryTime === '11:00') return true;
                }
            }
            return false;
        }

        function enforceDeliveryMinDate() {
            if (!pickupDate.value) return;

            // Force delivery date to be >= pickup date
            deliveryDate.min = pickupDate.value;

            // If user previously selected an invalid delivery date → reset it
            if (deliveryDate.value < pickupDate.value) {
                deliveryDate.value = pickupDate.value;
                state.chosenDeliveryTime = null; // reset time also
            }

            // Recalculate available time slots
            updateTimeSlots(deliveryDate, [delTime1, delTime2, delTime3, delTime4]);
        }


        function updateTimeSlots(dateInput, timeButtons) {
            const selectedDate = dateInput.value;
            const isDelivery = (timeButtons.length === 4);
            timeButtons.forEach(btn => {
                const t = btn.dataset.time;
                let disable = !isTimeSlotAvailable(t, selectedDate);
                if (isDelivery && !disable) disable = isDeliverySlotBlockedByPickup(t, selectedDate);
                if (disable) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btn.classList.remove('hover:bg-blue-600');
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-white', 'border');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.classList.add('hover:bg-blue-600');
                }
            });
        }

        // ---------- Controls renderer (single place to set controls) ----------
        function renderControls(step) {
            let html = '';

            if (step === 1) {
                html = `<div class="flex justify-end space-x-3">
          <div></div>
          <div></div>
        </div>`;
            } else if (step === 2) {
                html = `<div class="flex justify-start">
          <button id="backBtn" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
        </div>`;
            } else if (step === 3) {
                html = `<div class="flex justify-between">
          <button id="backBtn3" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
          <button id="nextBtn3" class="w-40 bg-blue-600 text-white py-2 rounded-lg">Next</button>
        </div>`;
            } else if (step === 4) {
                html = `<div class="flex justify-between">
          <button id="backBtn4" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
          <button id="nextBtn4" class="w-40 bg-blue-600 text-white py-2 rounded-lg">Next</button>
        </div>`;
            } else if (step === 5) {
                html = `<div class="flex justify-between">
          <button id="backBtn5" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
          <button id="nextBtn5" class="w-40 bg-blue-600 text-white py-2 rounded-lg">Next</button>
        </div>`;
            } else if (step === 6) {
                html = `<div class="flex justify-between space-x-3">
        <div class="flex space-x-3">
            <button id="backBtn6" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Back</button>
            <button id="startOverBtn6" class="w-40 bg-white border py-2 rounded-lg hover:bg-blue-600 hover:text-white">Start Over</button>
            <button id="addMoreOrdersBtn" class="w-40 bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600">Add More Orders</button>
        </div>
        <div class="flex space-x-3">
            <button id="placeOrderBtn" class="w-40 bg-green-600 text-white py-2 rounded-lg">Place Order</button>
        </div>
     </div>`;
            }


            controlsContainer.innerHTML = html;


            // Step 2 Back (always goes to step 1)
            if (step === 2) {
                const backBtn = document.getElementById('backBtn');
                if (backBtn) {
                    backBtn.addEventListener("click", () => {
                        goToStep(1);
                    });
                }
            }

            // Step 3 Back (conditional jump depending on selected gallon)
            if (step === 3) {
                const backBtn3 = document.getElementById('backBtn3');
                const nextBtn3 = document.getElementById('nextBtn3');

                if (backBtn3) {
                    backBtn3.addEventListener("click", () => {
                        //  NEW LOGIC INSERTED HERE
                        if (state.chosenType === "round") {
                            goToStep(1); // skip slim step
                        } else if (state.chosenType === "slim") {
                            goToStep(2); // go to slim gallon step
                        } else {
                            goToStep(1); // fallback
                        }
                    });
                }

                if (nextBtn3) nextBtn3.addEventListener('click', onNextFromStep3);
            }

            // Step 4 Back (general previous-step logic)
            if (step === 4) {
                const backBtn4 = document.getElementById('backBtn4');
                const nextBtn4 = document.getElementById('nextBtn4');

                if (backBtn4) {
                    backBtn4.addEventListener("click", () => {
                        goToStep(3);
                    });
                }
                if (nextBtn4) nextBtn4.addEventListener("click", onNextFromStep4);
            }

            // Step 5 Back
            if (step === 5) {
                const backBtn5 = document.getElementById('backBtn5');
                const nextBtn5 = document.getElementById('nextBtn5');

                if (backBtn5) {
                    backBtn5.addEventListener("click", () => {
                        goToStep(4);
                    });
                }
                if (nextBtn5) nextBtn5.addEventListener("click", onNextFromStep5);
            }

            // Step 6 Back
            if (step === 6) {
                const backBtn6 = document.getElementById('backBtn6');
                const startOverBtn6 = document.getElementById('startOverBtn6');
                const placeOrderBtn = document.getElementById('placeOrderBtn');
                const addMoreOrdersBtn = document.getElementById('addMoreOrdersBtn');
                const addToCartBtn = document.getElementById('addToCartBtn');

                if (backBtn6) backBtn6.addEventListener("click", () => goToStep(5));
                if (startOverBtn6) startOverBtn6.addEventListener("click", () => location.reload());
                if (placeOrderBtn) placeOrderBtn.addEventListener("click", onPlaceOrder);

                // Add More Orders
                if (addMoreOrdersBtn) addMoreOrdersBtn.addEventListener("click", () => {
                    saveCurrentOrder(true); // Save current selection to orders
                    // Reset current selection for new order
                    state.chosenType = null;
                    state.chosenSize = null;
                    state.chosenPrice = 0;
                    state.quantity = 1;
                    resetStep1UI();
                    goToStep(1);
                });

                // Add to Cart (without going to Step 1)
                if (addToCartBtn) addToCartBtn.addEventListener("click", () => {
                    saveCurrentOrder(true); // Add current selection to orders
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart',
                        text: 'Current selection has been added to your cart.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });


            }

        }


        // ---------- Step navigation & restore logic ----------
        function goToStep(n) {
            // Always hide old content (prevents piling)
            hideAllSteps();

            // Render proper navigation buttons
            renderControls(n);

            // Update the step title and progress indicator
            stepTitle.textContent = getStepTitle(n);
            setActiveStep(n);

            // Show the step container
            stepContent[n].classList.remove('hidden');

            // restore previously selected values / visuals for this step
            restoreStep(n);

            // ⭐ FIX: Reset step 1 UI when going back from later steps
            if (n === 1) {
                resetStep1UI(); // you must define this (see below)
            }

            // Step-specific logic
            if (n === 4) {
                const today = getTodayString();
                pickupDate.min = today;
                if (!pickupDate.value) pickupDate.value = today;

                updateTimeSlots(pickupDate, [timeMorning, timeAfternoon]);
                restorePickupVisuals();
            }

            if (n === 5) {
                const today = getTodayString();
                deliveryDate.min = today;
                if (!deliveryDate.value) deliveryDate.value = today;

                updateTimeSlots(deliveryDate, [delTime1, delTime2, delTime3, delTime4]);
                restoreDeliveryVisuals();
            }

            if (n === 6) {
                renderOrdersInStep6();
            }

            //  Apply conditional navigation shortcuts
            // example: if round gallon → skip step 2
            if (n === 2 && state.chosenType === "round") {
                // step 2 should never appear for round gallons
                return goToStep(3);
            }
        }


        function getStepTitle(n) {
            switch (n) {
                case 1:
                    return 'Step 1: Choose Gallon Type';
                case 2:
                    return 'Step 2: Choose Size';
                case 3:
                    return 'Step 3: Select Quantity';
                case 4:
                    return 'Step 4: Select Pick Up Date & Time of Gallon';
                case 5:
                    return 'Step 5: Select Delivery Date & Time of Gallon';
                case 6:
                    return 'Step 6: Review & Confirm';
            }
        }

        // Restore UI based on saved state for each step
        function restoreStep(step) {
            if (step === 1) {
                // show both cards unless round forced slim hidden
                slim.classList.remove('hidden');
                set1.classList.remove('grid-cols-1', 'place-items-center');
                set1.classList.add('grid-cols-2');
                // visual selection
                slim.classList.remove('border-blue-500', 'border-4');
                round.classList.remove('border-blue-500', 'border-4');
                if (state.chosenType === 'slim') {
                    slim.classList.add('border-blue-500', 'border-4');
                } else if (state.chosenType === 'round') {
                    round.classList.add('border-blue-500', 'border-4');
                    // keep slim hidden and center round if user had chosen round
                    slim.classList.add('hidden');
                    set1.classList.remove('grid-cols-2');
                    set1.classList.add('grid-cols-1', 'place-items-center');
                    // adjust label
                    const roundLabel = round.querySelector('p');
                    if (roundLabel) roundLabel.textContent = 'Round Gallon - 18.9L';
                } else {
                    // no selection: ensure defaults
                    const roundLabel = round.querySelector('p');
                    if (roundLabel) roundLabel.textContent = 'Round Gallon';
                }
            }

            if (step === 2) {
                // restore size visuals
                [size1, size2].forEach(el => {
                    el.classList.remove('border-blue-500', 'border-4', 'bg-blue-500', 'text-white');
                    el.classList.add('border-gray-300');
                });
                if (state.chosenSize === 'large') {
                    size1.classList.add('border-blue-500', 'border-4', 'bg-blue-500', 'text-white');
                } else if (state.chosenSize === 'small') {
                    size2.classList.add('border-blue-500', 'border-4', 'bg-blue-500', 'text-white');
                }
            }

            if (step === 3) {
                state.quantity = state.quantity || 1;
                qtyDisplay.textContent = state.quantity;
                updateSubtotal();
            }

            if (step === 4) {
                pickupDate.value = pickupDate.value || getTodayString();
                restorePickupVisuals();
            }

            if (step === 5) {
                deliveryDate.value = deliveryDate.value || getTodayString();
                restoreDeliveryVisuals();
            }

            if (step === 6) {
                // fill address and summary placeholders (renderOrders handles the table)
                document.getElementById('userNameDisplay').textContent = state.userName;
                document.getElementById('userContactDisplay').textContent = state.userContact;
                document.getElementById('addressDisplay').innerHTML = `${state.userAddress.house || ''} ${state.userAddress.street || ''}<br>${state.userAddress.barangay || ''}, ${state.userAddress.city || ''}, ${state.userAddress.province || ''} ${state.userAddress.postal || ''}`;
                document.getElementById('pickupSummary').textContent = `${pickupDate.value || ''} ${state.chosenPickupTime || ''}`;
                document.getElementById('deliverySummary').textContent = `${deliveryDate.value || ''} ${state.chosenDeliveryTime || ''}`;
            }
        }

        function resetStep1UI() {
            // show both options and restore layout
            if (slim) {
                slim.classList.remove('hidden');
                slim.classList.remove('border-blue-500', 'border-4');
                slim.classList.add('border-gray-300');
            }
            if (round) {
                round.classList.remove('hidden');
                round.classList.remove('border-blue-500', 'border-4');
                round.classList.add('border-gray-300');
                const roundLabel = round.querySelector('p');
                if (roundLabel) roundLabel.textContent = 'Round Gallon';
            }
            if (set1) {
                set1.classList.remove('grid-cols-1', 'place-items-center');
                if (!set1.classList.contains('grid-cols-2')) set1.classList.add('grid-cols-2');
            }

            // maintain highlighting if a choice exists
            if (state.chosenType === 'round') {
                if (round) round.classList.add('border-blue-500', 'border-4');
                if (slim) slim.classList.remove('border-blue-500', 'border-4');
            } else if (state.chosenType === 'slim') {
                if (slim) slim.classList.add('border-blue-500', 'border-4');
                if (round) round.classList.remove('border-blue-500', 'border-4');
            }
        }


        // ---------- Selection actions that update state ----------

        function selectGallon(type) {
            state.chosenType = type;
            // next step logic…
            if (type === "round") {
                goToStep(3); // skip step 2
            } else {
                goToStep(2); // slim → step 2 exists
            }
        }

        // Gallon type
        slim.addEventListener('click', () => {
            state.chosenType = 'slim';

            slim.classList.add('border-blue-500', 'border-4');
            round.classList.remove('border-blue-500', 'border-4');
            slim.classList.remove('hidden');
            set1.classList.remove('grid-cols-1', 'place-items-center');
            set1.classList.add('grid-cols-2');

            const roundLabel = round.querySelector('p');
            if (roundLabel) roundLabel.textContent = 'Round Gallon';

            // ⭐ Show SweetAlert AFTER selecting Slim
            Swal.fire({
                title: "Do you have your own gallon?",
                text: "Or would you like to borrow a gallon from the station?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "I have my own",
                cancelButtonText: "Borrow from station"
            }).then((result) => {
                if (result.isConfirmed) {
                    state.hasOwnGallon = true;
                } else {
                    state.hasOwnGallon = false;
                }
                goToStep(2); // proceed to size selection
            });
        });

        round.addEventListener('click', () => {
            state.chosenType = 'round';

            round.classList.add('border-blue-500', 'border-4');
            slim.classList.remove('border-blue-500', 'border-4');
            slim.classList.add('hidden');
            set1.classList.remove('grid-cols-2');
            set1.classList.add('grid-cols-1', 'place-items-center');

            const roundLabel = round.querySelector('p');
            if (roundLabel) roundLabel.textContent = 'Round Gallon - 18.9L';

            if (!round.dataset.price) round.dataset.price = '30';
            state.chosenSize = '18.9L';
            state.chosenPrice = parseFloat(round.dataset.price) || 0;

            // ⭐ Show SweetAlert AFTER selecting Round
            Swal.fire({
                title: "Do you have your own gallon?",
                text: "Or would you like to borrow a gallon from the station?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "I have my own",
                cancelButtonText: "Borrow from station"
            }).then((result) => {
                if (result.isConfirmed) {
                    state.hasOwnGallon = true;
                } else {
                    state.hasOwnGallon = false;
                }
                goToStep(3); // round skips step 2
            });
        });

        // Size selection
        function chooseSize(size, silent = false) {
            state.chosenSize = size;
            [size1, size2].forEach(el => el.classList.remove('border-blue-500', 'border-4', 'bg-blue-500', 'text-white'));
            if (size === 'large') {
                size1.classList.add('border-blue-500', 'border-4', 'bg-blue-500', 'text-white');
                state.chosenPrice = parseFloat(size1.dataset.price) || 0;
            } else if (size === 'small') {
                size2.classList.add('border-blue-500', 'border-4', 'bg-blue-500', 'text-white');
                state.chosenPrice = parseFloat(size2.dataset.price) || 0;
            } else {
                state.chosenPrice = 0;
            }
            if (!silent && state.chosenSize) goToStep(3);
        }
        size1.addEventListener('click', () => chooseSize('large'));
        size2.addEventListener('click', () => chooseSize('small'));

        // Quantity
        function updateSubtotal() {
            const subtotal = (state.chosenPrice || 0) * (state.quantity || 0);
            subtotalDisplay.textContent = '₱' + subtotal.toFixed(2);
            qtyDisplay.textContent = state.quantity;
        }
        qtyMinus.addEventListener('click', () => {
            if (state.quantity > 1) state.quantity -= 1;
            updateSubtotal();
        });
        qtyPlus.addEventListener('click', () => {
            state.quantity = (state.quantity || 1) + 1;
            updateSubtotal();
        });

        // ---------- Pickup/delivery time behaviours ----------
        function selectPickupTime(btn, label) {
            if (btn.disabled) return;
            [timeMorning, timeAfternoon].forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('bg-white', 'border');
            });
            btn.classList.remove('bg-white', 'border');
            btn.classList.add('bg-blue-600', 'text-white');
            state.chosenPickupTime = label;
        }

        timeMorning.addEventListener('click', () => selectPickupTime(timeMorning, 'Morning - 7:30 AM'));
        timeAfternoon.addEventListener('click', () => selectPickupTime(timeAfternoon, 'Afternoon - 1:00 PM'));

        function restorePickupVisuals() {
            [timeMorning, timeAfternoon].forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('bg-white', 'border');
            });
            if (state.chosenPickupTime) {
                if (state.chosenPickupTime.includes('7:30')) timeMorning.classList.remove('bg-white', 'border'), timeMorning.classList.add('bg-blue-600', 'text-white');
                else if (state.chosenPickupTime.includes('1:00')) timeAfternoon.classList.remove('bg-white', 'border'), timeAfternoon.classList.add('bg-blue-600', 'text-white');
            }
        }

        // Delivery selection
        const deliveryButtons = [delTime1, delTime2, delTime3, delTime4];
        const deliveryLabels = ['Morning - 9:00 AM', 'Morning - 11:00 AM', 'Afternoon - 2:00 PM', 'Afternoon - 4:00 PM'];
        deliveryButtons.forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                if (btn.disabled) return;
                deliveryButtons.forEach(b => {
                    b.classList.remove('bg-blue-600', 'text-white');
                    b.classList.add('bg-white', 'border');
                });
                btn.classList.remove('bg-white', 'border');
                btn.classList.add('bg-blue-600', 'text-white');
                state.chosenDeliveryTime = deliveryLabels[idx];
            });
        });

        function restoreDeliveryVisuals() {
            deliveryButtons.forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('bg-white', 'border');
            });
            if (state.chosenDeliveryTime) {
                const idx = deliveryLabels.indexOf(state.chosenDeliveryTime);
                if (idx >= 0) deliveryButtons[idx].classList.remove('bg-white', 'border'), deliveryButtons[idx].classList.add('bg-blue-600', 'text-white');
            }
        }

        // ---------- Next button handlers logic ----------
        function onNextFromStep3() {

            goToStep(4);
        }

        function onNextFromStep4() {
            const date = pickupDate.value;
            const time = state.chosenPickupTime;
            if (!date) {
                Swal.fire('Please select a pickup date', '', 'warning');
                return;
            }
            if (!time) {
                Swal.fire('Please select a pickup time slot', '', 'warning');
                return;
            }
            const today = getTodayString();
            const timeCode = time.includes('7:30') ? '07:30' : '13:00';
            if (date === today && !isTimeSlotAvailable(timeCode, date)) {
                Swal.fire('The selected pickup time has already passed. Please select a future time slot.', '', 'warning');
                return;
            }
            goToStep(5);
        }

        function onNextFromStep5() {
            const date = deliveryDate.value;
            const time = state.chosenDeliveryTime;
            if (!date) {
                Swal.fire('Please select a delivery date', '', 'warning');
                return;
            }
            if (!time) {
                Swal.fire('Please select a delivery time slot', '', 'warning');
                return;
            }
            const today = getTodayString();
            const timeMap = {
                'Morning - 9:00 AM': '09:00',
                'Morning - 11:00 AM': '11:00',
                'Afternoon - 2:00 PM': '14:00',
                'Afternoon - 4:00 PM': '16:00'
            };
            const selectedTime = timeMap[time];
            if (date === today && selectedTime && !isTimeSlotAvailable(selectedTime, date)) {
                Swal.fire('The selected delivery time has already passed. Please select a future time slot.', '', 'warning');
                return;
            }
            // mark step 6 and show review
            setActiveStep(6);
            goToStep(6);
        }

        // ---------- Orders & Step 6 rendering ----------
        function saveCurrentOrder(commit = true) {
            // Build order object from current state (return it even if not committing)
            if (!state.chosenType || !state.chosenPrice || !state.quantity) return null;

            let sizeLabel = '';
            if (state.chosenType === 'round') {
                sizeLabel = '18.9L';
            } else if (state.chosenType === 'slim') {
                sizeLabel = (state.chosenSize === 'large') ? '20L' : (state.chosenSize === 'small' ? '10L' : '');
            }

            const unit = state.chosenPrice || 0;
            const subtotal = unit * state.quantity;

            const order = {
                type: (state.chosenType === 'slim') ? 'Slim' : 'Round',
                size: sizeLabel,
                quantity: state.quantity,
                unitPrice: unit,
                subtotal: subtotal
            };

            if (commit) state.orders.push(order);
            return order;
        }


        function renderOrdersInStep6() {
            const tbody = document.getElementById('ordersTbody');
            const totalEl = document.getElementById('totalCost');

            // Always use saved orders
            const rows = state.orders ? state.orders.slice() : [];

            // Include current selection as a preview if it has a chosen type/size but not yet saved
            if (state.chosenType && !state.ordersPreviewed) {
                const preview = saveCurrentOrder(false); // build but don't commit
                if (preview) rows.push(preview);
            }

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
            if (totalEl) totalEl.textContent = total.toFixed(2);

            // Refresh the address & summaries shown in Step 6
            const userNameDisplay = document.getElementById('userNameDisplay');
            const userContactDisplay = document.getElementById('userContactDisplay');
            const addressDisplay = document.getElementById('addressDisplay');
            const pickupSummary = document.getElementById('pickupSummary');
            const deliverySummary = document.getElementById('deliverySummary');

            if (userNameDisplay) userNameDisplay.textContent = state.userName || '';
            if (userContactDisplay) userContactDisplay.textContent = state.userContact || '';
            if (addressDisplay) addressDisplay.innerHTML = `
        ${state.userAddress.house || ''} ${state.userAddress.street || ''}<br>
        ${state.userAddress.barangay || ''}, ${state.userAddress.city || ''}, 
        ${state.userAddress.province || ''} ${state.userAddress.postal || ''}
        `;
            if (pickupSummary) pickupSummary.textContent = `${pickupDate?.value || ''} ${state.chosenPickupTime || ''}`;
            if (deliverySummary) deliverySummary.textContent = `${deliveryDate?.value || ''} ${state.chosenDeliveryTime || ''}`;
        }



        // ---------- Address edit handlers inside step6 (delegation after rendering step6) ----------
        // We'll attach listeners once (they exist on the page) and toggle visibility
        document.getElementById('editAddressBtn').addEventListener('click', () => {
            document.getElementById('addressFormContainer').classList.remove('hidden');
            document.getElementById('addressDisplay').classList.add('hidden');
            document.getElementById('addrHouse').value = state.userAddress.house || '';
            document.getElementById('addrStreet').value = state.userAddress.street || '';
            document.getElementById('addrBarangay').value = state.userAddress.barangay || '';
            document.getElementById('addrCity').value = state.userAddress.city || '';
            document.getElementById('addrProvince').value = state.userAddress.province || '';
            document.getElementById('addrPostal').value = state.userAddress.postal || '';
        });

        document.getElementById('cancelAddressBtn').addEventListener('click', () => {
            document.getElementById('addressForm').classList.add('hidden');
            document.getElementById('addressDisplay').classList.remove('hidden');
        });

        document.getElementById('saveAddressBtn').addEventListener('click', () => {
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
            state.userAddress.house = h;
            state.userAddress.street = s;
            state.userAddress.barangay = b;
            state.userAddress.city = c;
            state.userAddress.province = p;
            state.userAddress.postal = pc;
            document.getElementById('addressDisplay').innerHTML = `${h} ${s}<br>${b}, ${c}, ${p} ${pc}`;
            document.getElementById('addressForm').classList.add('hidden');
            document.getElementById('addressDisplay').classList.remove('hidden');
        });

        // ---------- Place order ----------
        function onPlaceOrder() {
            const addr = state.userAddress;
            const addrFilled = addr.house && addr.street && addr.barangay && addr.city && addr.province && addr.postal;

            if (!addrFilled) {
                Swal.fire('Please provide a full delivery address before placing the order', '', 'warning');
                return;
            }

            // Collect all orders including the current selection
            let allOrders = state.orders ? state.orders.slice() : [];

            // If there's a current selection that hasn't been saved yet, add it
            if (state.chosenType && state.chosenPrice > 0) {
                const currentOrder = saveCurrentOrder(false);
                if (currentOrder) {
                    // Check if this exact item is already in orders
                    const alreadySaved = allOrders.some(o =>
                        o.type === currentOrder.type &&
                        o.size === currentOrder.size &&
                        o.quantity === currentOrder.quantity &&
                        o.unitPrice === currentOrder.unitPrice
                    );
                    if (!alreadySaved) {
                        allOrders.push(currentOrder);
                    }
                }
            }

            // Ensure we have at least one order
            if (!allOrders || allOrders.length === 0) {
                Swal.fire('Error', 'No items to order', 'error');
                return;
            }

            // Calculate total amount from ALL orders
            const totalAmount = allOrders.reduce((sum, order) => sum + order.subtotal, 0);

            // Generate order number (you might want to get this from backend)
            const orderNumber = 'ORD' + Date.now();

            // Format datetime strings for MySQL (YYYY-MM-DD HH:MM:SS)
            const pickupDatetime = formatDateTimeForMySQL(pickupDate.value, state.chosenPickupTime);
            const deliveryDatetime = formatDateTimeForMySQL(deliveryDate.value, state.chosenDeliveryTime);

            // Determine gallon owner
            const gallonOwner = state.hasOwnGallon ? 'customer' : 'station';

            // Get user_id from the fetched user data (assuming it's available)
            const userId = state.userId || null;

            if (!userId) {
                Swal.fire('Error', 'User information not found. Please refresh the page.', 'error');
                return;
            }

            // Prepare order data
            const orderData = {
                user_id: userId,
                order_number: orderNumber,
                total_amount: totalAmount,
                pickup_datetime: pickupDatetime,
                delivery_datetime: deliveryDatetime,
                order_status: 'pending',
                gallon_owner: gallonOwner,
                delivery_address: `${addr.house} ${addr.street}, ${addr.barangay}, ${addr.city}, ${addr.province} ${addr.postal}`,
                orders: allOrders // Send ALL orders
            };

            console.log('Sending order:', orderData); // Debug log
            console.log('Total orders:', allOrders.length); // Debug log

            // Send order to backend
            fetch('../Order/water_refill_backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Clear orders after successful placement
                        state.orders = [];
                        state.chosenType = null;
                        state.chosenSize = null;
                        state.chosenPrice = 0;
                        state.quantity = 1;

                        Swal.fire({
                            title: '<span style="color:#2563eb;font-weight:600;">Pending Payment</span>',
                            html: `
                                        <div class="text-base mb-4">
                                            Thank you for your order! Please have your payment ready upon delivery.
                                        </div>
                                        <div class="text-center">
                                            <button id='viewOrdersBtn' class='bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded'>
                                                View My Orders
                                            </button>
                                        </div>
                            `,
                            showConfirmButton: false,
                            showCloseButton: true,
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'rounded-lg'
                            },

                            didOpen: () => {
                                // View Orders button
                                const viewBtn = document.getElementById('viewOrdersBtn');
                                if (viewBtn) viewBtn.onclick = () => window.location.href = "#";

                                // Replace Place Order with Order Again
                                const placeBtn = document.getElementById("placeOrderBtn");
                                if (placeBtn) {
                                    // Remove old listeners
                                    placeBtn.replaceWith(placeBtn.cloneNode(true));
                                    const newPlaceBtn = document.getElementById("placeOrderBtn");

                                    newPlaceBtn.innerHTML = "Order Again";
                                    newPlaceBtn.className = "w-40 bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700";
                                    newPlaceBtn.disabled = false;
                                    newPlaceBtn.addEventListener("click", () => {
                                        window.location.href = "order_water_refill.php";
                                    });
                                }

                                // Disable Back, Start Over, Add More
                                ["backBtn6", "startOverBtn6", "addMoreOrdersBtn"].forEach(id => {
                                    const btn = document.getElementById(id);
                                    if (btn) {
                                        btn.disabled = true;
                                        btn.classList.add("opacity-60", "cursor-not-allowed", "pointer-events-none");
                                    }
                                });
                            }


                        });


                    } else {
                        console.error('Backend error:', data.message);
                        Swal.fire('Error', data.message || 'Failed to place order. Please try again.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    Swal.fire('Error', 'Something went wrong: ' + err.message, 'error');
                });
        }

        // Helper function to format datetime for MySQL
        function formatDateTimeForMySQL(dateStr, timeLabel) {
            // Parse time from label
            const timeMap = {
                'Morning - 7:30 AM': '07:30:00',
                'Afternoon - 1:00 PM': '13:00:00',
                'Morning - 9:00 AM': '09:00:00',
                'Morning - 11:00 AM': '11:00:00',
                'Afternoon - 2:00 PM': '14:00:00',
                'Afternoon - 4:00 PM': '16:00:00'
            };

            const time = timeMap[timeLabel] || '00:00:00';
            return `${dateStr} ${time}`;
        }

        // Helper function to format datetime for MySQL
        function formatDateTimeForMySQL(dateStr, timeLabel) {
            // Parse time from label
            const timeMap = {
                'Morning - 7:30 AM': '07:30:00',
                'Afternoon - 1:00 PM': '13:00:00',
                'Morning - 9:00 AM': '09:00:00',
                'Morning - 11:00 AM': '11:00:00',
                'Afternoon - 2:00 PM': '14:00:00',
                'Afternoon - 4:00 PM': '16:00:00'
            };

            const time = timeMap[timeLabel] || '00:00:00';
            return `${dateStr} ${time}`;
        }

        // ---------- Date initialization and listeners ----------
        function initializeDateInputs() {
            const today = getTodayString();
            pickupDate.min = today;
            if (!pickupDate.value) pickupDate.value = today;
            deliveryDate.min = today;
            if (!deliveryDate.value) deliveryDate.value = today;
        }

        pickupDate.addEventListener('change', () => {
            updateTimeSlots(pickupDate, [timeMorning, timeAfternoon]);
            enforceDeliveryMinDate();
        });

        deliveryDate.addEventListener('change', () => {
            enforceDeliveryMinDate();
        });
        // ---------- Initial setup ----------
        initializeDateInputs();
        updateTimeSlots(pickupDate, [timeMorning, timeAfternoon]);
        updateTimeSlots(deliveryDate, [delTime1, delTime2, delTime3, delTime4]);

        // set default quantity/price if needed
        state.quantity = 1;
        // If user selected something before page load, it would be restored; otherwise defaults apply
        updateSubtotal();

        // Start at step 1
        goToStep(1);
    </script>
</body>

</html>