<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";

const products = ref([]);
const search = ref("");
const loading = ref(false);
const error = ref("");

const lowStockThreshold = 5;

// Fetch products
const fetchInventory = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/products");

    products.value =
      response.data.products?.data ?? response.data.products ?? response.data.data ?? [];
  } catch (err) {
    console.error(err);

    error.value = err.response?.data?.message || "Failed to load inventory.";
  } finally {
    loading.value = false;
  }
};

// Get stock quantity
const getStock = (product) => {
  return Number(product.inventory?.quantity ?? product.stock_quantity ?? 0);
};

// Filter products
const filteredProducts = computed(() => {
  const term = search.value.toLowerCase().trim();

  if (!term) {
    return products.value;
  }

  return products.value.filter((product) => {
    return (
      product.name?.toLowerCase().includes(term) ||
      product.sku?.toLowerCase().includes(term)
    );
  });
});

// Inventory statistics
const totalProducts = computed(() => {
  return products.value.length;
});

const totalUnits = computed(() => {
  return products.value.reduce((total, product) => {
    return total + getStock(product);
  }, 0);
});

const lowStockProducts = computed(() => {
  return products.value.filter((product) => {
    const stock = getStock(product);

    return stock > 0 && stock <= lowStockThreshold;
  }).length;
});

const outOfStockProducts = computed(() => {
  return products.value.filter((product) => {
    return getStock(product) === 0;
  }).length;
});

// Stock status
const stockStatus = (product) => {
  const stock = getStock(product);

  if (stock === 0) {
    return {
      text: "Out of Stock",
      class: "bg-red-100 text-red-700",
    };
  }

  if (stock <= lowStockThreshold) {
    return {
      text: "Low Stock",
      class: "bg-yellow-100 text-yellow-700",
    };
  }

  return {
    text: "In Stock",
    class: "bg-green-100 text-green-700",
  };
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
  }).format(amount ?? 0);
};

onMounted(() => {
  fetchInventory();
});
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Inventory</h1>

      <p class="text-gray-500 mt-1">Monitor your current stock levels.</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Products -->
      <div class="bg-white rounded-xl shadow-sm border p-4">
        <p class="text-sm text-gray-500">Total Products</p>

        <h2 class="text-2xl font-bold text-gray-800 mt-2">
          {{ totalProducts }}
        </h2>
      </div>

      <!-- Total Units -->
      <div class="bg-white rounded-xl shadow-sm border p-4">
        <p class="text-sm text-gray-500">Total Units</p>

        <h2 class="text-2xl font-bold text-gray-800 mt-2">
          {{ totalUnits }}
        </h2>
      </div>

      <!-- Low Stock -->
      <div class="bg-white rounded-xl shadow-sm border p-4">
        <p class="text-sm text-gray-500">Low Stock</p>

        <h2 class="text-2xl font-bold text-yellow-600 mt-2">
          {{ lowStockProducts }}
        </h2>
      </div>

      <!-- Out of Stock -->
      <div class="bg-white rounded-xl shadow-sm border p-4">
        <p class="text-sm text-gray-500">Out of Stock</p>

        <h2 class="text-2xl font-bold text-red-600 mt-2">
          {{ outOfStockProducts }}
        </h2>
      </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm border p-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search product by name or SKU..."
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-500"
      />
    </div>

    <!-- Error -->
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-10 text-gray-500">Loading inventory...</div>

    <!-- Inventory Table -->
    <div v-else class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                Product
              </th>

              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">SKU</th>

              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                Buying Price
              </th>

              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                Selling Price
              </th>

              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                Stock
              </th>

              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                Status
              </th>
            </tr>
          </thead>

          <tbody class="divide-y">
            <tr
              v-for="product in filteredProducts"
              :key="product.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <div class="font-medium text-gray-800">
                  {{ product.name }}
                </div>
              </td>

              <td class="px-6 py-4 text-gray-500">
                {{ product.sku || "—" }}
              </td>

              <td class="px-6 py-4 text-gray-700">
                {{ formatCurrency(product.buying_price) }}
              </td>

              <td class="px-6 py-4 text-gray-700">
                {{ formatCurrency(product.selling_price) }}
              </td>

              <td class="px-6 py-4">
                <span class="font-semibold text-gray-800">
                  {{ getStock(product) }}
                </span>
              </td>

              <td class="px-6 py-4">
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="stockStatus(product).class"
                >
                  {{ stockStatus(product).text }}
                </span>
              </td>
            </tr>

            <!-- Empty -->
            <tr v-if="filteredProducts.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-500">
                No products found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="md:hidden divide-y">
        <div v-for="product in filteredProducts" :key="product.id" class="p-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="font-semibold text-gray-800">
                {{ product.name }}
              </h3>

              <p class="text-sm text-gray-500 mt-1">SKU: {{ product.sku || "—" }}</p>
            </div>

            <span
              class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
              :class="stockStatus(product).class"
            >
              {{ stockStatus(product).text }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
              <p class="text-xs text-gray-500">Buying Price</p>

              <p class="font-medium text-gray-700">
                {{ formatCurrency(product.buying_price) }}
              </p>
            </div>

            <div>
              <p class="text-xs text-gray-500">Selling Price</p>

              <p class="font-medium text-gray-700">
                {{ formatCurrency(product.selling_price) }}
              </p>
            </div>

            <div>
              <p class="text-xs text-gray-500">Current Stock</p>

              <p class="text-lg font-bold text-gray-800">
                {{ getStock(product) }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="filteredProducts.length === 0" class="p-10 text-center text-gray-500">
          No products found.
        </div>
      </div>
    </div>
  </div>
</template>
