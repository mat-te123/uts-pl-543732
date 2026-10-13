<script setup lang="ts">
import { VacantAPI } from '@/services/VacantAPI'
import { onMounted, ref, toRaw } from 'vue'

const vacants = ref<any[]>([])
const errorMessage = ref<string | null>(null)
const isLoading = ref<boolean>(true)

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = null

  try {
    const response = await VacantAPI.getindex()

    if (Array.isArray(response)) {
      vacants.value = response
    } else if (response && Array.isArray(response.data)) {
      vacants.value = response.data
    } else {
      vacants.value = []
    }

    console.log('Data vacants setelah fetch:', toRaw(vacants.value))
  } catch (error: any) {
    console.error('Error fetching data:', error)
    errorMessage.value = error.message || 'An error occurred while fetching data.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="main">
    <div v-if="isLoading" class="status-message">Loading vacant list...</div>

    <div v-else-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>

    <div v-else-if="vacants.length === 0" class="status-message">No vacant entries found.</div>

    <template v-else>
      <h1>Daftar Lowongan magang yang tersedia</h1>
      <div v-for="vacant in vacants" :key="vacant.id" class="vacant-card">
        <div id="title-subtitle">
          <h2>{{ vacant.vacant_name }}</h2>
          <p>{{ vacant.vacant_desc }}</p>
        </div>
        <a :href="vacant.vacant_link" target="_blank">click</a>
      </div>
    </template>
  </div>
</template>

<style scoped>
.main {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  font-family: 'JetBrains Mono', sans-serif;
}

.vacant-card {
  background-color: #f1f5f9;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  border-radius: 1rem;
  border: 1px solid #ccc;
  width: 100%;
  max-width: 500px;
  padding: 1.5rem;
  margin-bottom: 1rem;

  #title-subtitle {
    display: flex;
    flex-direction: column;
    gap: 0;

    h2 {
      --fontsize: 32px;
      font-size: var(--fontsize);
      line-height: 0;
    }
    p {
      --fontsize: 16;
      font-size: var(--fontsize);
      line-height: 0;
    }
  }

  a {
    background-color: #63abf8;
    color: white;
    text-decoration: none;
    padding: 0.5rem;
    border-radius: 0.5rem;
  }

  a:hover {
    background-color: #074f9c;
  }
}

.error-message {
  color: red;
  font-weight: bold;
}
</style>
