<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { AuthAPI } from '@/services/auth/AuthAPI'
import { useRouter } from 'vue-router'

const router = useRouter()

const isLogin = ref<boolean>(true)
const isLoading = ref<boolean>(false)
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const showPassword = ref<boolean>(false)

const Data = reactive({
  name: '',
  email: '',
  password: '',
})

const title = computed(() => (isLogin.value ? 'Masuk ke akun' : 'Buat akun baru'))
const submitLabel = computed(() => {
  if (isLoading.value) return isLogin.value ? 'Memproses...' : 'Mendaftar...'
  return isLogin.value ? 'Login' : 'Register'
})

const toggleMode = () => {
  isLogin.value = !isLogin.value
  errorMessage.value = null
  successMessage.value = null
  Data.name = ''
  Data.password = ''
  showPassword.value = false
}

const onLogin = async () => {
  await AuthAPI.login({ email: Data.email, password: Data.password })
  router.push('/vacants')
}

const onRegister = async () => {
  await AuthAPI.register({ name: Data.name, email: Data.email, password: Data.password })
  isLogin.value = true
  Data.name = ''
  Data.password = ''
  successMessage.value = 'Akun berhasil dibuat. Silakan login.'
}

const onSubmit = async () => {
  isLoading.value = true
  errorMessage.value = null
  successMessage.value = null

  try {
    if (isLogin.value) {
      await onLogin()
    } else {
      await onRegister()
    }
  } catch (error: any) {
    console.error('Terjadi error:', error)
    errorMessage.value =
      error?.response?.data?.message ||
      error?.message ||
      (isLogin.value ? 'Login gagal. Periksa email dan password.' : 'Registrasi gagal. Coba lagi.')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <span class="brand">Magang<span class="brand-dot">.</span>id</span>
      <h1>{{ title }}</h1>
      <p class="subtitle">
        {{
          isLogin
            ? 'Masuk untuk melihat dan mengelola lowongan magang.'
            : 'Daftar untuk mulai mengelola lowongan magang.'
        }}
      </p>

      <form @submit.prevent="onSubmit">
        <label v-if="!isLogin" class="field" for="name">
          <span>Nama</span>
          <input
            id="name"
            v-model="Data.name"
            type="text"
            name="name"
            autocomplete="name"
            required
            placeholder="Nama lengkap"
          />
        </label>

        <label class="field" for="email">
          <span>Email</span>
          <input
            id="email"
            v-model="Data.email"
            type="email"
            name="email"
            autocomplete="email"
            required
            placeholder="nama@email.com"
          />
        </label>

        <label class="field" for="password">
          <span>Password</span>
          <div class="password-wrap">
            <input
              id="password"
              v-model="Data.password"
              :type="showPassword ? 'text' : 'password'"
              name="password"
              :autocomplete="isLogin ? 'current-password' : 'new-password'"
              required
              minlength="6"
              placeholder="Minimal 6 karakter"
            />
            <button
              class="toggle-pass"
              type="button"
              :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? 'Sembunyikan' : 'Lihat' }}
            </button>
          </div>
        </label>

        <p v-if="successMessage" class="message message-success" role="status">
          {{ successMessage }}
        </p>
        <p v-if="errorMessage" class="message message-error" role="alert">
          {{ errorMessage }}
        </p>

        <button class="btn-submit" type="submit" :disabled="isLoading">
          {{ submitLabel }}
        </button>
      </form>

      <p class="switch">
        {{ isLogin ? 'Belum punya akun?' : 'Sudah punya akun?' }}
        <button class="switch-btn" type="button" :disabled="isLoading" @click="toggleMode">
          {{ isLogin ? 'Register' : 'Login' }}
        </button>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  --ink: #17202e;
  --muted: #5d6b7e;
  --paper: #f3f5f8;
  --surface: #ffffff;
  --line: #dde2ea;
  --accent: #1d5fd1;
  --accent-dark: #144aa5;
  --danger: #c0392b;
  --success: #1e7a46;

  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem 1rem;
  background: var(--paper);
  color: var(--ink);
  font-family: 'JetBrains Mono', ui-monospace, 'SFMono-Regular', Menlo, monospace;
}

.auth-card {
  width: 100%;
  max-width: 400px;
  padding: 2rem 1.75rem;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 0.875rem;
}

.brand {
  display: block;
  margin-bottom: 1.5rem;
  font-size: 1.125rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.brand-dot {
  color: var(--accent);
}

h1 {
  margin: 0;
  font-size: 1.5rem;
  line-height: 1.2;
  letter-spacing: -0.03em;
}

.subtitle {
  margin: 0.5rem 0 1.5rem;
  color: var(--muted);
  font-size: 0.875rem;
  line-height: 1.55;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1rem;
}

.field > span {
  font-size: 0.8125rem;
  font-weight: 600;
}

.field input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.65rem 0.75rem;
  border: 1px solid var(--line);
  border-radius: 0.5rem;
  background: var(--surface);
  color: var(--ink);
  font: inherit;
  font-size: 0.875rem;
}

.field input:focus {
  outline: 2px solid var(--accent);
  outline-offset: 1px;
  border-color: var(--accent);
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  padding-right: 5.5rem;
}

.toggle-pass {
  position: absolute;
  top: 50%;
  right: 0.5rem;
  transform: translateY(-50%);
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 0.375rem;
  background: transparent;
  color: var(--accent);
  font: inherit;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
}

.toggle-pass:hover {
  background: var(--paper);
}

.toggle-pass:focus-visible,
.switch-btn:focus-visible,
.btn-submit:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.message {
  margin: 0 0 1rem;
  padding: 0.6rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  line-height: 1.45;
}

.message-error {
  color: var(--danger);
  background: #fdecea;
}

.message-success {
  color: var(--success);
  background: #e8f5ee;
}


.btn-submit {
  width: 100%;
  padding: 0.7rem 1rem;
  border: none;
  border-radius: 0.5rem;
  background: var(--accent);
  color: #fff;
  font: inherit;
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.btn-submit:hover:not(:disabled) {
  background: var(--accent-dark);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.switch {
  margin: 1.25rem 0 0;
  color: var(--muted);
  font-size: 0.875rem;
  text-align: center;
}

.switch-btn {
  padding: 0;
  border: none;
  background: none;
  color: var(--accent);
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.switch-btn:hover:not(:disabled) {
  text-decoration: underline;
}
</style>