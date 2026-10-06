<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";

const products = ref([]);
const customers = ref([]);
const sales = ref([]);

const loading = ref(true);
const error = ref("");

const lowStockThreshold = 5;

// ------------------------------------
// Fetch dashboard data
// ------------------------------------

const fetchDashboardData = async () => {
  loading.value = true;
  error.value = "";

  try {
    const [productsResponse, customersResponse, salesResponse] = await Promise.all([
      api.get("/api/products"),
      api.get("/api/customers"),
      api.get("/api/sales"),
    ]);

    products.value =
      productsResponse.data.products?.data ??
      productsResponse.data.products ??
      productsResponse.data.data ??
      [];

    customers.value =
      customersResponse.data.customers?.data ??
      customersResponse.data.customers ??
      customersResponse.data.data ??
      [];

    sales.value =
      salesResponse.data.sales?.data ??
      salesResponse.data.sales ??
      salesResponse.data.data ??
      [];
  } catch (err) {
    console.error(err);

    error.value = err.response?.data?.message || "Failed to load dashboard data.";
  } finally {
    loading.value = false;
  }
};

// ------------------------------------
// Helpers
// ------------------------------------

const getStock = (product) => {
  return Number(product.inventory?.quantity ?? product.stock_quantity ?? 0);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
  }).format(amount ?? 0);
};

const formatDate = (date) => {
  if (!date) return "—";

  return new Date(date).toLocaleDateString("en-KE", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

// ------------------------------------
// Statistics
// ------------------------------------

const totalProducts = computed(() => {
  return products.value.length;
});

const totalCustomers = computed(() => {
  return customers.value.length;
});

const totalSales = computed(() => {
  return sales.value.reduce((total, sale) => {
    return total + Number(sale.total_amount ?? 0);
  }, 0);
});

const lowStockProducts = computed(() => {
  return products.value.filter((product) => {
    const stock = getStock(product);

    return stock > 0 && stock <= lowStockThreshold;
  });
});

const outOfStockProducts = computed(() => {
  return products.value.filter((product) => {
    return getStock(product) === 0;
  });
});

// ------------------------------------
// Today's sales
// ------------------------------------

const todaySales = computed(() => {
  const today = new Date();

  return sales.value
    .filter((sale) => {
      if (!sale.sale_date) return false;

      const saleDate = new Date(sale.sale_date);

      return (
        saleDate.getDate() === today.getDate() &&
        saleDate.getMonth() === today.getMonth() &&
        saleDate.getFullYear() === today.getFullYear()
      );
    })
    .reduce((total, sale) => {
      return total + Number(sale.total_amount ?? 0);
    }, 0);
});

// ------------------------------------
// Recent sales
// ------------------------------------

const recentSales = computed(() => {
  return [...sales.value]
    .sort((a, b) => {
      return new Date(b.sale_date) - new Date(a.sale_date);
    })
    .slice(0, 5);
});

// ------------------------------------
// Recent sales chart
// ------------------------------------

const salesByDay = computed(() => {
  const days = [];

  for (let i = 6; i >= 0; i--) {
    const date = new Date();

    date.setDate(date.getDate() - i);

    const amount = sales.value
      .filter((sale) => {
        if (!sale.sale_date) return false;

        const saleDate = new Date(sale.sale_date);

        return (
          saleDate.getDate() === date.getDate() &&
          saleDate.getMonth() === date.getMonth() &&
          saleDate.getFullYear() === date.getFullYear()
        );
      })
      .reduce((total, sale) => {
        return total + Number(sale.total_amount ?? 0);
      }, 0);

    days.push({
      label: date.toLocaleDateString("en-US", {
        weekday: "short",
      }),
      amount,
    });
  }

  return days;
});

const maxDailySales = computed(() => {
  return Math.max(...salesByDay.value.map((day) => day.amount), 1);
});

onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <div class="p-4 md:p-6 space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard</h1>

      <p class="text-gray-500 mt-1">Overview of your business performance.</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-gray-500">Loading dashboard...</div>

    <template v-else>
      <!-- Main Statistics -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today's Sales -->
        <div class="bg-white border rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500">Today's Sales</p>

          <h2 class="text-xl md:text-2xl font-bold text-gray-800 mt-2">
            {{ formatCurrency(todaySales) }}
          </h2>
        </div>

        <!-- Total Sales -->
        <div class="bg-white border rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500">Total Sales</p>

          <h2 class="text-xl md:text-2xl font-bold text-gray-800 mt-2">
            {{ formatCurrency(totalSales) }}
          </h2>
        </div>

        <!-- Products -->
        <div class="bg-white border rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500">Products</p>

          <h2 class="text-xl md:text-2xl font-bold text-gray-800 mt-2">
            {{ totalProducts }}
          </h2>
        </div>

        <!-- Customers -->
        <div class="bg-white border rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500">Customers</p>

          <h2 class="text-xl md:text-2xl font-bold text-gray-800 mt-2">
            {{ totalCustomers }}
          </h2>
        </div>
      </div>

      <!-- Sales Overview -->
      <div class="bg-white border rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="font-bold text-gray-800">Sales Overview</h2>

            <p class="text-sm text-gray-500">Sales performance over the last 7 days</p>
          </div>
        </div>

        <div class="h-64 flex items-end gap-3 md:gap-6">
          <div
            v-for="day in salesByDay"
            :key="day.label"
            class="flex-1 h-full flex flex-col justify-end items-center"
          >
            <!-- Amount -->
            <p class="text-xs text-gray-500 mb-2 hidden md:block">
              {{ day.amount > 0 ? formatCurrency(day.amount) : "" }}
            </p>

            <!-- Bar -->
            <div
              class="w-full max-w-12 bg-yellow-500 rounded-t-md transition-all"
              :style="{
                height: `${Math.max(
                  (day.amount / maxDailySales) * 85,
                  day.amount > 0 ? 5 : 0
                )}%`,
              }"
            ></div>

            <!-- Day -->
            <p class="text-xs text-gray-500 mt-2">
              {{ day.label }}
            </p>
          </div>
        </div>
      </div>

      <!-- Lower Section -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white border rounded-xl shadow-sm">
          <div class="p-5 border-b">
            <h2 class="font-bold text-gray-800">Recent Sales</h2>

            <p class="text-sm text-gray-500">Latest transactions</p>
          </div>

          <div class="divide-y">
            <div
              v-for="sale in recentSales"
              :key="sale.id"
              class="p-4 flex items-center justify-between gap-4"
            >
              <div>
                <p class="font-medium text-gray-800">
                  {{ sale.invoice_number }}
                </p>

                <p class="text-xs text-gray-500 mt-1">
                  {{ formatDate(sale.sale_date) }}
                </p>
              </div>

              <div class="text-right">
                <p class="font-semibold text-gray-800">
                  {{ formatCurrency(sale.total_amount) }}
                </p>

                <p class="text-xs text-gray-500 capitalize">
                  {{ sale.payment_method }}
                </p>
              </div>
            </div>

            <div v-if="recentSales.length === 0" class="p-8 text-center text-gray-500">
              No sales recorded yet.
            </div>
          </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white border rounded-xl shadow-sm">
          <div class="p-5 border-b">
            <h2 class="font-bold text-gray-800">Stock Alerts</h2>

            <p class="text-sm text-gray-500">Products requiring attention</p>
          </div>

          <div class="divide-y">
            <div
              v-for="product in [...outOfStockProducts, ...lowStockProducts].slice(0, 5)"
              :key="product.id"
              class="p-4 flex items-center justify-between gap-4"
            >
              <div>
                <p class="font-medium text-gray-800">
                  {{ product.name }}
                </p>

                <p class="text-xs text-gray-500 mt-1">SKU: {{ product.sku || "—" }}</p>
              </div>

              <div class="text-right">
                <p
                  class="font-bold"
                  :class="getStock(product) === 0 ? 'text-red-600' : 'text-yellow-600'"
                >
                  {{ getStock(product) }}
                </p>

                <p class="text-xs text-gray-500">units left</p>
              </div>
            </div>

            <div
              v-if="lowStockProducts.length === 0 && outOfStockProducts.length === 0"
              class="p-8 text-center text-gray-500"
            >
              All products have healthy stock levels.
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
