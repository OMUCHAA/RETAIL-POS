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
        <div class="flex items-center gap-3">
          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-900 text-sm font-bold text-white"
          >
            RP
          </div>

          <div>
            <h1 class="text-sm font-bold text-gray-900">Retail POS</h1>

            <p class="text-xs text-gray-400">Management System</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
        <p
          class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Main
        </p>

        <RouterLink
          to="/home"
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
              d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"
            />
          </svg>

          Dashboard
        </RouterLink>

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

        <RouterLink
          to="/categories"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          Categories
        </RouterLink>

        <RouterLink
          to="/suppliers"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          Suppliers
        </RouterLink>

        <RouterLink
          to="/customers"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          Customers
        </RouterLink>

        <p
          class="mb-3 mt-7 px-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400"
        >
          Transactions
        </p>

        <RouterLink
          to="/sales"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          Sales
        </RouterLink>

        <RouterLink
          to="/purchases"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          Purchases
        </RouterLink>

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
          Users
        </RouterLink>

        <RouterLink
          to="/reports"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          active-class="bg-gray-100 text-gray-900"
          @click="sidebarOpen = false"
        >
          Reports
        </RouterLink>
      </nav>

      <!-- Bottom -->
      <div class="border-t border-gray-100 p-4">
        <RouterLink
          to="/settings"
          class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
        >
          Settings
        </RouterLink>

        <button
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-600"
        >
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
        <div class="flex items-center gap-3">
          <!-- Mobile menu -->
          <button
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
            <h2 class="text-sm font-semibold text-gray-900">Dashboard</h2>

            <p class="hidden text-xs text-gray-400 sm:block">Overview of your business</p>
          </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-3">
          <!-- Notification -->
          <button
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

          <!-- User -->
          <div class="hidden h-9 w-px bg-gray-200 sm:block"></div>

          <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
              <p class="text-sm font-semibold text-gray-900">Test Cashier</p>

              <p class="text-xs text-gray-400">Cashier</p>
            </div>

            <div
              class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white"
            >
              TC
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
import { ref } from "vue";

const sidebarOpen = ref(false);
</script>
