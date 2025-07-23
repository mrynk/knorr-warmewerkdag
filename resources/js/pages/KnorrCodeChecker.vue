<template>
  <div class="knorr-app">
    <!-- Background Images -->
    <div class="background-container">
      <img 
        src="/static/iPhone 16 Plus - 1-x1.jpg" 
        alt="Knorr background design"
        class="background-image"
      />
    </div>

    <!-- Header with Knorr Branding -->
    <header class="app-header">
      <div class="header-logo">
        <div class="logo-placeholder"></div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section class="hero-section">
        <div class="hero-content">
          <h1 class="main-heading">
            Vul je code in en ontdek<br>direct of je prijs hebt.
          </h1>
        </div>
      </section>

      <!-- Form Section -->
      <section class="form-section">
        <form @submit.prevent="handleSubmit" class="code-form">
          <!-- Name Field -->
          <div class="input-group">
            <input
              v-model="formData.name"
              type="text"
              placeholder="Naam"
              class="form-input"
              :class="{ 'error': errors.name }"
              aria-label="Naam"
              @blur="validateField('name')"
            />
          </div>

          <!-- Email Field -->
          <div class="input-group">
            <input
              v-model="formData.email"
              type="email"
              placeholder="Email"
              class="form-input"
              :class="{ 'error': errors.email }"
              aria-label="Email adres"
              @blur="validateField('email')"
            />
          </div>

          <!-- Soup Selection -->
          <div class="input-group">
            <select
              v-model="formData.soupType"
              class="form-input form-select"
              :class="{ 'error': errors.soupType }"
              aria-label="Welke soep"
              @change="validateField('soupType')"
            >
              <option value="">Welke soep</option>
              <option value="tomaat">Tomatensoep</option>
              <option value="groente">Groentesoep</option>
              <option value="kip">Kippensoep</option>
              <option value="mushroom">Champignonsoep</option>
            </select>
          </div>

          <!-- Code Input -->
          <div class="input-group">
            <input
              v-model="formData.code"
              type="text"
              placeholder="X-X-X-X-X"
              class="form-input"
              :class="{ 'error': errors.code }"
              aria-label="Code"
              maxlength="9"
              @input="formatCode"
              @blur="validateField('code')"
            />
          </div>

          <!-- Terms Checkbox -->
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input
                v-model="formData.acceptTerms"
                type="checkbox"
                class="checkbox-input"
                aria-label="Ik ga akkoord met de actievoorwaarden"
                @change="validateField('acceptTerms')"
              />
              <span class="checkbox-custom"></span>
              <span class="checkbox-text">Ik ga akkoord met de actievoorwaarden</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            class="submit-button"
            :disabled="!isFormValid || isLoading"
            :aria-label="isLoading ? 'Code wordt gecontroleerd...' : 'Check mijn code'"
          >
            <span v-if="isLoading">Controleren...</span>
            <span v-else>Check mijn code</span>
          </button>
        </form>
      </section>

      <!-- Chef Section -->
      <section class="chef-section">
        <div class="chef-content">
          <h2 class="chef-heading">DUIK IN DE SOEP<br>MET ONZE CHEFS</h2>
          <div class="chef-play-button" @click="playChefVideo" @keydown.enter="playChefVideo" tabindex="0" role="button" aria-label="Speel chef video af">
            <div class="play-circle">
              <div class="play-icon"></div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Result Modal -->
    <div v-if="showResult" class="result-modal" @click="closeResult">
      <div class="result-content" @click.stop>
        <h3>{{ resultMessage.title }}</h3>
        <p>{{ resultMessage.description }}</p>
        <button @click="closeResult" class="close-button">Sluiten</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'

// Types
interface FormData {
  name: string
  email: string
  soupType: string
  code: string
  acceptTerms: boolean
}

interface ValidationErrors {
  name?: string
  email?: string
  soupType?: string
  code?: string
  acceptTerms?: string
}

interface ResultMessage {
  title: string
  description: string
}

// Reactive data
const formData = reactive<FormData>({
  name: '',
  email: '',
  soupType: '',
  code: '',
  acceptTerms: false
})

const errors = ref<ValidationErrors>({})
const isLoading = ref(false)
const showResult = ref(false)
const resultMessage = ref<ResultMessage>({ title: '', description: '' })

// Computed
const isFormValid = computed(() => {
  return formData.name.trim() !== '' &&
         formData.email.trim() !== '' &&
         formData.soupType !== '' &&
         formData.code.length === 9 &&
         formData.acceptTerms &&
         Object.keys(errors.value).length === 0
})

// Methods
const formatCode = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()
  
  // Format as X-X-X-X-X
  if (value.length > 0) {
    value = value.match(/.{1}/g)?.join('-') || value
  }
  
  formData.code = value.slice(0, 9) // Limit to X-X-X-X-X format
}

const validateField = (fieldName: keyof FormData) => {
  const newErrors = { ...errors.value }
  
  switch (fieldName) {
    case 'name':
      if (!formData.name.trim()) {
        newErrors.name = 'Naam is verplicht'
      } else {
        delete newErrors.name
      }
      break
      
    case 'email':
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!formData.email.trim()) {
        newErrors.email = 'Email is verplicht'
      } else if (!emailRegex.test(formData.email)) {
        newErrors.email = 'Ongeldig email adres'
      } else {
        delete newErrors.email
      }
      break
      
    case 'soupType':
      if (!formData.soupType) {
        newErrors.soupType = 'Selecteer een soep'
      } else {
        delete newErrors.soupType
      }
      break
      
    case 'code':
      if (formData.code.length !== 9) {
        newErrors.code = 'Code moet 5 karakters bevatten'
      } else {
        delete newErrors.code
      }
      break
      
    case 'acceptTerms':
      if (!formData.acceptTerms) {
        newErrors.acceptTerms = 'Accepteer de voorwaarden'
      } else {
        delete newErrors.acceptTerms
      }
      break
  }
  
  errors.value = newErrors
}

const handleSubmit = async () => {
  // Validate all fields
  Object.keys(formData).forEach(key => {
    validateField(key as keyof FormData)
  })
  
  if (!isFormValid.value) return
  
  isLoading.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Mock result based on code
    const cleanCode = formData.code.replace(/-/g, '')
    const isWinner = cleanCode.includes('WIN') || cleanCode === 'KNORR'
    
    resultMessage.value = isWinner
      ? {
          title: '🎉 Gefeliciteerd!',
          description: 'Je hebt een prijs gewonnen! Check je email voor meer details.'
        }
      : {
          title: 'Helaas geen prijs',
          description: 'Deze code heeft geen prijs. Probeer het opnieuw met een andere code!'
        }
    
    showResult.value = true
  } catch (error) {
    resultMessage.value = {
      title: 'Fout opgetreden',
      description: 'Er is iets misgegaan. Probeer het later opnieuw.'
    }
    showResult.value = true
  } finally {
    isLoading.value = false
  }
}

const playChefVideo = () => {
  // Handle chef video play
  console.log('Playing chef video...')
}

const closeResult = () => {
  showResult.value = false
  // Reset form after successful submission
  if (resultMessage.value.title.includes('Gefeliciteerd')) {
    Object.assign(formData, {
      name: '',
      email: '',
      soupType: '',
      code: '',
      acceptTerms: false
    })
    errors.value = {}
  }
}
</script>

<style scoped>
/* Root container */
.knorr-app {
  position: relative;
  width: 100%;
  max-width: 430px;
  height: 100vh;
  margin: 0 auto;
  background-color: #000;
  overflow-x: hidden;
  font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Background */
.background-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.background-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.9;
}

/* Header */
.app-header {
  position: relative;
  z-index: 2;
  padding: 53px 0 0 0;
  display: flex;
  justify-content: center;
}

.header-logo {
  width: 107px;
  height: 69px;
}

.logo-placeholder {
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.9);
  border-radius: 8px;
}

/* Main content */
.main-content {
  position: relative;
  z-index: 2;
  padding: 0 20px;
}

/* Hero section */
.hero-section {
  text-align: center;
  margin: 240px 0 60px 0;
}

.main-heading {
  font-size: 26px;
  line-height: 30.47px;
  font-weight: 600;
  color: #ffffff;
  margin: 0;
  padding: 0 28px;
}

/* Form section */
.form-section {
  padding: 0 15px;
  margin-bottom: 60px;
}

.code-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.input-group {
  position: relative;
}

.form-input {
  width: 100%;
  height: 48px;
  background-color: #ffffff;
  border: none;
  border-radius: 10px;
  padding: 0 15px;
  font-size: 20px;
  font-family: 'Roboto', sans-serif;
  color: #333;
  box-sizing: border-box;
  transition: all 0.2s ease-in-out;
}

.form-input::placeholder {
  color: #c5c5c5;
  font-size: 20px;
}

.form-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px #f8ba00;
  transform: scale(1.02);
}

.form-input.error {
  border: 2px solid #ff4444;
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23c5c5c5' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 12px center;
  background-repeat: no-repeat;
  background-size: 16px;
  padding-right: 40px;
}

/* Checkbox */
.checkbox-group {
  margin: 20px 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.checkbox-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.checkbox-custom {
  width: 20px;
  height: 20px;
  background-color: #ffffff;
  border-radius: 3px;
  margin-right: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease-in-out;
  flex-shrink: 0;
}

.checkbox-input:checked + .checkbox-custom::after {
  content: '✓';
  color: #f8ba00;
  font-weight: bold;
  font-size: 14px;
}

.checkbox-input:focus + .checkbox-custom {
  box-shadow: 0 0 0 2px #f8ba00;
}

.checkbox-text {
  color: #ffffff;
  font-size: 14px;
  line-height: 16.41px;
}

/* Submit button */
.submit-button {
  width: 222px;
  height: 52px;
  background-color: #f8ba00;
  border: none;
  border-radius: 13px;
  color: #ffffff;
  font-size: 20px;
  font-weight: 500;
  font-family: 'Roboto', sans-serif;
  cursor: pointer;
  margin: 20px auto 0;
  display: block;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 4px 12px rgba(248, 186, 0, 0.3);
}

.submit-button:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(248, 186, 0, 0.4);
}

.submit-button:active:not(:disabled) {
  transform: scale(0.98);
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Chef section */
.chef-section {
  text-align: center;
  padding: 40px 20px;
  margin-top: 60px;
}

.chef-heading {
  font-size: 24px;
  font-weight: bold;
  color: #ffffff;
  margin-bottom: 40px;
  line-height: 1.2;
}

.chef-play-button {
  cursor: pointer;
  display: inline-block;
  transition: transform 0.2s ease-in-out;
}

.chef-play-button:hover {
  transform: scale(1.05);
}

.chef-play-button:focus {
  outline: 2px solid #f8ba00;
  border-radius: 50%;
}

.play-circle {
  width: 136px;
  height: 136px;
  border: 6px solid #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  background-color: rgba(255, 255, 255, 0.1);
}

.play-icon {
  width: 0;
  height: 0;
  border-left: 32px solid #ffffff;
  border-top: 20px solid transparent;
  border-bottom: 20px solid transparent;
  margin-left: 8px;
}

/* Result modal */
.result-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  box-sizing: border-box;
}

.result-content {
  background-color: #ffffff;
  padding: 30px;
  border-radius: 15px;
  text-align: center;
  max-width: 350px;
  width: 100%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.result-content h3 {
  margin: 0 0 15px 0;
  font-size: 24px;
  color: #333;
}

.result-content p {
  margin: 0 0 25px 0;
  font-size: 16px;
  color: #666;
  line-height: 1.4;
}

.close-button {
  background-color: #f8ba00;
  color: #ffffff;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}

.close-button:hover {
  background-color: #e6a800;
  transform: translateY(-1px);
}

/* Responsive adjustments */
@media (max-width: 430px) {
  .knorr-app {
    max-width: 100%;
  }
  
  .main-content {
    padding: 0 15px;
  }
  
  .hero-section {
    margin: 200px 0 40px 0;
  }
  
  .main-heading {
    font-size: 22px;
    line-height: 26px;
  }
  
  .submit-button {
    width: 200px;
    height: 48px;
    font-size: 18px;
  }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Focus styles for better accessibility */
.form-input:focus,
.checkbox-input:focus + .checkbox-custom,
.submit-button:focus,
.chef-play-button:focus {
  outline: 2px solid #f8ba00;
  outline-offset: 2px;
}
</style> 