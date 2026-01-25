// Global State
let cart = JSON.parse(localStorage.getItem('noon_cart')) || [];
const MOCK_MODE = true; // Set to false when connecting to PHP
let allProducts = []; // Store all products for search/filtering
let currentCategory = 'all';
let currentSearchTerm = '';

// Mock Data
const MOCK_PRODUCTS = [
    { id: 1, name: 'Fresh Tomatoes (1kg)', price: 5.50, image: 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=400&q=80', category: 'vegetables' },
    { id: 2, name: 'Local Cucumber  (1kg)', price: 4.00, image: 'https://images.unsplash.com/photo-1604977042946-1eecc6a22662?auto=format&fit=crop&w=400&q=80', category: 'vegetables' },
    { id: 3, name: 'Fresh Milk Full Fat (2L)', price: 12.00, image: 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=400&q=80', category: 'grocery' },
    { id: 4, name: 'Chicken Breast Fillet (500g)', price: 18.50, image: 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=400&q=80', category: 'meat' },
    { id: 5, name: 'Fresh Salmon Fillet (200g)', price: 25.00, image: 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?auto=format&fit=crop&w=400&q=80', category: 'fish' },
    { id: 6, name: 'Basmati Rice (5kg)', price: 35.00, image: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80', category: 'hypermarket' },
    { id: 7, name: 'Eggs Large (30pcs)', price: 22.00, image: 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?auto=format&fit=crop&w=400&q=80', category: 'grocery' },
    { id: 8, name: 'Red Apples (1kg)', price: 7.50, image: 'https://images.unsplash.com/photo-1570913149827-d2ac84ab3f9a?auto=format&fit=crop&w=400&q=80', category: 'vegetables' },
    { id: 9, name: 'Fresh Bananas (1kg)', price: 6.00, image: 'https://images.unsplash.com/photo-1603833665858-e61d17a86224?auto=format&fit=crop&w=400&q=80', category: 'vegetables' },
    { id: 10, name: 'Lamb Chops (500g)', price: 45.00, image: 'https://images.unsplash.com/photo-1603360946369-dc9bb6f5429a?auto=format&fit=crop&w=400&q=80', category: 'meat' },
    { id: 11, name: 'Fresh Shrimps (500g)', price: 30.00, image: 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?auto=format&fit=crop&w=400&q=80', category: 'fish' },
    { id: 12, name: 'Sunflower Oil (1.5L)', price: 18.00, image: 'https://images.unsplash.com/photo-1474979266404-7eaacbcd041c?auto=format&fit=crop&w=400&q=80', category: 'grocery' },
    { id: 13, name: 'Italian Spaghetti (500g)', price: 8.50, image: 'https://images.unsplash.com/photo-1595295333158-4742f28fbd85?auto=format&fit=crop&w=400&q=80', category: 'hypermarket' },
    { id: 14, name: 'Orange Juice (1L)', price: 10.00, image: 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=400&q=80', category: 'grocery' },
];

// Utility: Format Price
const formatPrice = (price) => `AED ${parseFloat(price).toFixed(2)}`;

// Init
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();

    if (document.getElementById('product-grid')) {
        loadProducts();
    }

    if (document.getElementById('cart-items-container')) {
        renderCart();
    }

    // Category Filtering
    document.querySelectorAll('.category-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const category = e.target.dataset.category;
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput ? searchInput.value : '';
            loadProducts(category, searchTerm);
        });
    });
});

// Load Products
async function loadProducts(category = 'all', searchTerm = '') {
    let products = [];

    // Load all products if not already loaded
    if (allProducts.length === 0) {
        if (MOCK_MODE) {
            allProducts = MOCK_PRODUCTS;
        } else {
            // Fetch from PHP API
            try {
                const res = await fetch('api/get_products.php');
                allProducts = await res.json();
            } catch (e) {
                console.error("API Error", e);
                allProducts = [];
            }
        }
    }

    products = [...allProducts];

    // Apply category filter
    if (category !== 'all') {
        products = products.filter(p => p.category === category);
    }

    // Apply search filter
    if (searchTerm && searchTerm.trim() !== '') {
        const searchLower = searchTerm.toLowerCase().trim();
        products = products.filter(p => 
            p.name.toLowerCase().includes(searchLower)
        );
    }

    // Update current filters
    currentCategory = category;
    currentSearchTerm = searchTerm;

    // Render products
    renderProducts(products);
}

// Render products to grid
function renderProducts(products) {
    const grid = document.getElementById('product-grid');
    
    if (!grid) return;

    if (products.length === 0) {
        grid.innerHTML = '<div style="padding: 2rem; width: 100%; text-align: center; grid-column: 1 / -1;">No products found. Try a different search or category.</div>';
        return;
    }

    grid.innerHTML = products.map(p => `
        <div class="product-card">
            <img src="${p.image || p.image_url}" alt="${p.name}" class="product-image">
            <div class="product-info">
                <div class="product-price">
                    <span class="currency">AED</span> ${parseFloat(p.price).toFixed(2)}
                </div>
                <h3 class="product-name">${p.name}</h3>
                <button onclick="addToCart(${p.id})" class="add-btn">Add to cart</button>
            </div>
        </div>
    `).join('');
}

// Handle search input
function handleSearch() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;
    
    const searchTerm = searchInput.value;
    loadProducts(currentCategory, searchTerm);
}

// Clear search
function clearSearch() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
    }
    loadProducts(currentCategory, '');
}

// Cart Management
function addToCart(id) {
    // Find product from allProducts (or MOCK_PRODUCTS as fallback)
    const product = allProducts.find(p => p.id === id) || MOCK_PRODUCTS.find(p => p.id === id);
    
    if (!product) {
        console.error('Product not found:', id);
        return;
    }

    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ ...product, quantity: 1 });
    }

    saveCart();
    showToast(`Added ${product.name} to cart`);
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    renderCart(); // Re-render if on cart page
}

function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            saveCart();
            renderCart();
        }
    }
}

function saveCart() {
    localStorage.setItem('noon_cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
}

function renderCart() {
    const container = document.getElementById('cart-items-container');
    const summaryEl = document.getElementById('cart-summary');

    if (!container || !summaryEl) return;

    if (cart.length === 0) {
        container.innerHTML = '<p style="padding: 2rem; text-align: center;">Your cart is empty.</p>';
        summaryEl.style.display = 'none';
        return;
    }

    summaryEl.style.display = 'block';

    container.innerHTML = cart.map(item => `
        <div class="cart-item">
            <img src="${item.image || item.image_url}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
            <div style="flex: 1;">
                <h4>${item.name}</h4>
                <div style="font-weight: bold; margin: 0.2rem 0;">${formatPrice(item.price)}</div>
                <div class="item-quantity">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    <button onclick="removeFromCart(${item.id})" style="margin-left: auto; color: red; border: none; background: none; cursor: pointer; font-size: 0.9rem;">Remove</button>
                </div>
            </div>
        </div>
    `).join('');

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const delivery = 10.00;
    const total = subtotal + delivery;

    if (document.getElementById('subtotal-display')) {
        document.getElementById('subtotal-display').textContent = formatPrice(subtotal);
        document.getElementById('total-display').textContent = formatPrice(total);
    }
}

// Checkout
async function placeOrder(event) {
    event.preventDefault();

    if (cart.length === 0) return alert('Cart is empty');

    const form = event.target;
    const formData = new FormData(form);
    const orderData = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        address: `${formData.get('area')}, ${formData.get('street')}, ${formData.get('building')}`,
        latitude: formData.get('latitude'),
        longitude: formData.get('longitude'),
        items: cart,
        total: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) + 10
    };

    if (MOCK_MODE) {
        console.log('Order Placed:', orderData);
        alert('Order Placed Successfully! (Mock Mode)\nTotal: ' + formatPrice(orderData.total));
        cart = [];
        saveCart();
        window.location.href = 'index.html';
    } else {
        // Send to PHP
        const res = await fetch('api/place_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        const result = await res.json();
        if (result.success) {
            cart = [];
            saveCart();
            window.location.href = 'tracking.html?id=' + result.order_id;
        } else {
            alert('Error placing order');
        }
    }
}

// WhatsApp Order Handler
async function orderViaWhatsapp(event) {
    event.preventDefault();
    if (cart.length === 0) return alert('Cart is empty');

    // Get manual address fields
    const area = document.getElementById('delivery-area')?.value || '';
    const building = document.getElementById('delivery-building')?.value || '';
    const apartment = document.getElementById('delivery-apartment')?.value || '';
    const landmark = document.getElementById('delivery-landmark')?.value || '';
    const lat = document.getElementById('lat')?.value || '';
    const lng = document.getElementById('lng')?.value || '';

    // Validate required fields
    if (!area || !building || !apartment) {
        alert('Please fill in all required address fields (Area, Building, Apartment)');
        return;
    }

    // Construct full address string
    let fullAddress = `${area}, ${building}, ${apartment}`;
    if (landmark) {
        fullAddress += ` (Near: ${landmark})`;
    }

    // Get User info from Login if available
    const loggedInUser = getCurrentUser();
    const user = loggedInUser ? { name: loggedInUser.name || 'Customer', phone: loggedInUser.phone } : { name: 'Guest', phone: 'WhatsApp User' };

    // Construct Message
    let message = `*New Order Application*\n`;
    message += `Customer: ${user.name}\n`;
    message += `Phone: ${user.phone}\n\n`;
    message += `*Items:*\n`;

    cart.forEach(item => {
        message += `- ${item.name} (x${item.quantity}) - AED ${item.price}\n`;
    });

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const total = subtotal + 10;

    message += `\n*Total: AED ${total.toFixed(2)}*\n`;
    message += `(Cash on Delivery)\n\n`;

    message += `*Delivery Address:*\n${fullAddress}\n`;
    
    if (lat && lng) {
        message += `\n*GPS Location:*\nhttps://maps.google.com/?q=${lat},${lng}`;
    }

    // Try to save to DB (Background) - optional
    try {
        const orderData = {
            name: user.name,
            phone: user.phone,
            address: fullAddress,
            latitude: lat || null,
            longitude: lng || null,
            items: cart,
            total: total
        };

        // Non-blocking fetch
        if (!MOCK_MODE) {
            fetch('api/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
        }
    } catch (e) { console.log('DB Save failed', e); }

    // Clear Cart
    cart = [];
    saveCart();

    // Redirect to WhatsApp
    const whatsappNumber = "94763885530"; // Admin Number (+94763885530)
    const startChatUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
    window.open(startChatUrl, '_blank');

    // Redirect to success page or reload
    window.location.href = 'index.html';
}

// Simple Toast
function showToast(msg) {
    const div = document.createElement('div');
    div.textContent = msg;
    div.style.cssText = `
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        background: #383838; color: white; padding: 10px 20px; border-radius: 4px;
        z-index: 2000; animation: fadeIn 0.3s;
    `;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 2000);
}

// Geolocation
function getLocation() {
    const status = document.getElementById('location-status');

    if (!navigator.geolocation) {
        status.textContent = "Geolocation is not supported by this browser.";
        return;
    }

    status.textContent = "Locating...";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;

            status.innerHTML = `<span style="color: green;">✓ Location Captured</span>`;

            // Show Map
            const mapFrame = document.getElementById('map-frame');
            const googleMap = document.getElementById('google-map');
            if (mapFrame && googleMap) {
                mapFrame.style.display = 'block';
                // Using Google Maps Embed API (Simple mode)
                googleMap.src = `https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
            }

            // Reverse Geocoding to auto-fill address fields
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    if (data.address) {
                        const areaField = document.getElementById('delivery-area');
                        const buildingField = document.getElementById('delivery-building');
                        const apartmentField = document.getElementById('delivery-apartment');
                        const landmarkField = document.getElementById('delivery-landmark');

                        // Auto-fill area/street
                        if (areaField) {
                            let areaText = '';
                            if (data.address.road) areaText = data.address.road;
                            if (data.address.suburb) areaText += (areaText ? ', ' : '') + data.address.suburb;
                            if (data.address.city && !areaText.includes(data.address.city)) {
                                areaText += (areaText ? ', ' : '') + data.address.city;
                            }
                            if (areaText) areaField.value = areaText;
                        }

                        // Auto-fill building/block (if available)
                        if (buildingField && data.address.house_number) {
                            buildingField.value = data.address.house_number;
                        }

                        // Auto-fill apartment (if available)
                        if (apartmentField && data.address.house) {
                            apartmentField.value = data.address.house;
                        }

                        // Auto-fill landmark (if available)
                        if (landmarkField && data.address.neighbourhood) {
                            landmarkField.value = data.address.neighbourhood;
                        }

                        status.innerHTML = `<span style="color: green;">✓ Location Captured & Address Auto-filled</span>`;
                    }
                })
                .catch(err => {
                    console.log('Reverse geocoding failed', err);
                    status.innerHTML = `<span style="color: green;">✓ Location Captured (Address fields not auto-filled)</span>`;
                });
        },
        (error) => {
            status.textContent = "Unable to retrieve your location.";
            console.error(error);
        }
    );
}

// User Authentication Functions
function getCurrentUser() {
    const userStr = localStorage.getItem('noon_user');
    return userStr ? JSON.parse(userStr) : null;
}

function setCurrentUser(user) {
    localStorage.setItem('noon_user', JSON.stringify(user));
}

function clearCurrentUser() {
    localStorage.removeItem('noon_user');
}

// Signup Handler
async function handleSignup(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const signupData = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        password: formData.get('password')
    };

    if (MOCK_MODE) {
        // Mock signup
        setCurrentUser({ name: signupData.name, phone: signupData.phone });
        alert('Account created successfully! (Mock Mode)');
        window.location.href = 'index.html';
    } else {
        try {
            const response = await fetch('api/signup.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(signupData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                setCurrentUser(result.user);
                alert('Account created successfully!');
                window.location.href = 'index.html';
            } else {
                alert(result.message || 'Error creating account');
            }
        } catch (error) {
            console.error('Signup error:', error);
            alert('Error creating account. Please try again.');
        }
    }
}

// Login Handler (OTP-based - simplified for now)
function sendOtp() {
    const phone = document.getElementById('login-phone')?.value;
    if (!phone) {
        alert('Please enter your phone number');
        return;
    }
    
    // In a real app, send OTP via WhatsApp/ SMS
    // For now, just show OTP input
    document.getElementById('login-step-1').style.display = 'none';
    document.getElementById('login-step-2').style.display = 'block';
    
    // Mock: Auto-fill OTP for testing
    if (MOCK_MODE) {
        setTimeout(() => {
            document.getElementById('login-otp').value = '1234';
        }, 500);
    }
}

function resetLogin() {
    document.getElementById('login-step-1').style.display = 'block';
    document.getElementById('login-step-2').style.display = 'none';
    document.getElementById('login-phone').value = '';
    document.getElementById('login-otp').value = '';
}

// Verify OTP and Login
async function verifyOtp(event) {
    if (event) event.preventDefault();
    
    const phone = document.getElementById('login-phone')?.value;
    const otp = document.getElementById('login-otp')?.value;
    
    if (!phone || !otp) {
        alert('Please enter phone number and OTP');
        return;
    }
    
    if (MOCK_MODE) {
        // Mock login - in real app, verify OTP with backend
        setCurrentUser({ name: 'User', phone: phone });
        alert('Logged in successfully! (Mock Mode)');
        window.location.href = 'index.html';
    } else {
        // In real app, verify OTP with backend
        // For now, use password-based login as fallback
        const password = prompt('Enter your password:');
        if (password) {
            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    setCurrentUser(result.user);
                    alert('Logged in successfully!');
                    window.location.href = 'index.html';
                } else {
                    alert(result.message || 'Invalid credentials');
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('Error logging in. Please try again.');
            }
        }
    }
}

// Update checkout form with user info if logged in
function prefillUserInfo() {
    const user = getCurrentUser();
    if (user && document.getElementById('checkout-form')) {
        // Pre-fill user info in checkout if form exists
        // This would be used if we add a checkout form with user details
    }
}

// Initialize user info on page load
document.addEventListener('DOMContentLoaded', () => {
    const user = getCurrentUser();
    if (user) {
        // User is logged in - can show user info in header, etc.
        console.log('User logged in:', user);
    }
    
    // Add OTP form submit handler if on login page
    const otpForm = document.getElementById('login-step-2');
    if (otpForm) {
        const verifyBtn = otpForm.querySelector('button[type="submit"]');
        if (verifyBtn) {
            verifyBtn.addEventListener('click', verifyOtp);
        }
    }
});
