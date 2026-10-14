<script setup lang="ts">
import { VacantAPI } from '@/services/VacantAPI'
import { AuthAPI } from '@/services/auth/AuthAPI'
import { nextTick, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()


const data = ref<any[]>([])
const errorMessage = ref<string | null>(null)
const isLoading = ref<boolean>(true)

const loadData = async () => {
  isLoading.value = true
  errorMessage.value = null

  try {
    const response = await VacantAPI.getindex()
    data.value = response.vacants
  } catch (error: any) {
    console.error('Error fetching data:', error)
    errorMessage.value = error.message || 'Terjadi kesalahan saat mengambil data.'
  } finally {
    isLoading.value = false
  }
}

const showModal = ref(false)
const isSaving = ref(false)
const formError = ref<string | null>(null)
const nameInput = ref<HTMLInputElement | null>(null)

const form = reactive({
  vacant_name: '',
  vacant_desc: '',
  vacant_link: '',
})

const openModal = async () => {
  formError.value = null
  showModal.value = true
  await nextTick()
  nameInput.value?.focus()
}

const closeModal = () => {
  if (isSaving.value) return
  showModal.value = false
  form.vacant_name = ''
  form.vacant_desc = ''
  form.vacant_link = ''
  formError.value = null
}

const submitForm = async () => {
  isSaving.value = true
  formError.value = null

  try {
    await VacantAPI.addvacant({
      vacant_name: form.vacant_name.trim(),
      vacant_desc: form.vacant_desc.trim(),
      vacant_link: form.vacant_link.trim(),
    })
    isSaving.value = false
    closeModal()
    await loadData()
  } catch (error: any) {
    console.error('Error creating vacant:', error)
    formError.value = error.message || 'Gagal menyimpan lowongan. Coba lagi.'
  } finally {
    isSaving.value = false
  }
}

const handleLogout = async () => {
  try {
    // NOTE: remove this call if your API has no logout endpoint
    await AuthAPI.logout()
  } catch (error) {
    console.error('Logout request failed:', error)
  } finally {
    // NOTE: use the same key you store your auth token under
    localStorage.removeItem('token')
    router.push('/auth')
  }
}

onMounted(loadData)
</script>

<template>
  <div class="page">
    <header class="topbar">
      <div class="topbar-inner">
        <span class="brand">Magang<span class="brand-dot">.</span>id</span>
        <div class="topbar-actions">
          <button class="btn btn-primary" type="button" @click="openModal">
            + Tambah lowongan
          </button>
          <button class="btn btn-ghost" type="button" @click="handleLogout">Logout</button>
        </div>
      </div>
    </header>

    <main class="content">
      <div class="heading">
        <h1>Lowongan magang yang tersedia</h1>
        <p v-if="!isLoading && !errorMessage && data.length" class="count">
          {{ data.length }} lowongan
        </p>
      </div>

      <div v-if="isLoading" class="state">
        <span class="spinner" aria-hidden="true"></span>
        Memuat daftar lowongan...
      </div>

      <div v-else-if="errorMessage" class="state state-error" role="alert">
        <p>{{ errorMessage }}</p>
        <button class="btn btn-ghost" type="button" @click="loadData">Coba lagi</button>
      </div>

      <div v-else-if="data.length === 0" class="state">
        <p>Belum ada lowongan.</p>
        <button class="btn btn-primary" type="button" @click="openModal">
          Tambah lowongan pertama
        </button>
      </div>

      <ul v-else class="list">
        <li v-for="vacant in data" :key="vacant.id" class="card">
          <div class="card-text">
            <h2>{{ vacant.vacant_name }}</h2>
            <p>{{ vacant.vacant_desc }}</p>
          </div>
          <a
            class="btn btn-primary"
            :href="vacant.vacant_link"
            target="_blank"
            rel="noopener noreferrer"
          >
            Lihat lowongan
          </a>
        </li>
      </ul>
    </main>

    <!-- Add vacancy modal -->
    <div v-if="showModal" class="overlay" @click.self="closeModal" @keydown.esc="closeModal">
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <h2 id="modal-title">Tambah lowongan</h2>

        <form @submit.prevent="submitForm">
          <label class="field">
            <span>Nama lowongan</span>
            <input
              ref="nameInput"
              v-model="form.vacant_name"
              type="text"
              required
              placeholder="Frontend Developer Intern"
            />
          </label>

          <label class="field">
            <span>Deskripsi</span>
            <textarea
              v-model="form.vacant_desc"
              rows="3"
              required
              placeholder="Perusahaan, lokasi, atau ringkasan singkat"
            ></textarea>
          </label>

          <label class="field">
            <span>Link pendaftaran</span>
            <input
              v-model="form.vacant_link"
              type="url"
              required
              placeholder="https://contoh.com/lowongan"
            />
          </label>

          <p v-if="formError" class="form-error" role="alert">{{ formError }}</p>

          <div class="modal-actions">
            <button class="btn btn-ghost" type="button" :disabled="isSaving" @click="closeModal">
              Batal
            </button>
            <button class="btn btn-primary" type="submit" :disabled="isSaving">
              {{ isSaving ? 'Menyimpan...' : 'Simpan lowongan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
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
  --accent-dark: #144aa5;
  --danger: #c0392b;

  min-height: 100vh;
  background: var(--paper);
  color: var(--ink);
  font-family: 'JetBrains Mono', ui-monospace, 'SFMono-Regular', Menlo, monospace;
}

/* ---------- top bar ---------- */
.topbar {
  position: sticky;
  top: 0;
  z-index: 10;
  background: var(--surface);
  border-bottom: 1px solid var(--line);
}

.topbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  max-width: 760px;
  margin: 0 auto;
  padding: 0.75rem 1.25rem;
}

.brand {
  font-size: 1.125rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.brand-dot {
  color: var(--accent);
}

.topbar-actions {
  display: flex;
  gap: 0.5rem;
}

/* ---------- content ---------- */
.content {
  max-width: 760px;
  margin: 0 auto;
  padding: 2.5rem 1.25rem 4rem;
}

.heading {
  margin-bottom: 1.5rem;
}

.heading h1 {
  margin: 0;
  font-size: clamp(1.5rem, 4vw, 2rem);
  line-height: 1.2;
  letter-spacing: -0.03em;
}

.count {
  margin: 0.5rem 0 0;
  color: var(--muted);
  font-size: 0.875rem;
}

/* ---------- cards ---------- */
.list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  padding: 1.25rem 1.5rem;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 0.75rem;
  transition: border-color 0.15s ease;
}

.card:hover {
  border-color: var(--accent);
}

.card-text {
  min-width: 0;
}

.card-text h2 {
  margin: 0 0 0.35rem;
  font-size: 1.125rem;
  line-height: 1.3;
}

.card-text p {
  margin: 0;
  color: var(--muted);
  font-size: 0.875rem;
  line-height: 1.55;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.55rem 0.95rem;
  border: 1px solid transparent;
  border-radius: 0.5rem;
  font: inherit;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition:
    background-color 0.15s ease,
    border-color 0.15s ease;
}

.btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--accent);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: var(--accent-dark);
}

.btn-ghost {
  background: transparent;
  color: var(--ink);
  border-color: var(--line);
}

.btn-ghost:hover:not(:disabled) {
  background: var(--paper);
  border-color: var(--muted);
}

.state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 3rem 1rem;
  color: var(--muted);
  text-align: center;
}

.state p {
  margin: 0;
}

.state-error {
  color: var(--danger);
  font-weight: 600;
}

.spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 3px solid var(--line);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.overlay {
  position: fixed;
  inset: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(23, 32, 46, 0.55);
}

.modal {
  width: 100%;
  max-width: 440px;
  max-height: 100%;
  overflow-y: auto;
  padding: 1.5rem;
  background: var(--surface);
  border-radius: 0.875rem;
}

.modal h2 {
  margin: 0 0 1.25rem;
  font-size: 1.25rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1rem;
}

.field span {
  font-size: 0.8125rem;
  font-weight: 600;
}

.field input,
.field textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--line);
  border-radius: 0.5rem;
  background: var(--surface);
  color: var(--ink);
  font: inherit;
  font-size: 0.875rem;
  resize: vertical;
}

.field input:focus,
.field textarea:focus {
  outline: 2px solid var(--accent);
  outline-offset: 1px;
  border-color: var(--accent);
}

.form-error {
  margin: 0 0 1rem;
  color: var(--danger);
  font-size: 0.875rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.25rem;
}

@media (max-width: 520px) {
  .card {
    flex-direction: column;
    align-items: stretch;
  }

  .card .btn {
    width: 100%;
  }

  .topbar-inner {
    padding-inline: 1rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .spinner {
    animation-duration: 2s;
  }
}
</style>
