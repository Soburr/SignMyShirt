<template>
  <div class="sign-page">
    <div class="sign-page__tabs">
      <button :class="{ active: side === 'front' }" @click="side = 'front'">Front</button>
      <button :class="{ active: side === 'back' }" @click="side = 'back'">Back</button>
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
      Sign this shirt
    </button>
    <p v-else-if="!pendingSpot" class="sign-page__hint">
      Tap anywhere on the shirt to choose your spot.
    </p>

    <!-- section below the shirt: appears once a spot has been picked -->
    <div v-if="pendingSpot" ref="pickerSection" class="sign-page__picker">
      <SignatureModePicker ref="picker" />
      <button class="sign-page__submit" @click="submitSignature" :disabled="submitting">
        {{ submitting ? 'Placing…' : 'Place my signature' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import axios from 'axios';
import ShirtCanvas from '../components/ShirtCanvas.vue';
import SignatureModePicker from '../components/SignatureModePicker.vue';

const props = defineProps({
  shirtUuid: { type: String, required: true },
});

const shirt = ref({ signatures: [] });
const side = ref('front');
const placing = ref(false);
const pendingSpot = ref(null);
const submitting = ref(false);
const picker = ref(null);
const pickerSection = ref(null);

const signaturesForSide = computed(() =>
  (shirt.value.signatures || []).filter(s => s.side === side.value)
);

async function loadShirt() {
  const { data } = await axios.get(`/api/shirts/${props.shirtUuid}`);
  shirt.value = data;
}
loadShirt();

function startSigning() {
  placing.value = true;
  pendingSpot.value = null;
}

async function onPlace({ x, y }) {
  pendingSpot.value = { x, y };
  await nextTick();
  pickerSection.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

async function submitSignature() {
  const data = picker.value.getSignatureData();
  if (!data) return; 

  submitting.value = true;
  try {
    const { data: signature } = await axios.post(
      `/api/shirts/${props.shirtUuid}/signatures`,
      { ...data, side: side.value, x: pendingSpot.value.x, y: pendingSpot.value.y }
    );
    shirt.value.signatures.push(signature);
    placing.value = false;
    pendingSpot.value = null;
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.sign-page { max-width: 420px; margin: 0 auto; padding: 1rem; }
.sign-page__tabs { display: flex; gap: 8px; margin-bottom: 12px; }
.sign-page__tabs button { flex: 1; padding: 8px; border-radius: 8px; border: 1px solid #d8d8d8; background: #fff; }
.sign-page__tabs button.active { border-color: #1d1d1d; font-weight: 600; }
.sign-page__cta, .sign-page__submit {
  width: 100%; padding: 12px; margin-top: 12px; border-radius: 8px;
  border: none; background: #1d1d1d; color: #fff; font-weight: 600; cursor: pointer;
}
.sign-page__submit:disabled { opacity: 0.6; cursor: default; }
.sign-page__hint { text-align: center; color: #666; font-size: 14px; margin-top: 8px; }
.sign-page__picker { margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem; }
</style>