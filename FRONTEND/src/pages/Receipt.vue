<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../services/api";

const route = useRoute();
const router = useRouter();

const sale = ref(null);
const loading = ref(false);
const error = ref("");

const fetchSale = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get(`/api/sales/${route.params.id}`);

    sale.value = response.data.sale;
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load receipt.";
  } finally {
    loading.value = false;
  }
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

const formatTime = (date) => {
  if (!date) return "—";

  return new Date(date).toLocaleTimeString("en-KE", {
    hour: "2-digit",
    minute: "2-digit",
  });
};

const paymentMethodLabel = (method) => {
  const methods = {
    cash: "Cash",
    mpesa: "M-Pesa",
    card: "Card",
  };

  return methods[method] || method;
};

const printReceipt = () => {
  window.print();
};

const goBack = () => {
  router.push("/sales-history");
};

onMounted(() => {
  fetchSale();
});
</script>

<template>
  <div class="min-h-full bg-gray-50 py-8">
    <!-- Loading -->
    <div v-if="loading" class="flex min-h-96 items-center justify-center">
      <div class="text-center">
        <div
          class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"
        ></div>

        <p class="mt-3 text-sm text-gray-500">Loading receipt...</p>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="mx-auto max-w-lg px-4">
      <div
        class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
      >
        {{ error }}
      </div>
    </div>

    <!-- Receipt -->
    <div v-else-if="sale">
      <!-- Actions -->
      <div
        class="receipt-actions mx-auto mb-5 flex max-w-3xl items-center justify-between px-4"
      >
        <button
          @click="goBack"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          ← Back to Sales
        </button>

        <button
          @click="printReceipt"
          class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
        >
          Print Receipt
        </button>
      </div>

      <!-- Receipt paper -->
      <div id="receipt" class="mx-auto w-full max-w-3xl bg-white shadow-sm">
        <div class="px-8 py-8 sm:px-12">
          <!-- Business header -->
          <div class="text-center">
            <div
              class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-gray-900 text-lg font-bold text-white"
            >
              RP
            </div>

            <h1 class="mt-4 text-xl font-bold text-gray-900">Retail POS</h1>

            <p class="mt-1 text-sm text-gray-500">Management System</p>

            <p class="mt-3 text-xs text-gray-400">Thank you for your business</p>
          </div>

          <!-- Divider -->
          <div class="my-6 border-t border-dashed border-gray-300"></div>

          <!-- Sale information -->
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-400">Invoice</p>

              <p class="mt-1 font-semibold text-gray-900">
                {{ sale.invoice_number }}
              </p>
            </div>

            <div class="text-right">
              <p class="text-xs uppercase tracking-wide text-gray-400">Date</p>

              <p class="mt-1 font-semibold text-gray-900">
                {{ formatDate(sale.sale_date) }}
              </p>
            </div>

            <div>
              <p class="text-xs uppercase tracking-wide text-gray-400">Customer</p>

              <p class="mt-1 font-semibold text-gray-900">
                {{ sale.customer?.customer_name || "Walk-in Customer" }}
              </p>
            </div>

            <div class="text-right">
              <p class="text-xs uppercase tracking-wide text-gray-400">Time</p>

              <p class="mt-1 font-semibold text-gray-900">
                {{ formatTime(sale.created_at) }}
              </p>
            </div>
          </div>

          <!-- Items -->
          <div class="my-6 border-y border-gray-200 py-5">
            <div
              class="mb-3 grid grid-cols-[1fr_auto_auto] gap-4 text-xs font-semibold uppercase tracking-wide text-gray-400"
            >
              <span>Item</span>
              <span>Qty</span>
              <span class="text-right"> Amount </span>
            </div>

            <div
              v-for="item in sale.sale_items || sale.saleItems || []"
              :key="item.id"
              class="grid grid-cols-[1fr_auto_auto] gap-4 py-2 text-sm"
            >
              <div class="min-w-0">
                <p class="font-medium text-gray-900">
                  {{ item.product?.name || "Product" }}
                </p>

                <p class="mt-0.5 text-xs text-gray-400">
                  {{ formatPrice(item.selling_price) }}
                  each
                </p>
              </div>

              <span class="text-gray-600">
                {{ item.quantity }}
              </span>

              <span class="text-right font-medium text-gray-900">
                {{ formatPrice(item.subtotal) }}
              </span>
            </div>
          </div>

          <!-- Totals -->
          <div class="space-y-3">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500"> Subtotal </span>

              <span class="font-medium text-gray-900">
                {{ formatPrice(sale.total_amount) }}
              </span>
            </div>

            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500"> Payment Method </span>

              <span class="font-medium text-gray-900">
                {{ paymentMethodLabel(sale.payment_method) }}
              </span>
            </div>

            <div class="border-t border-gray-200 pt-4">
              <div class="flex items-center justify-between">
                <span class="text-base font-bold text-gray-900"> TOTAL </span>

                <span class="text-2xl font-bold text-gray-900">
                  {{ formatPrice(sale.total_amount) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="mt-8 border-t border-dashed border-gray-300 pt-6 text-center">
            <p class="text-sm font-medium text-gray-700">
              Thank you for shopping with us!
            </p>

            <p class="mt-2 text-xs text-gray-400">
              Invoice:
              {{ sale.invoice_number }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  body {
    background: white !important;
  }

  .receipt-actions {
    display: none !important;
  }

  #receipt {
    max-width: none !important;
    width: 100% !important;
    box-shadow: none !important;
  }
}
</style>
