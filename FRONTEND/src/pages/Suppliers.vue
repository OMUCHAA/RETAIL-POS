<script setup>
import { computed, onMounted, ref } from "vue";
import api from "../services/api";

const suppliers = ref([]);
const loading = ref(false);
const error = ref("");

const search = ref("");

const showModal = ref(false);
const editingSupplier = ref(null);
const saving = ref(false);
const formError = ref("");

const form = ref({
  supplier_name: "",
  phone: "",
  email: "",
  address: "",
});

const filteredSuppliers = computed(() => {
  if (!search.value.trim()) {
    return suppliers.value;
  }

  const keyword = search.value.toLowerCase();

  return suppliers.value.filter(
    (supplier) =>
      supplier.supplier_name?.toLowerCase().includes(keyword) ||
      supplier.phone?.toLowerCase().includes(keyword) ||
      supplier.email?.toLowerCase().includes(keyword)
  );
});

const fetchSuppliers = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/suppliers");

    suppliers.value = response.data.suppliers?.data ?? response.data.suppliers ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load suppliers.";
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingSupplier.value = null;

  form.value = {
    supplier_name: "",
    phone: "",
    email: "",
    address: "",
  };

  formError.value = "";
  showModal.value = true;
};

const openEditModal = (supplier) => {
  editingSupplier.value = supplier;

  form.value = {
    supplier_name: supplier.supplier_name ?? "",
    phone: supplier.phone ?? "",
    email: supplier.email ?? "",
    address: supplier.address ?? "",
  };

  formError.value = "";
  showModal.value = true;
};

const closeModal = () => {
  if (saving.value) return;

  showModal.value = false;
};

const saveSupplier = async () => {
  saving.value = true;
  formError.value = "";

  try {
    if (editingSupplier.value) {
      await api.put(`/api/suppliers/${editingSupplier.value.id}`, form.value);
    } else {
      await api.post("/api/suppliers", form.value);
    }

    showModal.value = false;

    await fetchSuppliers();
  } catch (err) {
    formError.value = err.response?.data?.message || "Unable to save supplier.";
  } finally {
    saving.value = false;
  }
};

const deleteSupplier = async (supplier) => {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${supplier.supplier_name}"?`
  );

  if (!confirmed) return;

  try {
    await api.delete(`/api/suppliers/${supplier.id}`);

    await fetchSuppliers();
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to delete supplier.";
  }
};

onMounted(() => {
  fetchSuppliers();
});
</script>

<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Suppliers</h1>

        <p class="mt-1 text-sm text-gray-500">
          Manage the suppliers that provide your products.
        </p>
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

        Add Supplier
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
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5">
      <p class="text-sm text-gray-500">Total Suppliers</p>

      <p class="mt-2 text-2xl font-bold text-gray-900">
        {{ suppliers.length }}
      </p>
    </div>

    <!-- Suppliers card -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
      <!-- Search -->
      <div class="border-b border-gray-100 p-4">
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
            placeholder="Search suppliers..."
            class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-4 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
          />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex min-h-64 items-center justify-center">
        <div class="text-center">
          <div
            class="mx-auto h-7 w-7 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"
          ></div>

          <p class="mt-3 text-sm text-gray-500">Loading suppliers...</p>
        </div>
      </div>

      <!-- Table -->
      <div v-else-if="filteredSuppliers.length" class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Supplier
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Phone
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Email
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Address
              </th>

              <th
                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="supplier in filteredSuppliers"
              :key="supplier.id"
              class="transition hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-sm font-semibold text-gray-600"
                  >
                    {{ supplier.supplier_name?.charAt(0)?.toUpperCase() }}
                  </div>

                  <span class="text-sm font-semibold text-gray-900">
                    {{ supplier.supplier_name }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ supplier.phone || "—" }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ supplier.email || "—" }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ supplier.address || "—" }}
              </td>

              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openEditModal(supplier)"
                    class="rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteSupplier(supplier)"
                    class="rounded-lg px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty state -->
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
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 20a6 6 0 0112 0M16 7a4 4 0 110 8"
            />
          </svg>
        </div>

        <h3 class="mt-4 text-sm font-semibold text-gray-900">No suppliers found</h3>

        <p class="mt-1 text-sm text-gray-500">Add your first supplier to get started.</p>
      </div>

      <!-- Mobile cards -->
      <div
        v-if="!loading && filteredSuppliers.length"
        class="divide-y divide-gray-100 md:hidden"
      >
        <div v-for="supplier in filteredSuppliers" :key="supplier.id" class="p-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-900">
                {{ supplier.supplier_name }}
              </h3>

              <p class="mt-1 text-xs text-gray-400">
                {{ supplier.email || "No email" }}
              </p>
            </div>

            <div class="flex gap-1">
              <button
                @click="openEditModal(supplier)"
                class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"
              >
                Edit
              </button>

              <button
                @click="deleteSupplier(supplier)"
                class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
              >
                Delete
              </button>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-400">Phone</p>

              <p class="mt-1 text-sm font-medium text-gray-700">
                {{ supplier.phone || "—" }}
              </p>
            </div>

            <div>
              <p class="text-xs text-gray-400">Address</p>

              <p class="mt-1 text-sm font-medium text-gray-700">
                {{ supplier.address || "—" }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 px-4 py-6"
    >
      <div
        class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl"
      >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
          <div>
            <h2 class="text-lg font-bold text-gray-900">
              {{ editingSupplier ? "Edit Supplier" : "Add Supplier" }}
            </h2>

            <p class="mt-1 text-xs text-gray-500">
              {{
                editingSupplier
                  ? "Update supplier information."
                  : "Add a supplier to your system."
              }}
            </p>
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
        <form @submit.prevent="saveSupplier" class="space-y-5 px-6 py-6">
          <div
            v-if="formError"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ formError }}
          </div>

          <!-- Supplier name -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Supplier name
            </label>

            <input
              v-model="form.supplier_name"
              type="text"
              required
              placeholder="e.g. ABC Distributors"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Phone -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Phone </label>

            <input
              v-model="form.phone"
              type="tel"
              placeholder="e.g. 0712345678"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Email </label>

            <input
              v-model="form.email"
              type="email"
              placeholder="supplier@example.com"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Address -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Address </label>

            <textarea
              v-model="form.address"
              rows="3"
              placeholder="Supplier address"
              class="w-full rounded-lg border border-gray-300 px-3 py-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            ></textarea>
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
              {{ saving ? "Saving..." : "Save Supplier" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
