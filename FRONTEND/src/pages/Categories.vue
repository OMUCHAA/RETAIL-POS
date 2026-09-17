<script setup>
import { onMounted, ref, computed } from "vue";
import api from "../services/api";

const categories = ref([]);
const loading = ref(false);
const error = ref("");

const search = ref("");

const showModal = ref(false);
const editingCategory = ref(null);
const saving = ref(false);
const formError = ref("");

const form = ref({
  name: "",
  description: "",
});

const filteredCategories = computed(() => {
  if (!search.value.trim()) {
    return categories.value;
  }

  const keyword = search.value.toLowerCase();

  return categories.value.filter(
    (category) =>
      category.name?.toLowerCase().includes(keyword) ||
      category.description?.toLowerCase().includes(keyword)
  );
});

const fetchCategories = async () => {
  loading.value = true;
  error.value = "";

  try {
    const response = await api.get("/api/categories");

    categories.value = response.data.categories?.data ?? response.data.categories ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to load categories.";
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingCategory.value = null;

  form.value = {
    name: "",
    description: "",
  };

  formError.value = "";
  showModal.value = true;
};

const openEditModal = (category) => {
  editingCategory.value = category;

  form.value = {
    name: category.name ?? "",
    description: category.description ?? "",
  };

  formError.value = "";
  showModal.value = true;
};

const closeModal = () => {
  if (saving.value) return;

  showModal.value = false;
};

const saveCategory = async () => {
  saving.value = true;
  formError.value = "";

  try {
    if (editingCategory.value) {
      await api.put(`/api/categories/${editingCategory.value.id}`, form.value);
    } else {
      await api.post("/api/categories", form.value);
    }

    showModal.value = false;

    await fetchCategories();
  } catch (err) {
    formError.value = err.response?.data?.message || "Unable to save category.";
  } finally {
    saving.value = false;
  }
};

const deleteCategory = async (category) => {
  const confirmed = window.confirm(`Are you sure you want to delete "${category.name}"?`);

  if (!confirmed) return;

  try {
    await api.delete(`/api/categories/${category.id}`);

    await fetchCategories();
  } catch (err) {
    error.value = err.response?.data?.message || "Unable to delete category.";
  }
};

onMounted(() => {
  fetchCategories();
});
</script>

<template>
  <div class="mx-auto max-w-7xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Categories</h1>

        <p class="mt-1 text-sm text-gray-500">
          Organize your products into manageable categories.
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

        Add Category
      </button>
    </div>

    <!-- Error -->
    <div
      v-if="error"
      class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      {{ error }}
    </div>

    <!-- Main card -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
      <!-- Toolbar -->
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
            placeholder="Search categories..."
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

          <p class="mt-3 text-sm text-gray-500">Loading categories...</p>
        </div>
      </div>

      <!-- Table -->
      <div v-else-if="filteredCategories.length" class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Category
              </th>

              <th
                class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500"
              >
                Description
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
              v-for="category in filteredCategories"
              :key="category.id"
              class="transition hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100"
                  >
                    <svg
                      class="h-4 w-4 text-gray-500"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="1.8"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 6a2 2 0 012-2h5l9 9a2 2 0 010 3l-4 4a2 2 0 01-3 0L4 11V6z"
                      />
                    </svg>
                  </div>

                  <span class="text-sm font-semibold text-gray-900">
                    {{ category.name }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4">
                <p class="max-w-xl text-sm text-gray-500">
                  {{ category.description || "No description" }}
                </p>
              </td>

              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openEditModal(category)"
                    class="rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteCategory(category)"
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
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 6a2 2 0 012-2h5l9 9a2 2 0 010 3l-4 4a2 2 0 01-3 0L4 11V6z"
            />
          </svg>
        </div>

        <h3 class="mt-4 text-sm font-semibold text-gray-900">No categories found</h3>

        <p class="mt-1 text-sm text-gray-500">
          Create your first category to organize your products.
        </p>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 px-4 py-6"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
          <div>
            <h2 class="text-lg font-bold text-gray-900">
              {{ editingCategory ? "Edit Category" : "Add Category" }}
            </h2>

            <p class="mt-1 text-xs text-gray-500">
              {{
                editingCategory
                  ? "Update category information."
                  : "Create a new product category."
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
        <form @submit.prevent="saveCategory" class="space-y-5 px-6 py-6">
          <div
            v-if="formError"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ formError }}
          </div>

          <!-- Name -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Category name
            </label>

            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. Beverages"
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
              rows="4"
              placeholder="Optional category description"
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
              {{ saving ? "Saving..." : "Save Category" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
