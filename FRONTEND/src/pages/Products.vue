<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Products</h1>

        <p class="mt-1 text-sm text-gray-500">Manage your products, pricing and stock.</p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800"
      >
        <svg
          class="h-4 w-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>

        Add Product
      </button>
    </div>

    <!-- Error -->
    <div
      v-if="error"
      class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>

    <!-- Stats -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Total Products</p>

        <p class="mt-2 text-2xl font-bold text-gray-900">
          {{ products.length }}
        </p>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-5">
        <p class="text-sm text-gray-500">Active Products</p>

        <p class="mt-2 text-2xl font-bold text-gray-900">
          {{ products.filter((product) => product.is_active).length }}
        </p>
      </div>

      <div
        class="rounded-xl border border-gray-200 bg-white p-5 sm:col-span-2 xl:col-span-1"
      >
        <p class="text-sm text-gray-500">Low / Out of Stock</p>

        <p class="mt-2 text-2xl font-bold text-gray-900">
          {{ products.filter((product) => (product.current_stock ?? 0) <= 10).length }}
        </p>
      </div>
    </div>

    <!-- Products card -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
      <!-- Toolbar -->
      <div
        class="flex flex-col gap-4 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between"
      >
        <!-- Search -->
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
            placeholder="Search products..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
          />
        </div>

        <!-- Status -->
        <select
          v-model="statusFilter"
          class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
        >
          <option value="all">All Products</option>

          <option value="active">Active</option>

          <option value="inactive">Inactive</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex min-h-64 items-center justify-center">
        <div class="text-center">
          <div
            class="mx-auto h-7 w-7 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"
          ></div>

          <p class="mt-3 text-sm text-gray-500">Loading products...</p>
        </div>
      </div>

      <!-- Desktop table -->
      <div v-else class="hidden overflow-x-auto md:block">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Product
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Category
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Price
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Stock
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
              v-for="product in filteredProducts"
              :key="product.id"
              class="transition hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <div>
                  <p class="text-sm font-semibold text-gray-900">
                    {{ product.name }}
                  </p>

                  <p v-if="product.barcode" class="mt-1 text-xs text-gray-400">
                    {{ product.barcode }}
                  </p>
                </div>
              </td>

              <td class="px-6 py-4">
                <span class="text-sm text-gray-600">
                  {{ product.category?.name || "Uncategorized" }}
                </span>
              </td>

              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ formatPrice(product.price) }}
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-900">
                    {{ product.current_stock ?? 0 }}
                  </span>

                  <span
                    :class="stockStatus(product.current_stock ?? 0).classes"
                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                  >
                    {{ stockStatus(product.current_stock ?? 0).label }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4">
                <span
                  v-if="product.is_active"
                  class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                >
                  Active
                </span>

                <span
                  v-else
                  class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600"
                >
                  Inactive
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

      <!-- Empty state -->
      <div
        v-if="!loading && filteredProducts.length === 0"
        class="flex min-h-64 flex-col items-center justify-center px-6 text-center"
      >
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
          <svg
            class="h-6 w-6 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
            />
          </svg>
        </div>

        <h3 class="mt-4 text-sm font-semibold text-gray-900">No products found</h3>

        <p class="mt-1 text-sm text-gray-500">
          Try changing your search or add a new product.
        </p>
      </div>

      <!-- Mobile cards -->
      <div
        v-if="!loading && filteredProducts.length > 0"
        class="divide-y divide-gray-100 md:hidden"
      >
        <div v-for="product in filteredProducts" :key="product.id" class="p-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-900">
                {{ product.name }}
              </h3>

              <p class="mt-1 text-xs text-gray-400">
                {{ product.category?.name || "Uncategorized" }}
              </p>
            </div>

            <span
              :class="
                product.is_active
                  ? 'bg-emerald-50 text-emerald-700'
                  : 'bg-gray-100 text-gray-600'
              "
              class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
            >
              {{ product.is_active ? "Active" : "Inactive" }}
            </span>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-400">Price</p>

              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ formatPrice(product.price) }}
              </p>
            </div>

            <div>
              <p class="text-xs text-gray-400">Stock</p>

              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ product.current_stock ?? 0 }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Product Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 px-4 py-6"
    >
      <div
        class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl"
      >
        <!-- Modal header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Add Product</h2>

            <p class="mt-1 text-xs text-gray-500">Add a new product to your catalogue.</p>
          </div>

          <button
            @click="closeModal"
            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
          >
            <svg
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="createProduct" class="space-y-5 px-6 py-6">
          <div
            v-if="formError"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ formError }}
          </div>

          <!-- Name -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Product name
            </label>

            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. Mineral Water 500ml"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Category -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Category </label>

            <select
              v-model="form.category_id"
              class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            >
              <option value="">Select category</option>

              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Price -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Selling price
            </label>

            <input
              v-model="form.price"
              type="number"
              min="0"
              step="0.01"
              required
              placeholder="0.00"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Barcode -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Barcode </label>

            <input
              v-model="form.barcode"
              type="text"
              placeholder="Optional"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Description
            </label>

            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Optional product description"
              class="w-full rounded-lg border border-gray-300 px-3 py-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            ></textarea>
          </div>

          <!-- Active -->
          <label class="flex cursor-pointer items-center gap-3">
            <input
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-400"
            />

            <span class="text-sm text-gray-700"> Product is active </span>
          </label>

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
              {{ saving ? "Saving..." : "Save Product" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import api from "../services/api";

const products = ref([]);
const loading = ref(false);
const error = ref("");

const search = ref("");
const statusFilter = ref("all");

const showModal = ref(false);
const saving = ref(false);

const formError = ref("");

const form = ref({
  category_id: "",
  name: "",
  description: "",
  price: "",
  barcode: "",
  image: "",
  is_active: true,
});

const categories = ref([]);

const filteredProducts = computed(() => {
  let result = products.value;

  if (search.value.trim()) {
    const keyword = search.value.toLowerCase();

    result = result.filter(
      (product) =>
        product.name?.toLowerCase().includes(keyword) ||
        product.barcode?.toLowerCase().includes(keyword)
    );
  }

  if (statusFilter.value !== "all") {
    result = result.filter((product) =>
      statusFilter.value === "active" ? product.is_active : !product.is_active
    );
  }

  return result;
});

const fetchProducts = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/products");

    products.value = response.data.products?.data ?? response.data.products ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load products.";
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await api.get("/api/categories");

    categories.value = response.data.categories?.data ?? response.data.categories ?? [];
  } catch (err) {
    console.error(err);
  }
};

const openCreateModal = () => {
  form.value = {
    category_id: "",
    name: "",
    description: "",
    price: "",
    barcode: "",
    image: "",
    is_active: true,
  };

  formError.value = "";
  showModal.value = true;
};

const closeModal = () => {
  if (saving.value) return;

  showModal.value = false;
};

const createProduct = async () => {
  saving.value = true;
  formError.value = "";

  try {
    await api.post("/api/products", form.value);

    showModal.value = false;

    await fetchProducts();
  } catch (err) {
    formError.value = err.response?.data?.message || "Unable to create product.";
  } finally {
    saving.value = false;
  }
};

const stockStatus = (stock) => {
  if (stock <= 0) {
    return {
      label: "Out of stock",
      classes: "bg-red-50 text-red-700",
    };
  }

  if (stock <= 10) {
    return {
      label: "Low stock",
      classes: "bg-amber-50 text-amber-700",
    };
  }

  return {
    label: "In stock",
    classes: "bg-emerald-50 text-emerald-700",
  };
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(price ?? 0);
};

onMounted(() => {
  fetchProducts();
  fetchCategories();
});
</script>
