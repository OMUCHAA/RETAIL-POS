<script setup>
import { computed, onMounted, ref } from "vue";
import api from "../services/api";

const purchases = ref([]);
const suppliers = ref([]);
const products = ref([]);

const loading = ref(false);
const error = ref("");

const search = ref("");
const paymentStatus = ref("");

const showModal = ref(false);
const saving = ref(false);
const formError = ref("");

const form = ref({
  supplier_id: "",
  purchase_date: new Date().toISOString().split("T")[0],
  invoice_number: "",
  payment_status: "paid",
  remarks: "",
  items: [
    {
      product_id: "",
      quantity: 1,
      buying_price: "",
    },
  ],
});

const filteredPurchases = computed(() => {
  let result = purchases.value;

  if (search.value.trim()) {
    const keyword = search.value.toLowerCase();

    result = result.filter(
      (purchase) =>
        purchase.invoice_number?.toLowerCase().includes(keyword) ||
        purchase.supplier?.supplier_name?.toLowerCase().includes(keyword)
    );
  }

  if (paymentStatus.value) {
    result = result.filter((purchase) => purchase.payment_status === paymentStatus.value);
  }

  return result;
});

const purchaseTotal = computed(() => {
  return form.value.items.reduce((total, item) => {
    const quantity = Number(item.quantity) || 0;
    const price = Number(item.buying_price) || 0;

    return total + quantity * price;
  }, 0);
});

const formatPrice = (amount) => {
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(amount ?? 0);
};

const fetchPurchases = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/purchases");

    purchases.value = response.data.purchases?.data ?? response.data.purchases ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load purchases.";
  } finally {
    loading.value = false;
  }
};

const fetchSuppliers = async () => {
  try {
    const response = await api.get("/api/suppliers");

    suppliers.value = response.data.suppliers?.data ?? response.data.suppliers ?? [];
  } catch (err) {
    console.error(err);
  }
};

const fetchProducts = async () => {
  try {
    const response = await api.get("/api/products");

    products.value = response.data.products?.data ?? response.data.products ?? [];
  } catch (err) {
    console.error(err);
  }
};

const resetForm = () => {
  form.value = {
    supplier_id: "",
    purchase_date: new Date().toISOString().split("T")[0],
    invoice_number: "",
    payment_status: "paid",
    remarks: "",
    items: [
      {
        product_id: "",
        quantity: 1,
        buying_price: "",
      },
    ],
  };

  formError.value = "";
};

const openCreateModal = () => {
  resetForm();
  showModal.value = true;
};

const closeModal = () => {
  if (saving.value) return;

  showModal.value = false;
};

const addItem = () => {
  form.value.items.push({
    product_id: "",
    quantity: 1,
    buying_price: "",
  });
};

const removeItem = (index) => {
  if (form.value.items.length === 1) return;

  form.value.items.splice(index, 1);
};

const savePurchase = async () => {
  saving.value = true;
  formError.value = "";

  try {
    await api.post("/api/purchases", form.value);

    showModal.value = false;

    await fetchPurchases();
  } catch (err) {
    formError.value = err.response?.data?.message || "Unable to save purchase.";
  } finally {
    saving.value = false;
  }
};

const paymentBadge = (status) => {
  const badges = {
    paid: "bg-emerald-50 text-emerald-700",
    partial: "bg-amber-50 text-amber-700",
    pending: "bg-red-50 text-red-700",
  };

  return badges[status] || "bg-gray-100 text-gray-600";
};

onMounted(() => {
  fetchPurchases();
  fetchSuppliers();
  fetchProducts();
});
</script>

<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Purchases</h1>

        <p class="mt-1 text-sm text-gray-500">
          Record stock purchases from your suppliers.
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
      >
        <span class="text-lg leading-none">+</span>
        New Purchase
      </button>
    </div>

    <!-- Error -->
    <div
      v-if="error"
      class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>

    <!-- Summary -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Total Purchases</p>

        <p class="mt-2 text-2xl font-bold text-gray-900">
          {{ purchases.length }}
        </p>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Total Purchase Value</p>

        <p class="mt-2 text-2xl font-bold text-gray-900">
          {{
            formatPrice(
              purchases.reduce(
                (total, purchase) => total + Number(purchase.total_amount || 0),
                0
              )
            )
          }}
        </p>
      </div>
    </div>

    <!-- Purchases -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
      <!-- Toolbar -->
      <div
        class="flex flex-col gap-4 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="relative w-full sm:max-w-sm">
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
            placeholder="Search invoice or supplier..."
            class="h-10 w-full rounded-lg border border-gray-300 pl-9 pr-4 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
          />
        </div>

        <select
          v-model="paymentStatus"
          class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
        >
          <option value="">All Payment Status</option>

          <option value="paid">Paid</option>

          <option value="partial">Partial</option>

          <option value="pending">Pending</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex min-h-64 items-center justify-center">
        <div class="text-center">
          <div
            class="mx-auto h-7 w-7 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"
          ></div>

          <p class="mt-3 text-sm text-gray-500">Loading purchases...</p>
        </div>
      </div>

      <!-- Table -->
      <div v-else-if="filteredPurchases.length" class="overflow-x-auto">
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
                Supplier
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Date
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Amount
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Status
              </th>

              <th
                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Action
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="purchase in filteredPurchases"
              :key="purchase.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <span class="text-sm font-semibold text-gray-900">
                  {{ purchase.invoice_number }}
                </span>
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ purchase.supplier?.supplier_name || "—" }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ purchase.purchase_date }}
              </td>

              <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                {{ formatPrice(purchase.total_amount) }}
              </td>

              <td class="px-6 py-4">
                <span
                  class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                  :class="paymentBadge(purchase.payment_status)"
                >
                  {{ purchase.payment_status }}
                </span>
              </td>

              <td class="px-6 py-4 text-right">
                <button
                  class="rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100"
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
          <span class="text-xl text-gray-400"> $ </span>
        </div>

        <h3 class="mt-4 text-sm font-semibold text-gray-900">No purchases found</h3>

        <p class="mt-1 text-sm text-gray-500">
          Record your first purchase to see it here.
        </p>
      </div>
    </div>

    <!-- Create Purchase Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 px-4 py-6"
    >
      <div
        class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
          <div>
            <h2 class="text-lg font-bold text-gray-900">New Purchase</h2>

            <p class="mt-1 text-xs text-gray-500">
              Record products received from a supplier.
            </p>
          </div>

          <button
            @click="closeModal"
            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="savePurchase" class="space-y-6 p-6">
          <!-- Error -->
          <div
            v-if="formError"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ formError }}
          </div>

          <!-- Purchase information -->
          <div class="grid gap-5 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">
                Supplier
              </label>

              <select
                v-model="form.supplier_id"
                required
                class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
              >
                <option value="">Select supplier</option>

                <option
                  v-for="supplier in suppliers"
                  :key="supplier.id"
                  :value="supplier.id"
                >
                  {{ supplier.supplier_name }}
                </option>
              </select>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">
                Purchase date
              </label>

              <input
                v-model="form.purchase_date"
                type="date"
                required
                class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700">
                Invoice number
              </label>

              <input
                v-model="form.invoice_number"
                type="text"
                required
                placeholder="INV-001"
                class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
              />
            </div>
          </div>

          <!-- Items -->
          <div>
            <div class="mb-3 flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold text-gray-900">Purchase Items</h3>

                <p class="mt-1 text-xs text-gray-500">Add each product received.</p>
              </div>

              <button
                type="button"
                @click="addItem"
                class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
              >
                + Add Item
              </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
              <table class="w-full min-w-[700px]">
                <thead>
                  <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">
                      Product
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">
                      Quantity
                    </th>

                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">
                      Buying Price
                    </th>

                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500">
                      Subtotal
                    </th>

                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(item, index) in form.items" :key="index">
                    <td class="px-4 py-3">
                      <select
                        v-model="item.product_id"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-gray-500"
                      >
                        <option value="">Select product</option>

                        <option
                          v-for="product in products"
                          :key="product.id"
                          :value="product.id"
                        >
                          {{ product.name }}
                        </option>
                      </select>
                    </td>

                    <td class="px-4 py-3">
                      <input
                        v-model="item.quantity"
                        type="number"
                        min="1"
                        required
                        class="h-10 w-24 rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500"
                      />
                    </td>

                    <td class="px-4 py-3">
                      <input
                        v-model="item.buying_price"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        class="h-10 w-32 rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500"
                      />
                    </td>

                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                      {{
                        formatPrice(
                          (Number(item.quantity) || 0) * (Number(item.buying_price) || 0)
                        )
                      }}
                    </td>

                    <td class="px-4 py-3 text-right">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        :disabled="form.items.length === 1"
                        class="rounded-lg p-2 text-red-500 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-30"
                      >
                        ✕
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Bottom information -->
          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-5">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                  Payment status
                </label>

                <select
                  v-model="form.payment_status"
                  required
                  class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-gray-500"
                >
                  <option value="paid">Paid</option>

                  <option value="partial">Partial</option>

                  <option value="pending">Pending</option>
                </select>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">
                  Remarks
                </label>

                <textarea
                  v-model="form.remarks"
                  rows="3"
                  placeholder="Optional remarks..."
                  class="w-full rounded-lg border border-gray-300 px-3 py-3 text-sm outline-none focus:border-gray-500"
                ></textarea>
              </div>
            </div>

            <!-- Total -->
            <div class="flex items-end justify-end">
              <div class="w-full rounded-xl bg-gray-50 p-5 md:max-w-sm">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-500"> Purchase Total </span>

                  <span class="text-xl font-bold text-gray-900">
                    {{ formatPrice(purchaseTotal) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <button
              type="button"
              @click="closeModal"
              class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>

            <button
              type="submit"
              :disabled="saving"
              class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ saving ? "Saving Purchase..." : "Save Purchase" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
