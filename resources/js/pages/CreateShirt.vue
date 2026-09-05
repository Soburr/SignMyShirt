<template>
  <div class="create-page">
    <h1>Design your sign-out shirt</h1>
    <p class="create-page__hint">
      Add a word or image to the front and/or back. This is optional on both sides —
      your friends will sign around whatever you add.
    </p>

    <p class="create-page__step-label">Choose a side</p>
    <div class="create-page__tabs">
      <button :class="{ active: side === 'front' }" @click="side = 'front'">👕 Front</button>
      <button :class="{ active: side === 'back' }" @click="side = 'back'">🔄 Back</button>
    </div>

    <ShirtCanvas
      :side="side"
      :signatures="[]"
      :placing="placingText"
      :design-text="form[`${side}_text`]"
      :design-text-color="form[`${side}_text_color`]"
      :design-x="form[`${side}_x`]"
      :design-y="form[`${side}_y`]"
      :design-image-path="previewUrl(side)"
      @place="onPlaceText"
    />

    <div class="create-page__field">
      <label>Text ({{ side }}) — optional</label>
      <input type="text" v-model="form[`${side}_text`]" maxlength="60" placeholder="e.g. Class of 2026" />
    </div>

    <div class="create-page__field" v-if="form[`${side}_text`]">
      <label>Text color</label>
      <input type="color" v-model="form[`${side}_text_color`]" />
    </div>

    <button
      v-if="form[`${side}_text`]"
      type="button"
      class="create-page__place-btn"
      :class="{ active: placingText }"
      @click="placingText = true"
    >
      {{ placingText ? '👆 Tap the shirt to place your text' : '✥ Move text position' }}
    </button>

    <div class="create-page__field">
      <label>Image ({{ side }}) — optional</label>
      <p class="create-page__field-hint">Automatically centered on the chest, sized to fit — no placing needed.</p>

      <div v-if="!previewUrl(side)" class="create-page__upload">
        <input
          :id="`image-input-${side}`"
          type="file"
          accept="image/*"
          class="create-page__upload-input"
          @change="e => onImageChange(e, side)"
        />
        <label :for="`image-input-${side}`" class="create-page__upload-label">
          📷 Choose an image
        </label>
      </div>

      <div v-else class="create-page__preview">
        <img :src="previewUrl(side)" alt="Chosen image preview" class="create-page__preview-thumb" />
        <button type="button" class="create-page__preview-remove" @click="removeImage(side)">
          ✕ Remove
        </button>
      </div>
    </div>

    <p v-if="error" class="create-page__error">{{ error }}</p>

    <button type="button" class="create-page__submit" :disabled="submitting" @click="submit">
      {{ submitting ? 'Creating…' : 'Create my shirt & get a link' }}
    </button>
  </div>
</template>

<script setup>
defineOptions({ layout: null });

import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ShirtCanvas from '../components/ShirtCanvas.vue';

const side = ref('front');
const placingText = ref(false);
const submitting = ref(false);
const error = ref('');

const form = reactive({
  creator_name: '',
  front_text: '', front_text_color: '#1a7a3c', front_image: null, front_x: 50, front_y: 30,
  back_text: '', back_text_color: '#1a7a3c', back_image: null, back_x: 50, back_y: 30,
});

// local object URLs so the chosen image previews on the canvas before upload
const previews = reactive({ front: '', back: '' });

function previewUrl(s) {
  return previews[s];
}

function hasContentOn(s) {
  return !!(form[`${s}_text`] || form[`${s}_image`]);
}

function onImageChange(event, s) {
  const file = event.target.files[0];
  if (!file) return;
  form[`${s}_image`] = file;
  previews[s] = URL.createObjectURL(file);
}

function removeImage(s) {
  form[`${s}_image`] = null;
  previews[s] = '';
}

function onPlaceText({ x, y }) {
  form[`${side.value}_x`] = x;
  form[`${side.value}_y`] = y;
  placingText.value = false;
}

function submit() {
  error.value = '';

  if (!hasContentOn('front') && !hasContentOn('back')) {
    error.value = 'Add at least a word or image to the front or back before continuing.';
    return;
  }

  submitting.value = true;

  const data = new FormData();
  Object.entries(form).forEach(([key, value]) => {
    if (value !== null && value !== '') data.append(key, value);
  });

  router.post('/shirts', data, {
    onError: (errors) => {
      error.value = Object.values(errors)[0] || 'Something went wrong.';
      submitting.value = false;
    },
    onFinish: () => { submitting.value = false; },
  });
}
</script>

<style scoped>
.create-page {
  max-width: 420px;
  margin: 0 auto;
  padding: 1.5rem 1rem 3rem;
  background: #ffffff;
  color: #1d1d1d;
  min-height: 100vh;
}
.create-page__hint { color: #666; font-size: 14px; margin-bottom: 1rem; }
.create-page__step-label {
  font-size: 12px; font-weight: 700; letter-spacing: 0.03em;
  text-transform: uppercase; color: #1a7a3c; margin: 0 0 8px;
}
.create-page__tabs { display: flex; gap: 8px; margin-bottom: 12px; }
.create-page__tabs button {
  flex: 1; padding: 10px; border-radius: 8px; border: 1.5px solid #d8d8d8;
  background: #fff; color: #1d1d1d; font-size: 15px; cursor: pointer;
}
.create-page__tabs button.active { border-color: #1a7a3c; background: #f2faf5; color: #1a7a3c; font-weight: 600; }
.create-page__place-btn {
  width: 100%; margin-top: 10px; padding: 10px; border-radius: 8px;
  border: 1.5px dashed #1a7a3c; background: #f2faf5; color: #1a7a3c; font-weight: 500; cursor: pointer;
}
.create-page__place-btn.active { background: #1a7a3c; color: #fff; border-style: solid; }
.create-page__field { margin-top: 16px; }
.create-page__field label { display: block; font-size: 13px; color: #444; margin-bottom: 4px; font-weight: 500; }
.create-page__field-hint { font-size: 12px; color: #888; margin: 0 0 8px; }
.create-page__field input[type="text"] {
  width: 100%; padding: 10px; border-radius: 8px; border: 1.5px solid #d8d8d8; color: #1d1d1d; font-size: 15px;
}
.create-page__upload-input { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; }
.create-page__upload-label {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  padding: 14px; border-radius: 8px; border: 1.5px dashed #d8d8d8;
  background: #fafafa; color: #444; font-size: 14px; cursor: pointer;
}
.create-page__upload-label:hover { border-color: #1a7a3c; color: #1a7a3c; }
.create-page__preview {
  display: flex; align-items: center; gap: 12px; padding: 8px;
  border: 1.5px solid #e5e5e5; border-radius: 8px;
}
.create-page__preview-thumb { width: 56px; height: 56px; object-fit: contain; border-radius: 6px; background: #fafafa; }
.create-page__preview-remove {
  margin-left: auto; font-size: 13px; color: #b3261e; background: none; border: none; cursor: pointer;
}
.create-page__error { color: #b3261e; font-size: 13px; margin-top: 12px; }
.create-page__submit {
  width: 100%; padding: 14px; margin-top: 20px; border-radius: 8px;
  border: none; background: #1a7a3c; color: #fff; font-weight: 600; font-size: 16px; cursor: pointer;
}
.create-page__submit:disabled { opacity: 0.6; }
</style>