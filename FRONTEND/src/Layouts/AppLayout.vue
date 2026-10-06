<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/40 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-300 lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="flex h-20 items-center border-b border-gray-100 px-6">
        <RouterLink
          to="/dashboard"
          class="flex items-center gap-3"
          @click="sidebarOpen = false"
        >
          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-900 text-sm font-bold text-white"
          >
            RP
          </div>

          <div>
            <h1 class="text-sm font-bold text-gray-900">Retail POS</h1>

            <p class="text-xs text-gray-400">Management System</p>
          </div>
        </RouterLink>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
        <!-- Main -->
        <p
          class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Main
        </p>

        <!-- Dashboard -->
        <RouterLink
          to="/dashboard"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <rect x="3" y="3" width="7" height="7" rx="1" />

            <rect x="14" y="3" width="7" height="7" rx="1" />

            <rect x="3" y="14" width="7" height="7" rx="1" />

            <rect x="14" y="14" width="7" height="7" rx="1" />
          </svg>

          Dashboard
        </RouterLink>

        <!-- Sales -->
        <p
          class="mb-3 mt-7 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Sales
        </p>

        <!-- New Sale -->
        <RouterLink
          to="/sales"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1 5h11m-9 4a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
            />
          </svg>

          New Sale
        </RouterLink>

        <!-- Sales History -->
        <RouterLink
          to="/reports"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 17h6M9 13h6M9 9h6M5 3h10l4 4v14H5V3z"
            />
          </svg>

          Sales History
        </RouterLink>

        <!-- Customers -->
        <RouterLink
          to="/customers"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm13 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
            />
          </svg>

          Customers
        </RouterLink>

        <!-- Inventory -->
        <p
          class="mb-3 mt-7 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Inventory
        </p>

        <!-- Products -->
        <RouterLink
          to="/products"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
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

          Products
        </RouterLink>

        <!-- Categories -->
        <RouterLink
          to="/categories"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>

          Categories
        </RouterLink>

        <!-- Inventory -->
        <RouterLink
          to="/inventory"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 7h16M4 12h16M4 17h16"
            />
          </svg>

          Inventory
        </RouterLink>

        <!-- Suppliers -->
        <RouterLink
          to="/suppliers"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5"
            />
          </svg>

          Suppliers
        </RouterLink>

        <!-- Purchasing -->
        <p
          class="mb-3 mt-7 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Purchasing
        </p>

        <RouterLink
          to="/purchases"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3 7h18M5 7v12h14V7M8 7V4h8v3M9 11h6M9 15h4"
            />
          </svg>

          Purchases
        </RouterLink>

        <!-- Management -->
        <p
          class="mb-3 mt-7 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Management
        </p>

        <RouterLink
          to="/users"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm8-6a3 3 0 11-2.83 4.99M22 21v-2a4 4 0 00-3-2.83"
            />
          </svg>

          Users
        </RouterLink>
      </nav>

      <!-- Bottom -->
      <div class="border-t border-gray-100 p-4">
        <!-- Current user -->
        <div class="mb-3 flex items-center gap-3 rounded-lg bg-gray-50 p-3">
          <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white"
          >
            {{ userInitials }}
          </div>

          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-gray-900">
              {{ userName }}
            </p>

            <p class="text-xs capitalize text-gray-400">
              {{ userRole }}
            </p>
          </div>
        </div>

        <!-- Logout -->
        <button
          type="button"
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-600"
          @click="logout"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 12H3m0 0l4-4m-4 4l4 4M21 5v14a2 2 0 01-2 2h-6"
            />
          </svg>

          Logout
        </button>
      </div>
    </aside>

    <!-- Main area -->
    <div class="lg:pl-64">
      <!-- Topbar -->
      <header
        class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-gray-200 bg-white/95 px-4 backdrop-blur sm:px-6"
      >
        <!-- Left -->
        <div class="flex items-center gap-3">
          <!-- Mobile menu -->
          <button
            type="button"
            class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
            @click="sidebarOpen = true"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>

          <div>
            <h2 class="text-sm font-semibold text-gray-900">
              {{ pageTitle }}
            </h2>

            <p class="hidden text-xs text-gray-400 sm:block">
              {{ pageDescription }}
            </p>
          </div>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-3">
          <!-- Notification -->
          <button
            type="button"
            class="relative rounded-lg p-2.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900"
          >
            <svg
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0"
              />
            </svg>

            <span
              class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-red-500"
            ></span>
          </button>

          <!-- Divider -->
          <div class="hidden h-9 w-px bg-gray-200 sm:block"></div>

          <!-- User -->
          <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
              <p class="text-sm font-semibold text-gray-900">
                {{ userName }}
              </p>

              <p class="text-xs capitalize text-gray-400">
                {{ userRole }}
              </p>
            </div>

            <div
              class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white"
            >
              {{ userInitials }}
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";

const sidebarOpen = ref(false);

const route = useRoute();
const router = useRouter();

const user = ref(null);

// ------------------------------------
// Page information
// ------------------------------------

const pageInformation = {
  dashboard: {
    title: "Dashboard",
    description: "Overview of your business",
  },

  sales: {
    title: "New Sale",
    description: "Create a new customer sale",
  },

  "sales-history": {
    title: "Sales History",
    description: "View and manage previous sales",
  },

  customers: {
    title: "Customers",
    description: "Manage your customers",
  },

  products: {
    title: "Products",
    description: "Manage your products",
  },

  categories: {
    title: "Categories",
    description: "Organize your products",
  },

  inventory: {
    title: "Inventory",
    description: "Monitor your current stock",
  },

  suppliers: {
    title: "Suppliers",
    description: "Manage your suppliers",
  },

  purchases: {
    title: "Purchases",
    description: "Manage stock purchases",
  },

  users: {
    title: "Users",
    description: "Manage system users",
  },
};

// ------------------------------------
// Current page
// ------------------------------------

const pageTitle = computed(() => {
  return pageInformation[route.name]?.title ?? "Retail POS";
});

const pageDescription = computed(() => {
  return pageInformation[route.name]?.description ?? "Management System";
});

// ------------------------------------
// User information
// ------------------------------------

const userName = computed(() => {
  return user.value?.name ?? "User";
});

const userRole = computed(() => {
  return user.value?.role ?? "user";
});

const userInitials = computed(() => {
  const name = userName.value;

  if (!name) {
    return "U";
  }

  return name
    .split(" ")
    .map((part) => part.charAt(0))
    .join("")
    .substring(0, 2)
    .toUpperCase();
});

// ------------------------------------
// Get authenticated user
// ------------------------------------

const fetchUser = async () => {
  try {
    const response = await api.get("/api/user");

    user.value = response.data;
  } catch (error) {
    console.error("Failed to load authenticated user:", error);
  }
};

// ------------------------------------
// Logout
// ------------------------------------

const logout = async () => {
  try {
    await api.post("/api/logout");
  } catch (error) {
    console.error("Logout error:", error);
  } finally {
    localStorage.removeItem("token");

    user.value = null;

    router.push("/login");
  }
};

onMounted(() => {
  fetchUser();
});
</script>
