<template>
  <div class="mode-picker">
    <div class="mode-picker__tabs">
      <button
        type="button"
        :class="{ active: mode === 'typed' }"
        @click="mode = 'typed'"
      >Type it</button>
      <button
        type="button"
        :class="{ active: mode === 'drawn' }"
        @click="mode = 'drawn'"
      >Draw it</button>
    </div>

    <div class="mode-picker__pen">
      <label>Pen color</label>
      <input type="color" v-model="color" />
    </div>

    <div v-if="mode === 'typed'" class="mode-picker__typed">
      <input
        type="text"
        v-model="typedText"
        maxlength="40"
        placeholder="Your name or a short message"
      />
    </div>

    <div v-else class="mode-picker__drawn">
      <svg
        ref="pad"
        viewBox="0 0 260 100"
        class="mode-picker__pad"
        @pointerdown="startStroke"
        @pointermove="continueStroke"
        @pointerup="endStroke"
        @pointerleave="endStroke"
      >
        <path
          v-for="(d, i) in strokes"
          :key="i"
          :d="d"
          :stroke="color"
          stroke-width="2"
          fill="none"
          stroke-linecap="round"
        />
        <path
          v-if="currentStroke"
          :d="currentStroke"
          :stroke="color"
          stroke-width="2"
          fill="none"
          stroke-linecap="round"
        />
      </svg>
      <button type="button" class="mode-picker__clear" @click="clearDrawing">Clear</button>
    </div>

    <p v-if="error" class="mode-picker__error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const mode = ref('typed');
const color = ref('#1d1d1d');
const typedText = ref('');
const error = ref('');

// Free-hand drawing: each finished stroke becomes one SVG path string;
// combined into one drawn_path (space-joined) on submit.
const strokes = reactive([]);
const currentStroke = ref('');
let drawing = false;

function startStroke(event) {
  drawing = true;
  const { x, y } = padPoint(event);
  currentStroke.value = `M${x} ${y}`;
}

function continueStroke(event) {
  if (!drawing) return;
  const { x, y } = padPoint(event);
  currentStroke.value += ` L${x} ${y}`;
}

function endStroke() {
  if (!drawing) return;
  drawing = false;
  if (currentStroke.value) strokes.push(currentStroke.value);
  currentStroke.value = '';
}

function padPoint(event) {
  const rect = event.currentTarget.getBoundingClientRect();
  return {
    x: Math.round(((event.clientX - rect.left) / rect.width) * 260),
    y: Math.round(((event.clientY - rect.top) / rect.height) * 100),
  };
}

function clearDrawing() {
  strokes.splice(0, strokes.length);
  currentStroke.value = '';
}

function getSignatureData() {
  error.value = '';

  if (mode.value === 'typed') {
    if (!typedText.value.trim()) {
      error.value = 'Type your name or a short message first.';
      return null;
    }
    return { mode: 'typed', typed_text: typedText.value.trim(), color: color.value };
  }

  if (!strokes.length) {
    error.value = 'Draw your signature first.';
    return null;
  }
  return { mode: 'drawn', drawn_path: strokes.join(' '), color: color.value };
}

defineExpose({ getSignatureData });
</script>

<style scoped>
.mode-picker { margin-top: 1.5rem; }
.mode-picker__tabs { display: flex; gap: 8px; margin-bottom: 12px; }
.mode-picker__tabs button {
  flex: 1; padding: 8px; border-radius: 8px; border: 1px solid #d8d8d8;
  background: #fff; cursor: pointer;
}
.mode-picker__tabs button.active { border-color: #1d1d1d; font-weight: 600; }
.mode-picker__pen { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.mode-picker__typed input { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #d8d8d8; }
.mode-picker__pad { width: 100%; background: #fafafa; border: 1px dashed #c9c9c9; border-radius: 8px; touch-action: none; }
.mode-picker__clear { margin-top: 8px; font-size: 13px; background: none; border: none; color: #666; cursor: pointer; }
.mode-picker__error { color: #b3261e; font-size: 13px; margin-top: 8px; }
</style>