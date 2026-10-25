<script setup lang="ts">
import { VacantAPI } from '@/services/VacantAPI'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

interface Vacant {
  id: number
  vacant_name: string
  vacant_desc: string
  vacant_link: string
  created_at: string
  updated_at: string
}

const route = useRoute()
const router = useRouter()
const vacant = ref<Vacant | null>(null)
const isLoading = ref(true)
const errorMessage = ref<string | null>(null)

const formattedCreatedAt = () => {
  if (!vacant.value?.created_at) return ''

  return new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'long',
    timeStyle: 'short',
  }).format(new Date(vacant.value.created_at))
}

const loadVacant = async () => {
  isLoading.value = true
  errorMessage.value = null

  try {
    const response = await VacantAPI.getById(String(route.params.id))
    vacant.value = response.vacant
  } catch (error) {
    console.error('Error fetching vacant detail:', error)
    errorMessage.value = 'Gagal memuat detail lowongan. Coba lagi.'
  } finally {
    isLoading.value = false
  }
}

const deleteVacant = async () => {
  if (!vacant.value) return

  try {
    if (!confirm('Apakah Anda yakin ingin menghapus lowongan ini?')) {
      return
    }
    await VacantAPI.deletevacant(vacant.value.id)
    router.push({ name: 'Vacant' })
  } catch (error) {
    console.error('Error deleting vacant:', error)
    errorMessage.value = 'Gagal menghapus lowongan. Coba lagi.'
  }
}
onMounted(loadVacant)
</script>

<template>
  <div class="page">
    <header class="topbar">
      <div class="topbar-inner">
        <button class="back-button" type="button" @click="router.push({ name: 'Vacant' })">
          ← Kembali
        </button>
        <span class="brand">Magang<span class="brand-dot">.</span>id</span>
      </div>
    </header>

    <main class="content">
      <div v-if="isLoading" class="state">Memuat detail lowongan...</div>

      <div v-else-if="errorMessage" class="state state-error" role="alert">
        <p>{{ errorMessage }}</p>
        <button class="btn btn-primary" type="button" @click="loadVacant">Coba lagi</button>
      </div>

      <article v-else-if="vacant" class="detail-card">
        <p class="eyebrow">Detail lowongan</p>
        <h1>{{ vacant.vacant_name }}</h1>
        <p class="description">{{ vacant.vacant_desc }}</p>

        <dl class="metadata">
          <div>
            <dt>Dibuat pada</dt>
            <dd>{{ formattedCreatedAt() }}</dd>
          </div>
        </dl>

        <a
          class="btn btn-primary"
          :href="vacant.vacant_link"
          target="_blank"
          rel="noopener noreferrer"
        >
          Tonton video di YouTube
        </a>
        <button class="btn btn-primary" type="button" @click="deleteVacant">Hapus Lowongan</button>
      </article>
    </main>
  </div>
</template>

<style scoped>
.page {
  --ink: #17202e;
  --muted: #5d6b7e;
  --paper: #f3f5f8;
  --surface: #ffffff;
  --line: #dde2ea;
  --accent: #1d5fd1;

  min-height: 100vh;
  background: var(--paper);
  color: var(--ink);
  font-family: 'JetBrains Mono', ui-monospace, 'SFMono-Regular', Menlo, monospace;
}

.topbar {
  background: var(--surface);
  border-bottom: 1px solid var(--line);
}

.topbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 960px;
  margin: 0 auto;
  padding: 1rem 1.5rem;
}

.brand {
  font-size: 1.1rem;
  font-weight: 700;
}

.brand-dot {
  color: var(--accent);
}

.back-button {
  border: 0;
  background: transparent;
  color: var(--accent);
  cursor: pointer;
  font: inherit;
  padding: 0;
}

.content {
  max-width: 960px;
  margin: 0 auto;
  padding: 3rem 1.5rem;
}

.state {
  color: var(--muted);
  text-align: center;
}

.state-error {
  color: #c0392b;
}

.detail-card {
  max-width: 680px;
  margin: 0 auto;
  padding: 2.5rem;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 0.75rem;
  box-shadow: 0 12px 32px rgb(23 32 46 / 8%);
}

.eyebrow {
  margin: 0 0 0.75rem;
  color: var(--accent);
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  font-size: clamp(1.8rem, 4vw, 2.5rem);
}

.description {
  margin: 1.25rem 0 2rem;
  color: var(--muted);
  line-height: 1.7;
  white-space: pre-wrap;
}

.metadata {
  margin: 0 0 2rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--line);
}

.metadata dt {
  color: var(--muted);
  font-size: 0.8rem;
}

.metadata dd {
  margin: 0.35rem 0 0;
}

.btn {
  display: inline-block;
  padding: 0.75rem 1rem;
  border-radius: 0.4rem;
  font: inherit;
  font-weight: 700;
  text-decoration: none;
}

.btn-primary {
  border: 1px solid var(--accent);
  background: var(--accent);
  color: white;
}
</style>
