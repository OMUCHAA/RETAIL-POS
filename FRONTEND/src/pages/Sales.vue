<script setup>
import { computed, onMounted, ref } from "vue";
import api from "../services/api";

const products = ref([]);
const customers = ref([]);

const loading = ref(false);
const error = ref("");
const saving = ref(false);
const successMessage = ref("");

const search = ref("");

const cart = ref([]);

const customerId = ref("");
const paymentMethod = ref("cash");
const paymentStatus = ref("paid");

const filteredProducts = computed(() => {
  if (!search.value.trim()) {
    return products.value;
  }

  const keyword = search.value.toLowerCase();

  return products.value.filter((product) =>
    product.name?.toLowerCase().includes(keyword)
  );
});

const cartTotal = computed(() => {
  return cart.value.reduce((total, item) => {
    return total + Number(item.subtotal || 0);
  }, 0);
});

const cartQuantity = computed(() => {
  return cart.value.reduce((total, item) => {
    return total + Number(item.quantity || 0);
  }, 0);
});

const formatPrice = (amount) => {
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(amount ?? 0);
};

const fetchProducts = async () => {
  try {
    const response = await api.get("/api/products");

    products.value = response.data.products?.data ?? response.data.products ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load products.";
  }
};

const fetchCustomers = async () => {
  try {
    const response = await api.get("/api/customers");

    customers.value = response.data.customers?.data ?? response.data.customers ?? [];
  } catch (err) {
    console.error(err);
  }
};

const getProductPrice = (product) => {
  return Number(product.selling_price || 0);
};

const getProductStock = (product) => {
  return Number(product.inventory?.quantity ?? product.stock_quantity ?? 0);
};

const addToCart = (product) => {
  const existingItem = cart.value.find((item) => item.product_id === product.id);

  const stock = getProductStock(product);

  if (stock <= 0) {
    error.value = "This product is out of stock.";
    return;
  }

  error.value = "";

  if (existingItem) {
    if (existingItem.quantity >= stock) {
      error.value = "You cannot add more than the available stock.";
      return;
    }

    existingItem.quantity += 1;

    existingItem.subtotal = existingItem.quantity * existingItem.selling_price;

    return;
  }

  cart.value.push({
    product_id: product.id,
    name: product.name,
    selling_price: getProductPrice(product),
    quantity: 1,
    subtotal: getProductPrice(product),
    stock: stock,
  });
};

const increaseQuantity = (item) => {
  if (item.quantity >= item.stock) {
    error.value = "You cannot exceed the available stock.";
    return;
  }

  item.quantity += 1;

  item.subtotal = item.quantity * item.selling_price;

  error.value = "";
};

const decreaseQuantity = (item) => {
  if (item.quantity <= 1) {
    removeFromCart(item);
    return;
  }

  item.quantity -= 1;

  item.subtotal = item.quantity * item.selling_price;
};

const removeFromCart = (item) => {
  cart.value = cart.value.filter((cartItem) => cartItem.product_id !== item.product_id);
};

const clearCart = () => {
  cart.value = [];
  error.value = "";
};

const completeSale = async () => {
  if (!cart.value.length) {
    error.value = "Please add at least one product to the cart.";
    return;
  }

  saving.value = true;
  error.value = "";
  successMessage.value = "";

  try {
    const payload = {
      customer_id: customerId.value || null,
      payment_method: paymentMethod.value,
      payment_status: paymentStatus.value,

      items: cart.value.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
        selling_price: item.selling_price,
      })),
    };

    const response = await api.post("/api/sales", payload);

    successMessage.value = response.data.message || "Sale completed successfully.";

    clearCart();

    customerId.value = "";
    paymentMethod.value = "cash";
    paymentStatus.value = "paid";

    await fetchProducts();
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to complete sale.";
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loading.value = true;

  Promise.all([fetchProducts(), fetchCustomers()]).finally(() => {
    loading.value = false;
  });
});
</script>

<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">Sales</h1>

      <p class="mt-1 text-sm text-gray-500">
        Create sales and process customer payments.
      </p>
    </div>

    <!-- Messages -->
    <div
      v-if="error"
      class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>

    <div
      v-if="successMessage"
      class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
    >
      {{ successMessage }}
    </div>

    <!-- Main POS -->
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
      <!-- Products -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <!-- Search -->
        <div class="border-b border-gray-100 p-4">
          <div class="relative">
            <svg
              class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>

            <input
              v-model="search"
              type="search"
              placeholder="Search products..."
              class="h-11 w-full rounded-lg border border-gray-300 pl-10 pr-4 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex min-h-96 items-center justify-center">
          <div
            class="h-8 w-8 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"
          ></div>
        </div>

        <!-- Products -->
        <div v-else class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 xl:grid-cols-4">
          <button
            v-for="product in filteredProducts"
            :key="product.id"
            type="button"
            @click="addToCart(product)"
            :disabled="getProductStock(product) <= 0"
            class="group rounded-xl border border-gray-200 bg-white p-4 text-left transition hover:border-gray-400 hover:shadow-sm disabled:cursor-not-allowed disabled:opacity-50"
          >
            <div
              class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-gray-500"
            >
              <svg
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.7"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                />
              </svg>
            </div>

            <h3 class="mt-3 line-clamp-2 text-sm font-semibold text-gray-900">
              {{ product.name }}
            </h3>

            <p class="mt-2 text-sm font-bold text-gray-900">
              {{ formatPrice(product.selling_price) }}
            </p>

            <p
              class="mt-1 text-xs"
              :class="getProductStock(product) > 0 ? 'text-gray-500' : 'text-red-500'"
            >
              {{
                getProductStock(product) > 0
                  ? `${getProductStock(product)} in stock`
                  : "Out of stock"
              }}
            </p>
          </button>
        </div>

        <!-- Empty -->
        <div
          v-if="!loading && filteredProducts.length === 0"
          class="flex min-h-80 flex-col items-center justify-center px-6 text-center"
        >
          <div
            class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100"
          >
            <span class="text-gray-400"> □ </span>
          </div>

          <h3 class="mt-4 text-sm font-semibold text-gray-900">No products found</h3>

          <p class="mt-1 text-sm text-gray-500">Try searching for another product.</p>
        </div>
      </div>

      <!-- Cart -->
      <div
        class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white"
      >
        <!-- Cart header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <div>
            <h2 class="font-semibold text-gray-900">Current Sale</h2>

            <p class="mt-1 text-xs text-gray-500">{{ cartQuantity }} item(s)</p>
          </div>

          <button
            v-if="cart.length"
            type="button"
            @click="clearCart"
            class="text-xs font-medium text-red-600 hover:text-red-700"
          >
            Clear
          </button>
        </div>

        <!-- Cart items -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="cart.length" class="divide-y divide-gray-100">
            <div v-for="item in cart" :key="item.product_id" class="p-4">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <h3 class="truncate text-sm font-semibold text-gray-900">
                    {{ item.name }}
                  </h3>

                  <p class="mt-1 text-xs text-gray-500">
                    {{ formatPrice(item.selling_price) }}
                    each
                  </p>
                </div>

                <button
                  type="button"
                  @click="removeFromCart(item)"
                  class="text-xs text-gray-400 hover:text-red-600"
                >
                  Remove
                </button>
              </div>

              <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center rounded-lg border border-gray-200">
                  <button
                    type="button"
                    @click="decreaseQuantity(item)"
                    class="h-8 w-8 text-gray-600 hover:bg-gray-50"
                  >
                    −
                  </button>

                  <span class="w-8 text-center text-sm font-semibold">
                    {{ item.quantity }}
                  </span>

                  <button
                    type="button"
                    @click="increaseQuantity(item)"
                    class="h-8 w-8 text-gray-600 hover:bg-gray-50"
                  >
                    +
                  </button>
                </div>

                <span class="text-sm font-bold text-gray-900">
                  {{ formatPrice(item.subtotal) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Empty cart -->
          <div
            v-else
            class="flex min-h-64 flex-col items-center justify-center px-6 text-center"
          >
            <div
              class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100"
            >
              <svg
                class="h-6 w-6 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.7"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 4h13M9 21h.01M19 21h.01"
                />
              </svg>
            </div>

            <h3 class="mt-4 text-sm font-semibold text-gray-900">Your cart is empty</h3>

            <p class="mt-1 text-sm text-gray-500">Select a product to begin a sale.</p>
          </div>
        </div>

        <!-- Checkout -->
        <div class="border-t border-gray-100 p-5">
          <!-- Customer -->
          <div class="mb-4">
            <label
              class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Customer
            </label>

            <select
              v-model="customerId"
              class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-gray-500"
            >
              <option value="">Walk-in Customer</option>

              <option
                v-for="customer in customers"
                :key="customer.id"
                :value="customer.id"
              >
                {{ customer.customer_name }}
              </option>
            </select>
          </div>

          <!-- Payment method -->
          <div class="mb-4">
            <label
              class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500"
            >
              Payment Method
            </label>

            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="method in [
                  {
                    value: 'cash',
                    label: 'Cash',
                  },
                  {
                    value: 'mpesa',
                    label: 'M-Pesa',
                  },
                  {
                    value: 'card',
                    label: 'Card',
                  },
                ]"
                :key="method.value"
                type="button"
                @click="paymentMethod = method.value"
                class="rounded-lg border px-3 py-2.5 text-xs font-semibold transition"
                :class="
                  paymentMethod === method.value
                    ? 'border-gray-900 bg-gray-900 text-white'
                    : 'border-gray-200 text-gray-600 hover:bg-gray-50'
                "
              >
                {{ method.label }}
              </button>
            </div>
          </div>

          <!-- Total -->
          <div class="mb-4 rounded-xl bg-gray-50 p-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-500"> Total </span>

              <span class="text-2xl font-bold text-gray-900">
                {{ formatPrice(cartTotal) }}
              </span>
            </div>
          </div>

          <!-- Complete -->
          <button
            type="button"
            @click="completeSale"
            :disabled="saving || cart.length === 0"
            class="w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ saving ? "Processing Sale..." : "Complete Sale" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
