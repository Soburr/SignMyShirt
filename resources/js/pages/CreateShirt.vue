<template>
  <div class="create-page">
    <h1>Design your sign-out shirt</h1>
    <p class="create-page__hint">
      Add a word or image to the front and/or back. This is optional on both sides —
      your friends will sign around whatever you add.
    </p>

    <div class="create-page__tabs">
      <button :class="{ active: side === 'front' }" @click="side = 'front'">Front</button>
      <button :class="{ active: side === 'back' }" @click="side = 'back'">Back</button>
    </div>

    <ShirtCanvas
      :side="side"
      :signatures="[]"
      :placing="placingDesign"
      :design-text="form[`${side}_text`]"
      :design-text-color="form[`${side}_text_color`]"
      :design-image-path="previewUrl(side)"
      :design-x="form[`${side}_x`]"
      :design-y="form[`${side}_y`]"
      :design-width="form[`${side}_width`]"
      :design-height="form[`${side}_height`]"
      @place="onPlaceDesign"
    />

    <button type="button" class="create-page__place-btn" @click="placingDesign = true">
      {{ hasContentOn(side) ? 'Move ' + side + ' design' : 'Tap to place on ' + side }}
    </button>

    <div class="create-page__field">
      <label>Text ({{ side }}) — optional</label>
      <input type="text" v-model="form[`${side}_text`]" maxlength="60" placeholder="e.g. Class of 2026" />
    </div>

    <div class="create-page__field" v-if="form[`${side}_text`]">
      <label>Text color</label>
      <input type="color" v-model="form[`${side}_text_color`]" />
    </div>

    <div class="create-page__field">
      <label>Image ({{ side }}) — optional</label>
      <input type="file" accept="image/*" @change="e => onImageChange(e, side)" />
    </div>

    <p v-if="error" class="create-page__error">{{ error }}</p>

    <button type="button" class="create-page__submit" :disabled="submitting" @click="submit">
      {{ submitting ? 'Creating…' : 'Create my shirt & get a link' }}
    </button>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ShirtCanvas from '../components/ShirtCanvas.vue';

const side = ref('front');
const placingDesign = ref(false);
const submitting = ref(false);
const error = ref('');

const form = reactive({
  creator_name: '',
  front_text: '', front_text_color: '#1d1d1d', front_image: null,
  front_x: 50, front_y: 50, front_width: 30, front_height: 20,
  back_text: '', back_text_color: '#1d1d1d', back_image: null,
  back_x: 50, back_y: 50, back_width: 30, back_height: 20,
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

function onPlaceDesign({ x, y }) {
  form[`${side.value}_x`] = x;
  form[`${side.value}_y`] = y;
  placingDesign.value = false;
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
.create-page { max-width: 420px; margin: 0 auto; padding: 1.5rem 1rem; }
.create-page__hint { color: #666; font-size: 14px; margin-bottom: 1rem; }
.create-page__tabs { display: flex; gap: 8px; margin-bottom: 12px; }
.create-page__tabs button { flex: 1; padding: 8px; border-radius: 8px; border: 1px solid #d8d8d8; background: #fff; }
.create-page__tabs button.active { border-color: #1d1d1d; font-weight: 600; }
.create-page__place-btn {
  width: 100%; margin-top: 10px; padding: 8px; border-radius: 8px;
  border: 1px dashed #999; background: #fafafa; cursor: pointer;
}
.create-page__field { margin-top: 14px; }
.create-page__field label { display: block; font-size: 13px; color: #444; margin-bottom: 4px; }
.create-page__field input[type="text"] { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #d8d8d8; }
.create-page__error { color: #b3261e; font-size: 13px; margin-top: 12px; }
.create-page__submit {
  width: 100%; padding: 12px; margin-top: 16px; border-radius: 8px;
  border: none; background: #1d1d1d; color: #fff; font-weight: 600; cursor: pointer;
}
.create-page__submit:disabled { opacity: 0.6; }
</style>