<template>
  <div class="sign-page">
    <div v-if="justCreated" class="sign-page__success">
      <p class="sign-page__success-title">🎉 Your shirt is ready!</p>
      <p class="sign-page__success-text">Copy this link and share it with friends so they can sign it too.</p>
      <div class="sign-page__success-link">
        <input type="text" :value="shareUrl" readonly @click="$event.target.select()" />
        <button type="button" @click="copyLink">{{ copied ? '✓ Copied' : 'Copy' }}</button>
      </div>
    </div>

    <h1 class="sign-page__title">Sign this shirt</h1>

    <p class="sign-page__step-label">Step 1 · Choose a side</p>
    <div class="sign-page__tabs">
      <button :class="{ active: side === 'front' }" @click="side = 'front'">
        👕 Front
      </button>
      <button :class="{ active: side === 'back' }" @click="side = 'back'">
        🔄 Back
      </button>
    </div>

    <ShirtCanvas
      :side="side"
      :signatures="signaturesForSide"
      :placing="placing"
      :design-text="shirt[`${side}_text`]"
      :design-text-color="shirt[`${side}_text_color`]"
      :design-image-path="shirt[`${side}_image_path`]"
      :design-x="shirt[`${side}_x`] ?? 50"
      :design-y="shirt[`${side}_y`] ?? 50"
      :design-width="shirt[`${side}_width`] ?? 30"
      :design-height="shirt[`${side}_height`] ?? 20"
      @place="onPlace"
    />

    <button v-if="!placing" class="sign-page__cta" @click="startSigning">
      ✏️ Sign this shirt
    </button>
    <p v-else-if="!pendingSpot" class="sign-page__hint">
      👆 Tap anywhere on the {{ side }} of the shirt to choose your spot
    </p>

    <!-- section below the shirt: appears once a spot has been picked -->
    <div v-if="pendingSpot" ref="pickerSection" class="sign-page__picker">
      <SignatureModePicker ref="picker" />
      <button class="sign-page__submit" @click="submitSignature" :disabled="submitting">
        {{ submitting ? 'Placing…' : '✓ Place my signature' }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineOptions({ layout: null });

import { ref, computed, nextTick } from 'vue';
import ShirtCanvas from '../components/ShirtCanvas.vue';
import SignatureModePicker from '../components/SignatureModePicker.vue';

// The shirt (with its signatures already loaded) is passed straight in by
// the GET /shirts/{shirt} route, which renders this page via Inertia.
const props = defineProps({
  shirt: { type: Object, required: true },
  justCreated: { type: Boolean, default: false },
});

// plain local binding so the template can reference `shirt` directly
const shirt = props.shirt;
const justCreated = props.justCreated;
const shareUrl = window.location.href;
const copied = ref(false);

function copyLink() {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(shareUrl).then(showCopied);
    return;
  }

  // navigator.clipboard only exists on HTTPS or localhost - fall back to the
  // older execCommand method for plain http:// custom domains (e.g. Herd's
  // .test domains without SSL enabled).
  const input = document.createElement('textarea');
  input.value = shareUrl;
  input.style.position = 'fixed';
  input.style.opacity = '0';
  document.body.appendChild(input);
  input.focus();
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
  showCopied();
}

function showCopied() {
  copied.value = true;
  setTimeout(() => { copied.value = false; }, 2000);
}

const signatures = ref(props.shirt.signatures || []);
const side = ref('front');
const placing = ref(false);
const pendingSpot = ref(null); // { x, y } in percentages, once chosen
const submitting = ref(false);
const picker = ref(null);
const pickerSection = ref(null);

const signaturesForSide = computed(() =>
  signatures.value.filter(s => s.side === side.value)
);

function startSigning() {
  placing.value = true;
  pendingSpot.value = null;
}

async function onPlace({ x, y }) {
  pendingSpot.value = { x, y };
  await nextTick();
  pickerSection.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Laravel sets an encrypted XSRF-TOKEN cookie on every response by default
// (via the VerifyCsrfToken middleware); reading it directly means signature
// submission works without depending on a <meta name="csrf-token"> tag
// existing in the page head.
function getCsrfTokenFromCookie() {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
  return match ? decodeURIComponent(match[1]) : '';
}

async function submitSignature() {
  const data = picker.value.getSignatureData();
  if (!data) return; // picker already shows its own inline error

  submitting.value = true;
  try {
    const response = await fetch(`/shirts/${props.shirt.uuid}/signatures`, {
      method: 'POST',
      credentials: 'same-origin', // ensures the session/XSRF cookies are sent
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-XSRF-TOKEN': getCsrfTokenFromCookie(),
      },
      body: JSON.stringify({
        ...data,
        side: side.value,
        x: pendingSpot.value.x,
        y: pendingSpot.value.y,
      }),
    });

    if (!response.ok) throw new Error('Failed to save signature');

    const signature = await response.json();
    signatures.value.push(signature);
    placing.value = false;
    pendingSpot.value = null;
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.sign-page {
  max-width: 420px; margin: 0 auto; padding: 1.5rem 1rem 3rem;
  background: #ffffff; color: #1d1d1d; min-height: 100vh;
}
.sign-page__success {
  background: #f2faf5; border: 1.5px solid #1a7a3c; border-radius: 10px;
  padding: 14px; margin-bottom: 16px;
}
.sign-page__success-title { font-weight: 700; color: #1a7a3c; margin: 0 0 4px; font-size: 16px; }
.sign-page__success-text { font-size: 13px; color: #444; margin: 0 0 10px; }
.sign-page__success-link { display: flex; gap: 6px; }
.sign-page__success-link input {
  flex: 1; padding: 8px; border-radius: 6px; border: 1px solid #cfe8d8;
  font-size: 12px; color: #444; background: #fff;
}
.sign-page__success-link button {
  padding: 8px 12px; border-radius: 6px; border: none;
  background: #1a7a3c; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
}
.sign-page__title { font-size: 22px; margin: 0 0 4px; }
.sign-page__step-label {
  font-size: 12px; font-weight: 700; letter-spacing: 0.03em;
  text-transform: uppercase; color: #1a7a3c; margin: 16px 0 8px;
}
.sign-page__tabs { display: flex; gap: 8px; margin-bottom: 14px; }
.sign-page__tabs button {
  flex: 1; padding: 10px; border-radius: 8px; border: 1.5px solid #d8d8d8;
  background: #fff; color: #1d1d1d; font-size: 15px; cursor: pointer;
}
.sign-page__tabs button.active { border-color: #1a7a3c; background: #f2faf5; color: #1a7a3c; font-weight: 600; }
.sign-page__cta, .sign-page__submit {
  width: 100%; padding: 14px; margin-top: 14px; border-radius: 8px;
  border: none; background: #1a7a3c; color: #fff; font-weight: 600; font-size: 16px; cursor: pointer;
}
.sign-page__submit:disabled { opacity: 0.6; cursor: default; }
.sign-page__hint {
  text-align: center; color: #1a7a3c; font-size: 14px; margin-top: 10px;
  background: #f2faf5; padding: 10px; border-radius: 8px;
}
.sign-page__picker { margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem; }
</style>