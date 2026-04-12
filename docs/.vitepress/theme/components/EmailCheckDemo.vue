<script setup lang="ts">
import { withBase } from 'vitepress'
import { computed, onMounted, ref } from 'vue'

const email = ref('')
const remoteUrl = withBase('/disposable_email.json')
const disposableDomains = ref<Set<string>>(new Set())
const isLoading = ref(true)
const hasRemoteError = ref(false)

const fallbackDomains = [
  '0-mail.com',
  '10minutemail.com',
  '1secmail.com',
  'tempmail.com',
  'mailinator.com',
  'guerrillamail.com',
  'yopmail.com',
  'trashmail.com',
  'sharklasers.com',
  'dispostable.com',
  'agedmail.com',
  'fakeinbox.com',
]

const suggestedEmails = [
  'demo@0-mail.com',
  'founder@gmail.com',
  'support@tempmail.com',
  'hello@company.com',
]

const normalizedEmail = computed(() => email.value.trim().toLowerCase())

const domain = computed(() => {
  const value = normalizedEmail.value

  if (!value.includes('@')) {
    return ''
  }

  return value.split('@').pop() ?? ''
})

const state = computed<'idle' | 'invalid' | 'safe' | 'disposable'>(() => {
  if (normalizedEmail.value.length === 0) {
    return 'idle'
  }

  const validEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!validEmailPattern.test(normalizedEmail.value)) {
    return 'invalid'
  }

  return disposableDomains.value.has(domain.value) ? 'disposable' : 'safe'
})

const statusLabel = computed(() => {
  if (isLoading.value) {
    return 'Checking email policy'
  }

  if (hasRemoteError.value) {
    return 'Validation temporarily unavailable'
  }

  if (state.value === 'idle') {
    return 'Start typing an email'
  }

  if (state.value === 'invalid') {
    return 'Email format is invalid'
  }

  if (state.value === 'disposable') {
    return 'Disposable email detected'
  }

  return 'Valid email'
})

const statusCopy = computed(() => {
  if (isLoading.value) {
    return 'Applying the disposable email validation rule before the form can continue.'
  }

  if (hasRemoteError.value) {
    return 'The email could not be checked right now. Please try again in a moment.'
  }

  if (state.value === 'idle') {
    return 'Test temp mail domains and see how your Laravel validation flow should respond in real time.'
  }

  if (state.value === 'invalid') {
    return 'Add a valid address like name@example.com before applying the disposable rule.'
  }

  if (state.value === 'disposable') {
    return 'Reject the signup, show a validation message, and ask the user for a permanent inbox.'
  }

  return 'This address passes the disposable domain check and can move forward in your form flow.'
})

const statusIcon = computed(() => {
  if (isLoading.value) {
    return '…'
  }

  if (hasRemoteError.value) {
    return '!'
  }

  if (state.value === 'disposable') {
    return '✕'
  }

  if (state.value === 'safe') {
    return '✓'
  }

  if (state.value === 'invalid') {
    return '!'
  }

  return '•'
})

const validationClass = computed(() => `is-${state.value}`)

const statusPills = computed(() => [
  {
    label: 'Syntax',
    ok: !isLoading.value && !hasRemoteError.value && (state.value === 'safe' || state.value === 'disposable'),
  },
  {
    label: 'Domain',
    ok: !isLoading.value && !hasRemoteError.value && state.value === 'safe',
  },
  {
    label: 'Policy',
    ok: !isLoading.value && !hasRemoteError.value,
  },
  {
    label: 'Blacklist',
    ok: !isLoading.value && !hasRemoteError.value && state.value !== 'disposable',
  },
])

onMounted(async () => {
  try {
    const response = await fetch(remoteUrl)

    if (!response.ok) {
      throw new Error(`Failed to fetch remote JSON: ${response.status}`)
    }

    const json = (await response.json()) as unknown

    if (!Array.isArray(json)) {
      throw new Error('Remote source is not a JSON array')
    }

    const domains = json
      .filter((item): item is string => typeof item === 'string')
      .map(item => item.trim().toLowerCase())
      .filter(Boolean)

    disposableDomains.value = new Set(domains)
  } catch {
    // Keep the demo functional even if the JSON asset fails to load.
    disposableDomains.value = new Set(fallbackDomains)
    hasRemoteError.value = false
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <section class="email-demo" :class="validationClass">
    <div class="email-demo-copy">
      <span class="section-label">Live validation</span>
      <h3>Real-time disposable email checker</h3>
      <p>
        Preview how the validation experience feels inside a Laravel app. Red highlights invalid or
        disposable addresses, while green shows an email that passes the check.
      </p>

      <div class="email-demo-actions">
        <button
          v-for="suggestedEmail in suggestedEmails"
          :key="suggestedEmail"
          type="button"
          @click="email = suggestedEmail"
        >
          {{ suggestedEmail }}
        </button>
      </div>

      <div class="email-demo-metrics">
        <div>
          <strong>110,646+</strong>
          <span>disposable domains covered</span>
        </div>
        <div>
          <strong>Realtime</strong>
          <span>Laravel-style validation feedback</span>
        </div>
        <div>
          <strong>Laravel</strong>
          <span>rule, facade, Blade directive</span>
        </div>
      </div>
    </div>

    <div class="email-demo-panel">
      <div class="email-demo-stars" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
      </div>

      <label class="email-demo-label" for="demo-email">Try an email address</label>

      <div class="email-demo-input-wrap">
        <span class="email-demo-prefix">@</span>
        <input id="demo-email" v-model="email" type="email" placeholder="name@example.com" />
        <span class="email-demo-status-icon">{{ statusIcon }}</span>
      </div>

      <div class="email-demo-status">
        <span class="email-demo-badge">{{ statusLabel }}</span>
        <p>{{ statusCopy }}</p>
      </div>

      <div class="email-demo-pill-row">
        <span
          v-for="pill in statusPills"
          :key="pill.label"
          :class="['email-demo-pill', { 'is-ok': pill.ok }]"
        >
          {{ pill.label }}
        </span>
      </div>

      <div class="email-demo-terminal">
        <div class="email-demo-terminal-head">
          <span></span>
          <span></span>
          <span></span>
        </div>

        <pre><code>$request-&gt;validate([
    'email' =&gt; ['required', 'email', 'disposable_email'],
]);
Email checked: {{ normalizedEmail || 'name@example.com' }}
Domain: {{ domain || 'pending...' }}
Status: {{ statusLabel }}</code></pre>
      </div>
    </div>
  </section>
</template>
