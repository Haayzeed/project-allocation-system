<script setup lang="ts">
import { ref } from 'vue';

const matricNumber = ref('');
const stateOfOrigin = ref('');

function handleSubmit() {
  // Add login logic here
  console.log('Login attempt:', { matricNumber: matricNumber.value, stateOfOrigin: stateOfOrigin.value });
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Student Login
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your matric number and state of origin
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="matric-number" class="sr-only">Matric Number</label>
            <input
              id="matric-number"
              v-model="matricNumber"
              name="matricNumber"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Matric Number"
            />
          </div>
          <div>
            <label for="state-of-origin" class="sr-only">State of Origin</label>
            <input
              id="state-of-origin"
              v-model="stateOfOrigin"
              name="stateOfOrigin"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="State of Origin"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Sign in
          </button>
        </div>
      </form>
    </div>
  </div>
</template> 