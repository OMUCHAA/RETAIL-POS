<script setup>
import { computed, onMounted, ref } from "vue";
import api from "../services/api";

const customers = ref([]);
const loading = ref(false);
const error = ref("");

const search = ref("");

const showModal = ref(false);
const editingCustomer = ref(null);
const saving = ref(false);
const formError = ref("");

const form = ref({
  customer_name: "",
  phone_number: "",
  email: "",
  address: "",
});

const filteredCustomers = computed(() => {
  if (!search.value.trim()) {
    return customers.value;
  }

  const keyword = search.value.toLowerCase();

  return customers.value.filter(
    (customer) =>
      customer.customer_name?.toLowerCase().includes(keyword) ||
      customer.phone_number?.toLowerCase().includes(keyword) ||
      customer.email?.toLowerCase().includes(keyword)
  );
});

const fetchCustomers = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/customers");

    customers.value = response.data.customers?.data ?? response.data.customers ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load customers.";
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingCustomer.value = null;

  form.value = {
    customer_name: "",
    phone_number: "",
    email: "",
    address: "",
  };

  formError.value = "";
  showModal.value = true;
};

const openEditModal = (customer) => {
  editingCustomer.value = customer;

  form.value = {
    customer_name: customer.customer_name ?? "",
    phone: customer.phone_number ?? "",
    email: customer.email ?? "",
    address: customer.address ?? "",
  };

  formError.value = "";
  showModal.value = true;
};

const closeModal = () => {
  if (saving.value) return;

  showModal.value = false;
};

const saveCustomer = async () => {
  saving.value = true;
  formError.value = "";

  try {
    if (editingCustomer.value) {
      await api.put(`/api/customers/${editingCustomer.value.id}`, form.value);
    } else {
      await api.post("/api/customers", form.value);
    }

    showModal.value = false;

    await fetchCustomers();
  } catch (err) {
    formError.value = err.response?.data?.message || "Unable to save customer.";
  } finally {
    saving.value = false;
  }
};

const deleteCustomer = async (customer) => {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${customer.customer_name}"?`
  );

  if (!confirmed) return;

  try {
    await api.delete(`/api/customers/${customer.id}`);

    await fetchCustomers();
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to delete customer.";
  }
};

onMounted(() => {
  fetchCustomers();
});
</script>

<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Customers</h1>

        <p class="mt-1 text-sm text-gray-500">
          Manage your customer records and contact information.
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

        Add Customer
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
      <p class="text-sm text-gray-500">Total Customers</p>

      <p class="mt-2 text-2xl font-bold text-gray-900">
        {{ customers.length }}
      </p>
    </div>

    <!-- Customers card -->
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
            placeholder="Search customers..."
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

          <p class="mt-3 text-sm text-gray-500">Loading customers...</p>
        </div>
      </div>

      <!-- Table -->
      <div v-else-if="filteredCustomers.length" class="hidden overflow-x-auto md:block">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Customer
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
              v-for="customer in filteredCustomers"
              :key="customer.id"
              class="transition hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600"
                  >
                    {{ customer.customer_name?.charAt(0)?.toUpperCase() }}
                  </div>

                  <span class="text-sm font-semibold text-gray-900">
                    {{ customer.customer_name }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ customer.phone_number || "—" }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ customer.email || "—" }}
              </td>

              <td class="px-6 py-4 text-sm text-gray-600">
                {{ customer.address || "—" }}
              </td>

              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openEditModal(customer)"
                    class="rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteCustomer(customer)"
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

      <!-- Mobile cards -->
      <div
        v-if="!loading && filteredCustomers.length"
        class="divide-y divide-gray-100 md:hidden"
      >
        <div v-for="customer in filteredCustomers" :key="customer.id" class="p-4">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <div
                class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600"
              >
                {{ customer.customer_name?.charAt(0)?.toUpperCase() }}
              </div>

              <div>
                <h3 class="text-sm font-semibold text-gray-900">
                  {{ customer.customer_name }}
                </h3>

                <p class="mt-1 text-xs text-gray-400">
                  {{ customer.phone_number || "No phone" }}
                </p>
              </div>
            </div>

            <div class="flex gap-1">
              <button
                @click="openEditModal(customer)"
                class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"
              >
                Edit
              </button>

              <button
                @click="deleteCustomer(customer)"
                class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
              >
                Delete
              </button>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-400">Email</p>

              <p class="mt-1 text-sm font-medium text-gray-700">
                {{ customer.email || "—" }}
              </p>
            </div>

            <div>
              <p class="text-xs text-gray-400">Address</p>

              <p class="mt-1 text-sm font-medium text-gray-700">
                {{ customer.address || "—" }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div
        v-if="!loading && filteredCustomers.length === 0"
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

        <h3 class="mt-4 text-sm font-semibold text-gray-900">No customers found</h3>

        <p class="mt-1 text-sm text-gray-500">Add your first customer to get started.</p>
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
              {{ editingCustomer ? "Edit Customer" : "Add Customer" }}
            </h2>

            <p class="mt-1 text-xs text-gray-500">
              {{
                editingCustomer
                  ? "Update customer information."
                  : "Add a customer to your system."
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
        <form @submit.prevent="saveCustomer" class="space-y-5 px-6 py-6">
          <div
            v-if="formError"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ formError }}
          </div>

          <!-- Name -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Customer name
            </label>

            <input
              v-model="form.customer_name"
              type="text"
              required
              placeholder="e.g. John Kamau"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Phone -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Phone </label>

            <input
              v-model="form.phone_number"
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
              placeholder="customer@example.com"
              class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm outline-none focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Address -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700"> Address </label>

            <textarea
              v-model="form.address"
              rows="3"
              placeholder="Customer address"
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
              {{ saving ? "Saving..." : "Save Customer" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
