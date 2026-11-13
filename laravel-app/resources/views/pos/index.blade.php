<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — Seumpama</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-100 text-slate-900 antialiased"
    x-data="posApp({{ Js::from($cartData) }}, {{ Js::from($productsPayload) }}, {{ Js::from($paymentMethods) }})">

    {{-- Topbar --}}
    <header class="h-14 bg-white border-b border-slate-200 flex items-center px-4 gap-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-slate-700 hover:text-slate-900">
            <div class="w-8 h-8 rounded bg-blue-600 flex items-center justify-center">
                <x-icon name="coffee" :size="16" class="text-white" />
            </div>
            <span class="font-semibold text-sm">Seumpama POS</span>
        </a>

        <div class="flex-1"></div>

        <div class="text-xs text-slate-500">
            <span class="hidden sm:inline">Session: </span>
            <span class="font-mono font-medium text-slate-700">{{ $session->business_date->format('d M Y') }}</span>
        </div>

        <div class="h-6 w-px bg-slate-200"></div>

        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="text-xs">
                <div class="font-medium text-slate-900 leading-tight">{{ auth()->user()->name }}</div>
                <div class="text-slate-500 leading-tight">{{ auth()->user()->role()?->name }}</div>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <div class="flex h-[calc(100vh-3.5rem)] overflow-hidden">

        {{-- Left: Product Catalog --}}
        <main class="flex-1 flex flex-col overflow-hidden">
            {{-- Search + Filter --}}
            <div class="bg-white border-b border-slate-200 p-3 space-y-2 shrink-0">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari produk (nama atau SKU)..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex gap-1.5 overflow-x-auto pb-1 -mb-1">
                    <button @click="categoryFilter = null"
                        :class="categoryFilter === null ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                        class="shrink-0 px-3 py-1 rounded-full border text-xs font-medium transition-colors">
                        Semua
                    </button>
                    @foreach ($categories as $cat)
                    <button @click="categoryFilter = {{ $cat->id }}"
                        :class="categoryFilter === {{ $cat->id }} ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                        class="shrink-0 px-3 py-1 rounded-full border text-xs font-medium transition-colors whitespace-nowrap">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="flex-1 overflow-y-auto p-3">
                <template x-if="filteredProducts.length === 0">
                    <div class="flex items-center justify-center h-full text-center">
                        <div>
                            <x-icon name="coffee" :size="48" class="mx-auto text-slate-300 mb-3" />
                            <p class="text-slate-600 font-medium">Tidak ada produk</p>
                            <p class="text-sm text-slate-400 mt-1" x-text="search || categoryFilter ? 'Coba ubah filter.' : 'Belum ada produk yang dijual.'"></p>
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="addToCart(product)"
                            :disabled="loading"
                            class="group text-left bg-white rounded-lg border border-slate-200 overflow-hidden hover:border-blue-400 hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-wait flex flex-col">
                            <div class="aspect-square bg-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                <template x-if="product.image_url">
                                    <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image_url">
                                    <x-icon name="coffee" :size="32" class="text-slate-300" />
                                </template>
                            </div>
                            <div class="p-2">
                                <div class="text-sm font-medium text-slate-900 line-clamp-2 leading-tight" x-text="product.name"></div>
                                <div class="text-xs text-slate-500 mt-0.5" x-text="product.category_name || '—'"></div>
                                <div class="text-sm font-bold text-blue-700 mt-1 font-mono" x-text="'Rp ' + fmt(product.selling_price)"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </main>

        {{-- Right: Cart --}}
        <aside class="w-96 bg-white border-l border-slate-200 flex flex-col shrink-0">
            <div class="p-3 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Order</h2>
                    <p class="text-xs text-slate-500">
                        <span x-text="cart.item_count"></span> <span x-text="cart.item_count === 1 ? 'item' : 'items'"></span>
                    </p>
                </div>
                <button type="button" @click="clearCart()"
                    :disabled="cart.item_count === 0 || loading"
                    x-show="cart.item_count > 0"
                    class="text-xs text-red-600 hover:text-red-800 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Kosongkan
                </button>
            </div>

            {{-- Error --}}
            <template x-if="error">
                <div class="mx-3 mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700" x-text="error"></div>
            </template>

            {{-- Cart items --}}
            <div class="flex-1 overflow-y-auto">
                <template x-if="cart.items.length === 0">
                    <div class="flex items-center justify-center h-full p-6 text-center">
                        <div>
                            <x-icon name="shopping-cart" :size="36" class="mx-auto text-slate-300 mb-2" />
                            <p class="text-sm text-slate-500">Cart kosong</p>
                            <p class="text-xs text-slate-400 mt-1">Klik produk untuk menambahkan</p>
                        </div>
                    </div>
                </template>

                <div class="divide-y divide-slate-100">
                    <template x-for="item in cart.items" :key="item.id">
                        <div class="p-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-slate-900 leading-tight" x-text="item.name"></div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5" x-text="'Rp ' + fmt(item.unit_price)"></div>
                                </div>
                                <button type="button" @click="removeItem(item.id)"
                                    class="text-slate-400 hover:text-red-600 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="inline-flex items-center rounded-lg border border-slate-300">
                                    <button type="button" @click="updateQty(item.id, item.quantity - 1)"
                                        class="px-2 py-1 text-slate-600 hover:bg-slate-100 disabled:opacity-50"
                                        :disabled="loading">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14" />
                                        </svg>
                                    </button>
                                    <span class="px-3 text-sm font-mono text-slate-900 min-w-[2rem] text-center" x-text="item.quantity"></span>
                                    <button type="button" @click="updateQty(item.id, item.quantity + 1)"
                                        class="px-2 py-1 text-slate-600 hover:bg-slate-100 disabled:opacity-50"
                                        :disabled="loading">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14" />
                                            <path d="M12 5v14" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="text-sm font-bold text-slate-900 font-mono" x-text="'Rp ' + fmt(item.subtotal)"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer / Subtotal --}}
            <div class="border-t border-slate-200 p-3 space-y-2 shrink-0">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-700">Subtotal</span>
                    <span class="text-lg font-bold text-slate-900 font-mono" x-text="'Rp ' + fmt(cart.subtotal)"></span>
                </div>

                <button type="button" @click="checkoutOpen = true"
                    :disabled="cart.item_count === 0 || loading"
                    class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed">
                    Checkout
                </button>
            </div>
        </aside>
    </div>

    {{-- Checkout Modal --}}
    <div x-show="checkoutOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div class="absolute inset-0 bg-slate-900/50" @click="checkoutOpen = false"></div>

        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden"
            @click.stop>
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Checkout</h3>
                <button @click="checkoutOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                {{-- Error --}}
                <template x-if="checkoutError">
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700" x-text="checkoutError"></div>
                </template>

                {{-- Total --}}
                <div class="p-4 bg-slate-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Total</span>
                        <span class="text-2xl font-bold text-slate-900 font-mono" x-text="'Rp ' + fmt(cart.subtotal)"></span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">
                        <span x-text="cart.item_count"></span> item
                    </div>
                </div>

                {{-- Payment method --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="m in paymentMethods" :key="m.id">
                            <button type="button" @click="paymentMethodId = m.id; amountPaid = (m.code !== 'cash' ? String(cart.subtotal) : '')"
                                :class="paymentMethodId === m.id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                                class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors">
                                <span x-text="m.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Amount input --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Jumlah Dibayar <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="any" min="0" x-model="amountPaid"
                        :placeholder="selectedPaymentMethod?.code === 'cash' ? 'Masukkan jumlah uang' : String(cart.subtotal)"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-lg font-mono text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-slate-500 mt-1" x-show="selectedPaymentMethod?.code === 'cash'">
                        Cash: boleh lebih (ada kembalian)
                    </p>
                    <p class="text-xs text-slate-500 mt-1" x-show="selectedPaymentMethod && selectedPaymentMethod.code !== 'cash'">
                        <span x-text="selectedPaymentMethod?.name"></span>: harus tepat
                    </p>
                </div>

                {{-- Inline validation warning --}}
                <template x-if="selectedPaymentMethod && selectedPaymentMethod.code !== 'cash' && amountPaid !== '' && Math.abs((parseFloat(amountPaid) || 0) - cart.subtotal) > 0.01">
                    <div class="p-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800">
                        <template x-if="(parseFloat(amountPaid) || 0) > cart.subtotal + 0.01">
                            <span>
                                <strong>Metode <span x-text="selectedPaymentMethod.name"></span> tidak menerima kelebihan bayar.</strong>
                                Ubah jumlah menjadi tepat <strong x-text="'Rp ' + fmt(cart.subtotal)"></strong>, atau ganti metode ke Cash.
                            </span>
                        </template>
                        <template x-if="(parseFloat(amountPaid) || 0) < cart.subtotal - 0.01">
                            <span>
                                <strong>Pembayaran kurang.</strong>
                                Butuh <strong x-text="'Rp ' + fmt(cart.subtotal)"></strong>.
                            </span>
                        </template>
                    </div>
                </template>

                {{-- Change (cash) --}}
                <template x-if="selectedPaymentMethod?.code === 'cash' && change > 0">
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg flex justify-between">
                        <span class="text-sm font-medium text-green-800">Kembalian</span>
                        <span class="font-bold text-green-800 font-mono" x-text="'Rp ' + fmt(change)"></span>
                    </div>
                </template>

                {{-- Reference --}}
                <template x-if="selectedPaymentMethod && selectedPaymentMethod.code !== 'cash'">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            No. Referensi <span class="text-xs text-slate-400">(opsional)</span>
                        </label>
                        <input type="text" x-model="paymentReference" placeholder="No. transaksi QRIS/transfer"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </template>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Catatan <span class="text-xs text-slate-400">(opsional)</span>
                    </label>
                    <input type="text" x-model="orderNotes" placeholder="Catatan order"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="px-5 py-4 border-t border-slate-200 flex gap-2">
                <button type="button" @click="checkoutOpen = false"
                    class="flex-1 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="submitCheckout()"
                    :disabled="!canCheckout || loading"
                    class="flex-1 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!loading">Konfirmasi</span>
                    <span x-show="loading">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function posApp(initialCart, products, paymentMethods) {
            return {
                cart: initialCart,
                products: products,
                paymentMethods: paymentMethods,
                search: '',
                categoryFilter: null,
                loading: false,
                error: null,
                checkoutOpen: false,
                checkoutError: null,
                paymentMethodId: paymentMethods.length > 0 ? paymentMethods[0].id : null,
                amountPaid: '',
                paymentReference: '',
                orderNotes: '',

                get selectedPaymentMethod() {
                    return this.paymentMethods.find(m => m.id == this.paymentMethodId) || null;
                },

                get change() {
                    const paid = parseFloat(this.amountPaid) || 0;
                    const change = paid - this.cart.subtotal;
                    return change > 0 ? change : 0;
                },

                get canCheckout() {
                    if (this.cart.item_count === 0) return false;
                    const paid = parseFloat(this.amountPaid) || 0;
                    if (paid < this.cart.subtotal - 0.01) return false;
                    if (this.selectedPaymentMethod?.code !== 'cash' && Math.abs(paid - this.cart.subtotal) > 0.01) return false;
                    return true;
                },

                get filteredProducts() {
                    const s = this.search.trim().toLowerCase();
                    return this.products.filter(p => {
                        if (this.categoryFilter !== null && p.category_id !== this.categoryFilter) return false;
                        if (s && !p.name.toLowerCase().includes(s) && !p.sku.toLowerCase().includes(s)) return false;
                        return true;
                    });
                },

                fmt(n) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
                },

                async request(url, options) {
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    const res = await fetch(url, {
                        ...options,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            ...(options.headers || {}),
                        },
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) {
                        throw new Error(data.message || 'Terjadi kesalahan.');
                    }
                    return data;
                },

                async addToCart(product) {
                    if (this.loading) return;
                    this.loading = true;
                    this.error = null;
                    try {
                        const data = await this.request('/pos/cart/items', {
                            method: 'POST',
                            body: JSON.stringify({
                                product_id: product.id,
                                quantity: 1,
                            }),
                        });
                        this.cart = data.cart;
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async updateQty(itemId, newQty) {
                    if (newQty <= 0) return this.removeItem(itemId);
                    if (this.loading) return;
                    this.loading = true;
                    this.error = null;
                    try {
                        const data = await this.request(`/pos/cart/items/${itemId}`, {
                            method: 'PATCH',
                            body: JSON.stringify({
                                quantity: newQty
                            }),
                        });
                        this.cart = data.cart;
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async removeItem(itemId) {
                    if (this.loading) return;
                    this.loading = true;
                    this.error = null;
                    try {
                        const data = await this.request(`/pos/cart/items/${itemId}`, {
                            method: 'DELETE',
                        });
                        this.cart = data.cart;
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async clearCart() {
                    if (!confirm('Kosongkan cart?')) return;
                    if (this.loading) return;
                    this.loading = true;
                    this.error = null;
                    try {
                        const data = await this.request('/pos/cart/clear', {
                            method: 'POST',
                        });
                        this.cart = data.cart;
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async submitCheckout() {
                    if (!this.canCheckout || this.loading) return;
                    this.loading = true;
                    this.checkoutError = null;
                    try {
                        const data = await this.request('/pos/checkout', {
                            method: 'POST',
                            body: JSON.stringify({
                                payment_method_id: this.paymentMethodId,
                                amount_paid: parseFloat(this.amountPaid),
                                payment_reference: this.paymentReference || null,
                                notes: this.orderNotes || null,
                            }),
                        });
                        if (data.success && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        }
                    } catch (e) {
                        this.checkoutError = e.message;
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</body>

</html>
