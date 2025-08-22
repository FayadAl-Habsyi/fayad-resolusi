<?php
/**
 * Transaksi (Kasir) Page
 */
require_once '../config/config.php';

// Set active menu
$active_menu = 'transaksi';

// Set page title
$page_title = 'Kasir';

// Get products
$products = query("SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id WHERE p.status = 1 ORDER BY p.nama_produk ASC");

// Get customers
$customers = query("SELECT * FROM pelanggan ORDER BY nama ASC");

// Add extra CSS
$extra_css = <<<EOT
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .product-img {
        height: 120px;
        width: 100%;
        object-fit: cover;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-name {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .product-category {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 10px;
    }
    
    .product-price {
        font-weight: 700;
        color: #667eea;
    }
    
    .cart-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 20px;
        height: calc(100vh - 250px);
        display: flex;
        flex-direction: column;
    }
    
    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .cart-item-info {
        flex-grow: 1;
    }
    
    .cart-item-name {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .cart-item-price {
        font-size: 0.9rem;
        color: #667eea;
    }
    
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-item-quantity button {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: #f1f1f1;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .cart-item-quantity button:hover {
        background: #667eea;
        color: white;
    }
    
    .cart-item-quantity input {
        width: 50px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 5px;
    }
    
    .cart-item-total {
        font-weight: 600;
        min-width: 100px;
        text-align: right;
    }
    
    .cart-item-remove {
        color: #f5576c;
        cursor: pointer;
        margin-left: 10px;
    }
    
    .cart-summary {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
    }
    
    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .cart-summary-label {
        font-weight: 500;
    }
    
    .cart-summary-value {
        font-weight: 600;
    }
    
    .cart-total {
        font-size: 1.2rem;
        font-weight: 700;
        color: #667eea;
    }
    
    .search-container {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-container i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .search-input {
        padding-left: 40px;
        border-radius: 25px;
    }
    
    .category-filter {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .category-btn {
        white-space: nowrap;
        border-radius: 20px;
        padding: 8px 15px;
        background: white;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .category-btn.active {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
    }
    
    .payment-modal .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .payment-modal .modal-header {
        background: var(--primary-gradient);
        color: white;
        border-bottom: none;
    }
    
    .payment-modal .modal-body {
        padding: 25px;
    }
    
    .payment-modal .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 1rem;
    }
    
    .payment-modal .btn-primary {
        background: var(--primary-gradient);
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 600;
    }
    
    .payment-modal .btn-secondary {
        background: #f1f1f1;
        border: none;
        color: #333;
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .cart-container {
            height: auto;
            margin-bottom: 30px;
        }
        
        .cart-items {
            max-height: 300px;
        }
    }
</style>
EOT;

// Add extra JS
$extra_js = <<<EOT
<script>
    // Cart data
    let cart = [];
    let customer = null;
    let discount = 0;
    let note = '';
    
    // Format currency
    function formatRupiah(angka) {
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }
    
    // Add product to cart
    function addToCart(product) {
        // Check if product already in cart
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            // Update quantity
            existingItem.quantity += 1;
            existingItem.total = existingItem.price * existingItem.quantity;
        } else {
            // Add new item
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1,
                total: product.price
            });
        }
        
        // Update cart UI
        updateCart();
    }
    
    // Update cart item quantity
    function updateQuantity(id, quantity) {
        const item = cart.find(item => item.id === id);
        
        if (item) {
            item.quantity = quantity;
            item.total = item.price * item.quantity;
            updateCart();
        }
    }
    
    // Remove item from cart
    function removeItem(id) {
        cart = cart.filter(item => item.id !== id);
        updateCart();
    }
    
    // Update cart UI
    function updateCart() {
        const cartItems = document.getElementById('cartItems');
        const subtotalElement = document.getElementById('subtotal');
        const discountElement = document.getElementById('discount');
        const taxElement = document.getElementById('tax');
        const totalElement = document.getElementById('total');
        const emptyCartMessage = document.getElementById('emptyCartMessage');
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        // Calculate totals
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        const tax = 0; // You can calculate tax if needed
        const total = subtotal - discount + tax;
        
        // Update summary values
        subtotalElement.textContent = formatRupiah(subtotal);
        discountElement.textContent = formatRupiah(discount);
        taxElement.textContent = formatRupiah(tax);
        totalElement.textContent = formatRupiah(total);
        
        // Show/hide empty cart message and checkout button
        if (cart.length === 0) {
            emptyCartMessage.style.display = 'block';
            checkoutBtn.disabled = true;
        } else {
            emptyCartMessage.style.display = 'none';
            checkoutBtn.disabled = false;
        }
        
        // Clear cart items
        cartItems.innerHTML = '';
        
        // Add items to cart
        cart.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatRupiah(item.price)}</div>
                </div>
                <div class="cart-item-quantity">
                    <button onclick="updateQuantity(${item.id}, Math.max(1, ${item.quantity - 1}))">-</button>
                    <input type="number" min="1" value="${item.quantity}" onchange="updateQuantity(${item.id}, Math.max(1, parseInt(this.value) || 1))">
                    <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
                <div class="cart-item-total">${formatRupiah(item.total)}</div>
                <div class="cart-item-remove" onclick="removeItem(${item.id})"><i class="fas fa-trash"></i></div>
            `;
            
            cartItems.appendChild(cartItem);
        });
        
        // Save cart to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));
        localStorage.setItem('discount', discount);
        localStorage.setItem('customer', customer ? JSON.stringify(customer) : null);
        localStorage.setItem('note', note);
    }
    
    // Filter products by category
    function filterByCategory(categoryId) {
        const products = document.querySelectorAll('.product-item');
        const categoryButtons = document.querySelectorAll('.category-btn');
        
        // Update active category button
        categoryButtons.forEach(button => {
            if (button.dataset.category === categoryId) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
        
        // Show/hide products
        products.forEach(product => {
            if (categoryId === 'all' || product.dataset.category === categoryId) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    }
    
    // Search products
    function searchProducts() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const products = document.querySelectorAll('.product-item');
        
        products.forEach(product => {
            const productName = product.dataset.name.toLowerCase();
            
            if (productName.includes(searchInput)) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    }
    
    // Process payment
    function processPayment() {
        const cashInput = document.getElementById('cashInput');
        const cash = parseFloat(cashInput.value.replace(/[^0-9]/g, ''));
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        const tax = 0; // You can calculate tax if needed
        const total = subtotal - discount + tax;
        
        if (isNaN(cash) || cash < total) {
            alert('Jumlah uang tidak valid atau kurang dari total belanja');
            return;
        }
        
        // Prepare transaction data
        const transaction = {
            customer_id: customer ? customer.id : null,
            items: cart,
            subtotal: subtotal,
            discount: discount,
            tax: tax,
            total: total,
            cash: cash,
            change: cash - total,
            note: note
        };
        
        // Send transaction data to server
        fetch('../api/save_transaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(transaction)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear cart
                cart = [];
                customer = null;
                discount = 0;
                note = '';
                updateCart();
                
                // Close modal
                $('#paymentModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    title: 'Transaksi Berhasil!',
                    text: 'Transaksi telah berhasil disimpan.',
                    icon: 'success',
                    confirmButtonText: 'Cetak Struk',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Open receipt in new tab
                        window.open('../penjualan/cetak.php?id=' + data.transaction_id, '_blank');
                    }
                });
            } else {
                alert('Terjadi kesalahan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses transaksi');
        });
    }
    
    // Format cash input
    function formatCash(input) {
        // Remove non-numeric characters
        let value = input.value.replace(/[^0-9]/g, '');
        
        // Format as currency
        if (value !== '') {
            value = parseInt(value);
            input.value = formatRupiah(value);
        } else {
            input.value = '';
        }
    }
    
    // Calculate change
    function calculateChange() {
        const cashInput = document.getElementById('cashInput');
        const changeElement = document.getElementById('changeAmount');
        
        const cash = parseFloat(cashInput.value.replace(/[^0-9]/g, '')) || 0;
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        const tax = 0; // You can calculate tax if needed
        const total = subtotal - discount + tax;
        
        const change = cash - total;
        
        changeElement.textContent = change >= 0 ? formatRupiah(change) : 'Rp 0';
    }
    
    // Set customer
    function setCustomer(id, name) {
        customer = { id, name };
        document.getElementById('selectedCustomer').textContent = name;
    }
    
    // Set discount
    function setDiscount() {
        const discountInput = document.getElementById('discountInput');
        discount = parseFloat(discountInput.value.replace(/[^0-9]/g, '')) || 0;
        updateCart();
        $('#discountModal').modal('hide');
    }
    
    // Set note
    function setNote() {
        const noteInput = document.getElementById('noteInput');
        note = noteInput.value;
        $('#noteModal').modal('hide');
    }
    
    // Load cart from localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedCart = localStorage.getItem('cart');
        const savedDiscount = localStorage.getItem('discount');
        const savedCustomer = localStorage.getItem('customer');
        const savedNote = localStorage.getItem('note');
        
        if (savedCart) {
            cart = JSON.parse(savedCart);
        }
        
        if (savedDiscount) {
            discount = parseFloat(savedDiscount);
        }
        
        if (savedCustomer && savedCustomer !== 'null') {
            customer = JSON.parse(savedCustomer);
            document.getElementById('selectedCustomer').textContent = customer.name;
        }
        
        if (savedNote) {
            note = savedNote;
        }
        
        updateCart();
    });
</script>
EOT;

// Include header
include '../includes/header.php';
?>

<div class="row">
    <!-- Products Section -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Search and Filter -->
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control search-input" id="searchInput" placeholder="Cari produk..." oninput="searchProducts()">
                </div>
                
                <div class="category-filter">
                    <button class="category-btn active" data-category="all" onclick="filterByCategory('all')">Semua</button>
                    <?php foreach (query("SELECT * FROM kategori ORDER BY nama_kategori") as $category): ?>
                    <button class="category-btn" data-category="<?= $category['id'] ?>" onclick="filterByCategory('<?= $category['id'] ?>')"><?= $category['nama_kategori'] ?></button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Products Grid -->
                <div class="row g-3">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-3 col-sm-6 product-item" data-category="<?= $product['id_kategori'] ?>" data-name="<?= $product['nama_produk'] ?>">
                        <div class="card product-card" onclick="addToCart({id: <?= $product['id'] ?>, name: '<?= addslashes($product['nama_produk']) ?>', price: <?= $product['harga_jual'] ?>})">
                            <img src="<?= !empty($product['gambar']) && $product['gambar'] !== 'default-product.jpg' ? upload_url('products/' . $product['gambar']) : 'https://via.placeholder.com/150?text=' . urlencode($product['nama_produk']) ?>" class="product-img" alt="<?= $product['nama_produk'] ?>">
                            <div class="product-info">
                                <div class="product-name"><?= $product['nama_produk'] ?></div>
                                <div class="product-category"><?= $product['nama_kategori'] ?? 'Tanpa Kategori' ?></div>
                                <div class="product-price"><?= format_rupiah($product['harga_jual']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Section -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Keranjang Belanja</h5>
                <div>
                    <button class="btn btn-sm btn-outline-danger me-2" onclick="cart = []; updateCart();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="cart-container">
                    <!-- Cart Items -->
                    <div class="cart-items" id="cartItems">
                        <!-- Cart items will be added here dynamically -->
                    </div>
                    
                    <!-- Empty Cart Message -->
                    <div id="emptyCartMessage" class="text-center py-4 text-muted">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <p>Keranjang belanja kosong</p>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="cart-summary-row">
                            <div class="cart-summary-label">Subtotal</div>
                            <div class="cart-summary-value" id="subtotal">Rp 0</div>
                        </div>
                        <div class="cart-summary-row">
                            <div class="cart-summary-label">Diskon</div>
                            <div class="cart-summary-value" id="discount">Rp 0</div>
                        </div>
                        <div class="cart-summary-row">
                            <div class="cart-summary-label">Pajak</div>
                            <div class="cart-summary-value" id="tax">Rp 0</div>
                        </div>
                        <div class="cart-summary-row">
                            <div class="cart-summary-label cart-total">Total</div>
                            <div class="cart-summary-value cart-total" id="total">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row g-2 mb-3">
                    <div class="col">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#customerModal">
                            <i class="fas fa-user me-2"></i><span id="selectedCustomer">Pelanggan</span>
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#discountModal">
                            <i class="fas fa-tag me-2"></i>Diskon
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#noteModal">
                            <i class="fas fa-sticky-note me-2"></i>Catatan
                        </button>
                    </div>
                </div>
                <button id="checkoutBtn" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#paymentModal" disabled>
                    <i class="fas fa-money-bill-wave me-2"></i>Bayar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Pilih Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="customerSearch" placeholder="Cari pelanggan...">
                </div>
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="setCustomer(null, 'Umum')">
                        <strong>Umum</strong>
                    </button>
                    <?php foreach ($customers as $customer): ?>
                    <button type="button" class="list-group-item list-group-item-action" onclick="setCustomer(<?= $customer['id'] ?>, '<?= addslashes($customer['nama']) ?>')">
                        <strong><?= $customer['nama'] ?></strong>
                        <?php if (!empty($customer['no_telp'])): ?>
                        <br><small><?= $customer['no_telp'] ?></small>
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">Tambah Diskon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="discountInput" class="form-label">Jumlah Diskon</label>
                    <input type="text" class="form-control" id="discountInput" placeholder="0" oninput="formatCash(this)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="setDiscount()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalLabel">Tambah Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="noteInput" class="form-label">Catatan</label>
                    <textarea class="form-control" id="noteInput" rows="3" placeholder="Tambahkan catatan untuk transaksi ini"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="setNote()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade payment-modal" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h5 class="text-center">Total Pembayaran</h5>
                    <h2 class="text-center" id="paymentTotal"></h2>
                </div>
                
                <div class="mb-3">
                    <label for="cashInput" class="form-label">Jumlah Uang</label>
                    <input type="text" class="form-control form-control-lg" id="cashInput" placeholder="0" oninput="formatCash(this); calculateChange()">
                </div>
                
                <div class="mb-3">
                    <label for="changeAmount" class="form-label">Kembalian</label>
                    <h3 id="changeAmount" class="mb-0">Rp 0</h3>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="processPayment()">Proses Pembayaran</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Update payment total when modal is shown
    document.getElementById('paymentModal').addEventListener('show.bs.modal', function () {
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        const tax = 0; // You can calculate tax if needed
        const total = subtotal - discount + tax;
        
        document.getElementById('paymentTotal').textContent = formatRupiah(total);
    });
    
    // Filter customers when searching
    document.getElementById('customerSearch').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const customerItems = document.querySelectorAll('#customerModal .list-group-item');
        
        customerItems.forEach(item => {
            const customerName = item.textContent.toLowerCase();
            
            if (customerName.includes(searchValue)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>

<?php
// Include footer
include '../includes/footer.php';
?>