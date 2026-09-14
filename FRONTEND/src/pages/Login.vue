<template>
  <div class="min-h-screen bg-gray-50 lg:flex">
    <!-- Left branding section -->
    <div
      class="hidden lg:flex lg:w-[46%] lg:min-h-screen bg-gray-900 text-white flex-col justify-between p-14"
    >
      <div></div>

      <div class="max-w-lg">
        <div
          class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-lg font-extrabold tracking-tight text-gray-900 mb-7"
        >
          RP
        </div>

        <h1 class="text-5xl font-bold tracking-tight leading-tight">Retail POS</h1>

        <p class="mt-5 max-w-md text-base leading-7 text-gray-400">
          A smarter way to manage your sales, inventory and business operations.
        </p>
      </div>

      <div class="flex items-center justify-between gap-5 text-xs text-gray-500">
        <span>© 2026 Retail POS</span>
        <span>Secure Business Management</span>
      </div>
    </div>

    <!-- Login section -->
    <div class="flex min-h-screen flex-1 items-center justify-center px-6 py-10 sm:px-10">
      <div class="w-full max-w-md">
        <!-- Mobile branding -->
        <div class="mb-12 flex items-center gap-3 lg:hidden">
          <div
            class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-900 text-sm font-extrabold text-white"
          >
            RP
          </div>

          <span class="text-lg font-bold tracking-tight text-gray-900"> Retail POS </span>
        </div>

        <!-- Heading -->
        <div class="mb-8">
          <h2 class="text-3xl font-bold tracking-tight text-gray-900">Welcome back</h2>

          <p class="mt-2 text-sm text-gray-500">Sign in to continue to your account.</p>
        </div>

        <!-- Error -->
        <div
          v-if="error"
          class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
          {{ error }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Email -->
          <div>
            <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
              Email address
            </label>

            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="Enter your email"
              autocomplete="email"
              required
              class="block h-12 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
              Password
            </label>

            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
              class="block h-12 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-gray-500 focus:ring-4 focus:ring-gray-100"
            />
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="flex h-12 w-full items-center justify-center rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white transition hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span v-if="loading"> Signing in... </span>

            <span v-else> Sign In </span>
          </button>
        </form>

        <!-- Security note -->
        <p class="mt-6 text-center text-xs leading-5 text-gray-400">
          Your connection is protected and your account credentials are securely handled.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../services/api";

const router = useRouter();

const email = ref("");
const password = ref("");

const loading = ref(false);
const error = ref("");

const handleLogin = async () => {
  error.value = "";
  loading.value = true;

  try {
    await api.get("/sanctum/csrf-cookie");

    await api.post("/api/login", {
      email: email.value,
      password: password.value,
    });
    router.push("/");
  } catch (err) {
    if (err.response?.status === 422) {
      error.value =
        err.response?.data?.message || "Something went wrong please try again";
      console.log(err.response.data.message);
    } else if (err.response?.status === 401) {
      error.value =
        err.response?.data?.message || "Something went wrong please try again";
      console.log(err.response.data.message);
    } else {
      error.value =
        err.response?.data?.message || "Something went wrong please try again";
      console.log(err.response.data.message);
    }

    console.error(err);
  } finally {
    loading.value = false;
  }
};
</script>
