<script setup>
import { onMounted, ref } from "vue";
import api from "../services/api";

const sales = ref([]);
const loading = ref(false);
const error = ref("");

const search = ref("");
const paymentStatus = ref("");

const selectedSale = ref(null);
const showDetails = ref(false);

const fetchSales = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/sales", {
      params: {
        search: search.value || undefined,
        payment_status: paymentStatus.value || undefined,
      },
    });

    sales.value = response.data.sales?.data ?? response.data.sales ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load sales.";
  } finally {
    loading.value = false;
  }
};

const viewSale = async (sale) => {
  try {
    const response = await api.get(`/api/sales/${sale.id}`);

    selectedSale.value = response.data.sale;
    showDetails.value = true;
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load sale details.";
  }
};

const closeDetails = () => {
  showDetails.value = false;
  selectedSale.value = null;
};

const formatPrice = (amount) => {
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
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

const paymentBadge = (status) => {
  const badges = {
    paid: "bg-emerald-50 text-emerald-700",
    partial: "bg-amber-50 text-amber-700",
    pending: "bg-red-50 text-red-700",
  };

  return badges[status] || "bg-gray-100 text-gray-600";
};

const paymentMethodLabel = (method) => {
  const methods = {
    cash: "Cash",
    mpesa: "M-Pesa",
    card: "Card",
  };

  return methods[method] || method;
};

onMounted(() => {
  fetchSales();
});
</script>

<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">Sales History</h1>

      <p class="mt-1 text-sm text-gray-500">View and review completed sales.</p>
    </div>

    <!-- Error -->
    <div
      v-if="error"
      class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>

    <!-- Sales -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
      <!-- Toolbar -->
      <div
        class="flex flex-col gap-4 border-b border-gray-100 p-4 md:flex-row md:items-center md:justify-between"
      >
        <div class="relative w-full md:max-w-sm">
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
            @keyup.enter="fetchSales"
            type="search"
            placeholder="Search invoice or customer..."
            class="h-10 w-full rounded-lg border border-gray-300 pl-9 pr-4 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
          />
        </div>

        <div class="flex gap-2">
          <select
            v-model="paymentStatus"
            @change="fetchSales"
            class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-gray-500"
          >
            <option value="">All Status</option>

            <option value="paid">Paid</option>

            <option value="partial">Partial</option>

            <option value="pending">Pending</option>
          </select>

          <button
            @click="fetchSales"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
          >
            Search
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex min-h-64 items-center justify-center">
        <div
          class="h-7 w-7 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"
        ></div>
      </div>

      <!-- Table -->
      <div v-else-if="sales.length" class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Invoice
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Customer
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Date
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Payment
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Amount
              </th>

              <th
                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Action
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            <tr v-for="sale in sales" :key="sale.id" class="transition hover:bg-gray-50">
              <td class="px-6 py-4">
                <span class="text-sm font-semibold text-gray-900">
                  {{ sale.invoice_number }}
                </span>
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ sale.customer?.customer_name || "Walk-in Customer" }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ formatDate(sale.sale_date) }}
              </td>

              <td class="px-6 py-4">
                <div class="flex flex-col gap-1">
                  <span class="text-sm font-medium text-gray-700">
                    {{ paymentMethodLabel(sale.payment_method) }}
                  </span>

                  <span
                    class="w-fit rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                    :class="paymentBadge(sale.payment_status)"
                  >
                    {{ sale.payment_status }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4 text-sm font-bold text-gray-900">
                {{ formatPrice(sale.total_amount) }}
              </td>

              <td class="px-6 py-4 text-right">
                <button
                  @click="viewSale(sale)"
                  class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                >
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty -->
      <div
        v-else
        class="flex min-h-64 flex-col items-center justify-center px-6 text-center"
      >
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
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
              d="M9 14l2 2 4-4m6 0a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>

        <h3 class="mt-4 text-sm font-semibold text-gray-900">No sales found</h3>

        <p class="mt-1 text-sm text-gray-500">Completed sales will appear here.</p>
      </div>
    </div>

    <!-- Sale Details Modal -->
    <div
      v-if="showDetails && selectedSale"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 px-4 py-6"
    >
      <div
        class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
      >
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-5">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Sale Details</h2>

            <p class="mt-1 text-sm text-gray-500">
              {{ selectedSale.invoice_number }}
            </p>
          </div>

          <button
            @click="closeDetails"
            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100"
          >
            ✕
          </button>
        </div>

        <div class="space-y-6 p-6">
          <!-- Information -->
          <div class="grid gap-4 sm:grid-cols-3">
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-400">Customer</p>

              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ selectedSale.customer?.customer_name || "Walk-in Customer" }}
              </p>
            </div>

            <div>
              <p class="text-xs uppercase tracking-wide text-gray-400">Payment</p>

              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ paymentMethodLabel(selectedSale.payment_method) }}
              </p>
            </div>

            <div>
              <p class="text-xs uppercase tracking-wide text-gray-400">Date</p>

              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ formatDate(selectedSale.sale_date) }}
              </p>
            </div>
          </div>

          <!-- Items -->
          <div>
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Items</h3>

            <div class="overflow-hidden rounded-xl border border-gray-200">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">
                      Product
                    </th>

                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500">
                      Qty
                    </th>

                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">
                      Price
                    </th>

                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">
                      Subtotal
                    </th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                  <tr
                    v-for="item in selectedSale.sale_items ||
                    selectedSale.saleItems ||
                    []"
                    :key="item.id"
                  >
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                      {{ item.product?.name || "Product" }}
                    </td>

                    <td class="px-4 py-3 text-center text-sm text-gray-600">
                      {{ item.quantity }}
                    </td>

                    <td class="px-4 py-3 text-right text-sm text-gray-600">
                      {{ formatPrice(item.selling_price) }}
                    </td>

                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                      {{ formatPrice(item.subtotal) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Total -->
          <div class="flex items-center justify-between rounded-xl bg-gray-50 p-5">
            <span class="text-sm font-medium text-gray-500"> Total </span>

            <span class="text-2xl font-bold text-gray-900">
              {{ formatPrice(selectedSale.total_amount) }}
            </span>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end border-t border-gray-100 px-6 py-4">
          <button
            @click="closeDetails"
            class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
